<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AusiBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AusiBookingController extends Controller
{
    public function AdminBookingAusi()
    {
        $ausiBookings = AusiBooking::with([
            'user',
            'createdBy',
            'cancelledBy'
        ])->whereDate('booking_date', '>=', now()->toDateString())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
        return view('backend.ausi.ausi-booking', compact('ausiBookings'));

    }


    public function AdminStoreAusiBooking(Request $request)
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


                $hasYearlyBooking = AusiBooking::where('unit_no', $unit)
                    ->whereYear('booking_date', Carbon::parse($bookingDate)->year)
                    ->where('booking_status', 1)
                    ->exists();

                if ($hasYearlyBooking && !$request->force_booking) {

                    DB::rollBack();

                    return response()->json([
                        'message' => 'This unit already has an AUSI booking within this year. Do you want to continue?',
                        'type' => 'yearly_booking_exists',
                        'requires_confirmation' => true
                    ], 409);
                }

                $existingBookings = AusiBooking::whereDate('booking_date', $bookingDate)
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



                $booking = AusiBooking::create([
                    'user_id' => auth()->id(),
                    'transaction_no' => '',
                    'unit_no' => $unit,
                    'created_by' => auth()->id(),
                    'name' => strtoupper($request->name),
                    'resident_type' => $request->selectResidentType,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                    'remarks' => $request->remarks,
                ]);

                $booking->transaction_no = '2SAUSI-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                return response()->json([
                    'message' => 'Admin Ausi booking created successfully.',

                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000);
                    continue;
                }

                Log::error('Admin Ausi Booking Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Database error.'], 500);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Admin Ausi Booking Fatal', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong.'], 500);
            }
        }

        return response()->json(['message' => 'Could not complete booking. Please retry.'], 500);
    }

    public function getBookedSlotsAdminAusi(Request $request)
    {
        $unit = strtoupper($request->unit);
        $areaLetter = preg_replace('/[^A-Z]/', '', $unit);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = AusiBooking::whereDate('booking_date', $request->date)
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

    public function getUpdatedAusiTable()
    {
        $bookings = AusiBooking::with('user', 'createdBy', 'cancelledBy')
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
                'emergency' => $b->emergency,
                'remarks' => $b->remarks,
                'booking_status' => $b->booking_status,

                // ADD THESE
                'display_status' => $b->display_status,
                'status_badge' => $b->status_badge,

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


    public function CancelAusiBookingAdmin(AusiBooking $booking)
    {
        try {
            $booking->load('user');

            $booking->booking_status = 2;
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


    public function AdminBookingAusiCalendar()
    {
        $schedules = AusiBooking::with('user')
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
                ];
            });

        return view('backend.ausi.ausi-booking-calendar', [
            'events' => $schedules
        ]);
    }

    public function fetchAusiCalendarSchedule($id)
    {
        $schedule = AusiBooking::with('user')->find($id);

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
        ]);
    }

    public function AdminReportAusi()
    {
        $ausiBookings = AusiBooking::whereDate('booking_date', '<', now()->toDateString())
            ->orderBy('booking_date', 'desc')
            ->paginate(10);
        return view('backend.ausi.ausi-booking-report', compact('ausiBookings'));

    }

    public function searchAusiReport(Request $request)
    {
        $searchAusiReport = $request->input('searchAusiReport');
        $currentDate = Carbon::today();

        $ausiBookings = AusiBooking::where('booking_date', '<', $currentDate)

            ->when($searchAusiReport, function ($query) use ($searchAusiReport) {

                $query->where(function ($q) use ($searchAusiReport) {

                    $q->where('unit_no', 'LIKE', "%{$searchAusiReport}%")
                        ->orWhere('name', 'LIKE', "%{$searchAusiReport}%");

                });
            })

            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        $ausiBookings->appends([
            'searchAusiReport' => $searchAusiReport
        ]);

        return view(
            'backend.ausi.ausi-booking-report',
            compact(
                'ausiBookings',
                'searchAusiReport'
            )
        );
    }

    public function fetchAusiBooking($id)
    {
        $ausiBooking = AusiBooking::with('user')->find($id);

        if (!$ausiBooking) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'name' => $ausiBooking->user->name ?? 'N/A',
            'unit_no' => $ausiBooking->unit_no,
            'resident_type' => $ausiBooking->resident_type,
            'remarks' => $ausiBooking->remarks,
            'transaction_no' => $ausiBooking->transaction_no,
            'booking_date' => Carbon::parse($ausiBooking->booking_date)->format('F d, Y'),
            'booking_time_slot' => $ausiBooking->booking_time_slot,
            'srf_no' => $ausiBooking->srf_no,
        ]);
    }

    public function AdminUpdateAusiBooking(Request $request)
    {

        try {
            $booking = AusiBooking::findOrFail($request->id);

            $booking->srf_no = $request->srf_no;
            $booking->remarks = $request->remarks;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'AUSI Booking updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function downloadAusiBookingReports(Request $request)
    {
        $fromDate = $request->input('download_start_date_ausi');
        $toDate = $request->input('download_end_date_ausi');

        Log::info("AUSI Download request", [
            'download_start_date_ausi' => $fromDate,
            'download_end_date_ausi' => $toDate
        ]);

        $formattedFromDate = Carbon::parse($fromDate)->format('m-d-Y');
        $formattedToDate = Carbon::parse($toDate)->format('m-d-Y');

        $data = DB::table('ausi_bookings')
            ->leftJoin('users as u', 'ausi_bookings.user_id', '=', 'u.id')
            ->select(
                'ausi_bookings.transaction_no',
                'ausi_bookings.unit_no',
                'ausi_bookings.resident_type',

                'ausi_bookings.name as resident_name',
                'u.name as created_by_name', // if you have created_by column, else remove this

                'ausi_bookings.booking_date',
                'ausi_bookings.booking_time_slot',
                'ausi_bookings.srf_no',
                'ausi_bookings.remarks',
                'ausi_bookings.emergency',
                'ausi_bookings.booking_status',
                'ausi_bookings.created_at',
                'ausi_bookings.updated_at'
            )
            ->whereBetween('booking_date', [$fromDate, $toDate])
            ->orderBy('booking_date', 'desc')
            ->get();

        Log::info("AUSI Data fetched", [
            'total_records' => count($data),
        ]);

        $fileName = "AUSI_Booking_Report_{$formattedFromDate}_to_{$formattedToDate}.csv";

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
                'Emergency',
                'Status',
                'Created By',
                'Created At',
                'Updated At'
            ]);

            foreach ($data as $row) {

                $bookingDate = Carbon::parse($row->booking_date)->format('F j, Y');
                $emergency = $row->emergency == 1 ? 'Yes' : 'No';

                $status = match ($row->booking_status) {
                    1 => 'Completed',
                    2 => 'Cancelled',
                    default => 'Booked'
                };

                fputcsv($handle, [
                    $row->transaction_no,
                    $row->resident_name,
                    $row->unit_no,
                    $row->resident_type,
                    $bookingDate,
                    $row->booking_time_slot,
                    $row->srf_no,
                    $row->remarks,
                    $emergency,
                    $status,
                    $row->created_by_name ?? null,
                    $row->created_at,
                    $row->updated_at
                ]);

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        Log::info("AUSI CSV Export Completed", ['filename' => $fileName]);

        return $response;
    }
}
