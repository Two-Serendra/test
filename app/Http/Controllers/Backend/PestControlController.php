<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PestControlBooking;
use App\Models\ResidentDetails;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Helpers\CsvHelper;


class PestControlController extends Controller
{
    public function AdminBookingPestControl()
    {
        $pestControlBookings = PestControlBooking::with([
            'user',
            'createdBy',
            'cancelledBy',
            'completedBy'
        ])->whereDate('booking_date', '>=', now()->toDateString())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return view('backend.pest-control.pest-control-booking', compact('pestControlBookings'));

    }

    public function searchPestControlBooking(Request $request)
    {
        $searchBooking = $request->input('searchPestControlBooking');

        $pestControlBookings = PestControlBooking::when($searchBooking, function ($query) use ($searchBooking) {

            $query->where('unit_no', 'LIKE', "%{$searchBooking}%")
                ->orWhere('name', 'LIKE', "%{$searchBooking}%");

        })
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        $pestControlBookings->appends([
            'searchPestControlBooking' => $searchBooking
        ]);

        return view(
            'backend.pest-control.pest-control-booking',
            compact('pestControlBookings', 'searchBooking')
        );
    }



    public function getBookedSlotsAdminPestControl(Request $request)
    {
        $unit = strtoupper($request->unit);
        $areaLetter = preg_replace('/[^A-Z]/', '', $unit);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = PestControlBooking::whereDate('booking_date', $request->date)
            ->where('booking_status', 1)
            ->where('emergency', 0) // Exclude emergency bookings
            ->get(['booking_time_slot', 'unit_area']);

        $slotStatus = [];

        foreach ($bookings as $b) {
            $slot = $b->booking_time_slot;
            $area = $b->unit_area;

            if (!isset($slotStatus[$slot])) {
                $slotStatus[$slot] = ['lowrise' => false, 'highrise' => false];
            }

            if (in_array($area, $lowrise))
                $slotStatus[$slot]['lowrise'] = true;
            if (in_array($area, $highrise))
                $slotStatus[$slot]['highrise'] = true;
        }

        $blockedForUser = [];

        foreach ($slotStatus as $slot => $status) {
            if ($status[$userGroup]) {
                $blockedForUser[] = $slot;
            }
        }

        return response()->json([
            'booked_slots' => $blockedForUser
        ]);
    }


    public function AdminStorePestControlBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        $towerGroups = [
            'A' => 'lowrise',
            'B' => 'lowrise',
            'C' => 'lowrise',
            'D' => 'lowrise',
            'E' => 'lowrise',
            'F' => 'highrise',
            'G' => 'highrise',
            'H' => 'highrise',
            'I' => 'highrise',
        ];

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $request->validate([
                    'name' => 'required|string|max:255',
                    'unit' => 'required|string|max:10',
                    'selectResidentType' => 'required|in:Owner,Tenant',
                    'booking_date' => 'required|date',
                    'booking_time_slot' => 'required|string',
                ]);

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();
                $unit = strtoupper($request->unit);
                $areaLetter = preg_replace('/[^A-Z]/', '', $unit);
                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();
                    return response()->json(['message' => 'Invalid unit area letter.'], 422);
                }

                $towerAreas = $towerGroup == 'lowrise'
                    ? ['A', 'B', 'C', 'D', 'E']
                    : ['F', 'G', 'H', 'I'];

                $existingUnitBooking = PestControlBooking::whereDate('booking_date', $bookingDate)
                    ->where('unit_no', $unit)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->exists();

                if ($existingUnitBooking) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'This unit already has a pest control booking for the selected date.',
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $existingBookings = PestControlBooking::whereDate('booking_date', $bookingDate)
                    ->whereIn('unit_area', $towerAreas)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                if ($existingBookings->contains('booking_time_slot', $request->booking_time_slot)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Slot already taken. Please refresh slots.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
                $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();

                $unitBookingsThisMonth = PestControlBooking::where('unit_no', $unit)
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$monthStart, $monthEnd])
                    ->lockForUpdate()
                    ->count();

                $freeBookingLimit = 1;
                $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;

                if ($chargedType == 2 && !$request->force_payment) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "This unit has reached the monthly free pest control booking limit. This booking will cost ₱350.00. Do you want to continue?",
                        'requires_payment' => true,
                        'remaining_free_bookings' => max($freeBookingLimit - $unitBookingsThisMonth, 0)
                    ], 409);
                }

                $booking = PestControlBooking::create([
                    'user_id' => auth()->id(),
                    'transaction_no' => '',
                    'unit_no' => $unit,
                    'created_by' => auth()->id(),
                    'name' => strtoupper($request->name),
                    'resident_type' => $request->selectResidentType,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                    'charged_type' => $chargedType,
                    'remarks' => $request->remarks,
                    'is_admin_created' => 1,
                ]);

                $booking->transaction_no = '2SPC-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                return response()->json([
                    'message' => 'Admin pest control booking created successfully.',
                    'charged_type' => $chargedType,
                    'free_used' => $unitBookingsThisMonth,
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000);
                    continue;
                }

                Log::error('Admin Pest Control Booking Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Database error.'], 500);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Admin Pest Control Booking Fatal', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong.'], 500);
            }
        }

        return response()->json(['message' => 'Could not complete booking. Please retry.'], 500);
    }


    public function getUpdatedPestControlTable()
    {
        $bookings = PestControlBooking::with('user', 'createdBy', 'cancelledBy')
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        $bookings->getCollection()->transform(function ($b) {
            return [
                'id' => $b->id,
                'transaction_no' => $b->transaction_no,
                'srf_no' => $b->srf_no,
                'name' => $b->name,
                'resident_type' => $b->resident_type,
                'unit_no' => $b->unit_no,
                'booking_date' => $b->booking_date,
                'booking_time_slot' => $b->booking_time_slot,
                'charged_type' => $b->charged_type,
                'emergency' => $b->emergency,
                'remarks' => $b->remarks,
                'booking_status' => $b->booking_status,

                // 👇 consistent formatting (same fix as grease trap)
                'created_at' => optional($b->created_at)->format('Y-m-d H:i:s'),
                'cancelled_at' => $b->cancelled_at
                    ? Carbon::parse($b->cancelled_at)->format('Y-m-d H:i:s')
                    : null,

                'createdBy' => $b->createdBy ? [
                    'name' => $b->createdBy->name
                ] : null,

                'cancelledBy' => $b->cancelledBy ? [
                    'name' => $b->cancelledBy->name
                ] : null,
            ];
        });

        return response()->json($bookings);
    }

    public function AdminStoreEmergencyPestControl(Request $request)
    {
        try {
            DB::beginTransaction();

            $bookingDate = Carbon::parse($request->booking_date)->toDateString();
            $unit = strtoupper(trim($request->unit));
            $areaLetter = preg_replace('/[^A-Z]/', '', $unit);
            $residentType = strtoupper($request->selectResidentType);


            $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
            $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();
            $unitBookingsThisMonth = PestControlBooking::where('unit_no', $unit)
                ->where('booking_status', 1)
                ->whereBetween('booking_date', [$monthStart, $monthEnd])
                ->count();

            $freeBookingLimit = 1;
            $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;


            if ($chargedType == 2 && !$request->force_payment) {
                DB::rollBack();
                return response()->json([
                    'message' => "This unit has reached the monthly free pest control booking limit. This booking will cost ₱350.00. Do you want to continue?",
                    'requires_payment' => true,
                    'remaining_free_bookings' => max($freeBookingLimit - $unitBookingsThisMonth, 0)
                ], 409);
            }


            $booking = PestControlBooking::create([
                'user_id' => auth()->id(),
                'transaction_no' => '',
                'unit_no' => $unit,
                'name' => $request->name,
                'resident_type' => $residentType,
                'booking_date' => $bookingDate,
                'booking_time_slot' => $request->booking_time_slot,
                'charged_type' => $chargedType,
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
                'emergency' => 1,
                'is_admin_created' => 1,
                'booking_status' => 1,
                'unit_area' => $areaLetter,
            ]);

            $booking->transaction_no = '2SEG-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
            $booking->save();

            DB::commit();

            return response()->json([
                'message' => 'Emergency pest control booking created successfully.',
                'charged_type' => $chargedType,
                'free_used' => $unitBookingsThisMonth,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Emergency Pest Control Booking Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }


    public function fetchPestControlBooking($id)
    {
        $pestControlBooking = PestControlBooking::with('user')->find($id);

        if (!$pestControlBooking) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'name' => $pestControlBooking->user->name ?? 'N/A',
            'unit_no' => $pestControlBooking->unit_no,
            'resident_type' => $pestControlBooking->resident_type,
            'remarks' => $pestControlBooking->remarks,
            'transaction_no' => $pestControlBooking->transaction_no,
            'booking_date' => Carbon::parse($pestControlBooking->booking_date)->format('F d, Y'),
            'booking_time_slot' => $pestControlBooking->booking_time_slot,
            'srf_no' => $pestControlBooking->srf_no,
            'charged_type' => $pestControlBooking->charged_type,
            'booking_status' => $pestControlBooking->booking_status,
            'status' => $pestControlBooking->p_c_status,
        ]);
    }

    public function AdminUpdatePestControlBooking(Request $request)
    {

        try {
            $booking = PestControlBooking::findOrFail($request->id);

            $booking->srf_no = $request->srf_no;
            $booking->remarks = $request->remarks;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Pest Control Booking updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function CancelPestControlBookingAdmin(PestControlBooking $booking)
    {
        try {
            $booking->load('user');

            $booking->booking_status = 0;
            $booking->cancelled_by = auth()->id();
            $booking->cancelled_at = now();
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking has been cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }

    public function AdminBookingPestControlCalendar()
    {
        $schedules = PestControlBooking::with('user')
            ->where('booking_status', 1)
            ->get()
            ->map(function ($schedule) {

                $timeSlot = str_replace([' NN', ' MN'], [' PM', ' AM'], $schedule->booking_time_slot);
                [$start, $end] = explode('-', $timeSlot);

                $startTime = Carbon::parse(trim($start))->format('g:i A');
                $endTime = Carbon::parse(trim($end))->format('g:i A');

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->unit_no . ' (' . $startTime . ' - ' . $endTime . ')',
                    'start' => $schedule->booking_date . ' ' . Carbon::parse(trim($start))->format('H:i:s'),
                    'end' => $schedule->booking_date . ' ' . Carbon::parse(trim($end))->format('H:i:s'),
                    'allDay' => false,
                    'unit_area' => $schedule->unit_area,
                    'emergency' => $schedule->emergency,
                ];
            });

        return view('backend.pest-control.pest-control-booking-calendar', [
            'events' => $schedules
        ]);
    }

    public function fetchPestControlCalendarSchedule($id)
    {
        $schedule = PestControlBooking::with('user')->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'id' => $schedule->id,
            'unit_no' => $schedule->unit_no,
            'name' => $schedule->user ? $schedule->user->name : 'N/A',
            'resident_type' => $schedule->resident_type,
            'booking_date' => date('F d, Y', strtotime($schedule->booking_date)),
            'booking_time_slot' => $schedule->booking_time_slot,
            'transaction_no' => $schedule->transaction_no,
            'srf_no' => $schedule->srf_no ?? 'N/A',
            'charged_type' => $schedule->charged_type,
            'emergency' => $schedule->emergency,
        ]);
    }

    public function AdminReportPestControl()
    {
        $pestControlBookings = PestControlBooking::whereDate('booking_date', '<', now()->toDateString())
            ->orderBy('booking_date', 'desc')
            ->paginate(10);
        return view('backend.pest-control.pest-control-report', compact('pestControlBookings'));

    }

    public function downloadPestControlBookingReports(Request $request)
    {
        $fromDate = $request->input('download_start_date_pc');
        $toDate = $request->input('download_end_date_pc');

        Log::info("Pest Control Download request", [
            'download_start_date_pc' => $fromDate,
            'download_end_date_pc' => $toDate
        ]);

        $formattedFromDate = Carbon::parse($fromDate)->format('m-d-Y');
        $formattedToDate = Carbon::parse($toDate)->format('m-d-Y');

        $data = DB::table('pest_control_bookings')
            ->leftJoin('users as u', 'pest_control_bookings.user_id', '=', 'u.id')
            ->leftJoin('users as completed_user', 'pest_control_bookings.completed_by', '=', 'completed_user.id')
            ->select(
                'pest_control_bookings.transaction_no',
                'pest_control_bookings.unit_no',
                'pest_control_bookings.resident_type',
                'pest_control_bookings.name as resident_name',
                'u.name as created_by_name',
                'completed_user.name as completed_by_name',
                'pest_control_bookings.booking_date',
                'pest_control_bookings.booking_time_slot',
                'pest_control_bookings.srf_no',
                'pest_control_bookings.remarks',
                'pest_control_bookings.charged_type',
                'pest_control_bookings.emergency',
                'pest_control_bookings.booking_status',
                'pest_control_bookings.created_at',
                'pest_control_bookings.updated_at',
                'pest_control_bookings.completed_at',
            )
            ->whereBetween('booking_date', [$fromDate, $toDate])
            ->orderBy('booking_date', 'desc')
            ->get();

        Log::info("Pest Control Data fetched", [
            'total_records' => count($data),
        ]);

        $fileName = "PestControl_Booking_Report_{$formattedFromDate}_to_{$formattedToDate}.csv";

        $response = new StreamedResponse(function () use ($data) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Transaction No',
                'Resident Name',
                'Unit No',
                'Resident Type',
                'Booking Date',
                'Time Slot',
                'SRF No',
                'Remarks',
                'Charged Type',
                'Emergency',
                'Status',
                'Created By',
                'Created At',
                'Updated At',
                'Completed At',
                'Completed By'
            ]);

            foreach ($data as $row) {

                $bookingDate = Carbon::parse($row->booking_date)->format('F j, Y');

                $chargeType = $row->charged_type == 1 ? 'Free' : 'Billable';
                $emergency = $row->emergency == 1 ? 'Yes' : 'No';

                $status = match ((int) $row->booking_status) {
                    PestControlBooking::STATUS_CANCELLED => 'Cancelled',
                    PestControlBooking::STATUS_SCHEDULED => 'Scheduled',
                    PestControlBooking::STATUS_COMPLETED => 'Completed',
                    default => 'Unknown',
                };

                fputcsv($handle, CsvHelper::sanitizeRow([
                    $row->transaction_no,
                    $row->resident_name,
                    $row->unit_no,
                    $row->resident_type,
                    $bookingDate,
                    $row->booking_time_slot,
                    $row->srf_no,
                    $row->remarks,
                    $chargeType,
                    $emergency,
                    $status,
                    $row->created_by_name ?? null,
                    $row->created_at,
                    $row->updated_at,
                    $row->completed_at,
                    $row->completed_by_name ?? null,
                ]));

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        Log::info("Pest Control CSV Export Completed", ['filename' => $fileName]);

        return $response;
    }

    public function importPestControlBookings(Request $request)
    {
        Log::info('Import pest control booking route hit');

        if (!$request->hasFile('file')) {
            Log::error('No file uploaded');
            return back()->with('error', 'No file uploaded');
        }

        $file = $request->file('file');
        Log::info('File received', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        DB::beginTransaction();

        try {

            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (count($csvData) <= 1) {
                Log::warning('CSV file is empty or only has headers');
                return back()->with('error', 'CSV file is empty');
            }

            $header = array_shift($csvData);

            $lastTransaction = PestControlBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction
                ? ((int) str_replace('2SPC-', '', $lastTransaction->transaction_no))
                : 0;

            foreach ($csvData as $index => $row) {

                Log::info('Processing CSV row', ['index' => $index, 'row' => $row]);


                $lastNumber++;
                $transactionNo = '2SPC-' . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);


                try {
                    PestControlBooking::create([
                        'transaction_no' => $transactionNo,
                        'unit_no' => trim($row[4] ?? null),
                        'resident_type' => trim($row[5] ?? null),
                        'name' => trim($row[6] ?? null),
                        'booking_date' => trim($row[7] ?? null),
                        'booking_time_slot' => trim($row[8] ?? null),
                        'srf_no' => !empty(trim($row[9] ?? '')) ? trim($row[9]) : null,
                        'unit_area' => trim($row[10] ?? null),
                        'remarks' => !empty(trim($row[11] ?? '')) ? trim($row[11]) : null,
                        'charged_type' => ($row[12] ?? '') !== '' ? (int) $row[12] : null,
                        'emergency' => ($row[13] ?? '') !== '' ? (int) $row[13] : 0,
                        'booking_status' => ($row[14] ?? '') !== '' ? (int) $row[14] : 0,
                        'cancelled_by' => ($row[15] ?? '') !== '' ? (int) $row[15] : null,
                        'cancelled_at' => ($row[16] ?? '') !== '' ? $row[16] : null,
                        'has_penalty' => ($row[17] ?? '') !== '' ? (int) $row[17] : 0,
                        'penalty_amount' => ($row[18] ?? '') !== '' ? (float) $row[18] : null,
                        'created_by' => ($row[19] ?? '') !== '' ? (int) $row[19] : null,
                    ]);
                    Log::info('Booking created', ['transaction_no' => $transactionNo]);

                } catch (\Exception $e) {
                    Log::error('Row insert failed', [
                        'index' => $index,
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            Log::info('CSV import completed successfully');
            return back()->with('success', 'Bookings imported successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }


    public function searchPestControlReport(Request $request)
    {
        $searchPestControlReport = $request->input('searchPestControlReport');
        $currentDate = Carbon::today();

        $pestControlBookings = PestControlBooking::where('booking_date', '<', $currentDate)

            ->when($searchPestControlReport, function ($query) use ($searchPestControlReport) {

                $query->where(function ($q) use ($searchPestControlReport) {

                    $q->where('unit_no', 'LIKE', "%{$searchPestControlReport}%")
                        ->orWhere('name', 'LIKE', "%{$searchPestControlReport}%");

                });
            })

            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        $pestControlBookings->appends([
            'searchPestControlReport' => $searchPestControlReport
        ]);

        return view(
            'backend.pest-control.pest-control-report',
            compact(
                'pestControlBookings',
                'searchPestControlReport'
            )
        );
    }

    public function completePestControlBooking(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:pest_control_bookings,id',
            'srf_no' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $booking = PestControlBooking::findOrFail($request->id);

        if ($booking->booking_status != PestControlBooking::STATUS_SCHEDULED) {

            return response()->json([
                'success' => false,
                'message' => 'Only scheduled bookings can be completed.'
            ], 422);
        }

        $booking->update([
            'srf_no' => $request->srf_no,
            'remarks' => $request->remarks,
            'booking_status' => PestControlBooking::STATUS_COMPLETED,
            'completed_at' => now(),
            'completed_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pest Control booking completed.'
        ]);
    }
}
