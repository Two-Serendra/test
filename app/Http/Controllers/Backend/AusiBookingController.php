<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AusiInspectionItem;
use Illuminate\Http\Request;
use App\Models\AusiBooking;
use App\Models\AusiInspectionResult;
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
            'cancelledBy',
            'completedBy',
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
                $bookingDateCarbon = Carbon::parse($request->booking_date);
                $bookingDate = $bookingDateCarbon->toDateString();
                $unit = strtoupper($request->unit);
                $areaLetter = preg_replace('/[^A-Z]/', '', $unit);
                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();
                    return response()->json(['message' => 'Invalid unit area letter.'], 422);
                }

                if ($bookingDateCarbon->isTuesday()) {

                    $blockedTuesdaySlots = [
                        '8:00 AM - 8:30 AM',
                        '8:30 AM - 9:00 AM',
                        '9:00 AM - 9:30 AM',
                        '9:30 AM - 10:00 AM',
                    ];

                    if (in_array($request->booking_time_slot, $blockedTuesdaySlots)) {

                        DB::rollBack();

                        return response()->json([
                            'message' => 'On Tuesdays, AUSI bookings start at 10:00 AM.'
                        ], 422);
                    }
                }



                $towerAreas = $towerGroup == 'lowrise'
                    ? ['A', 'B', 'C', 'D', 'E']
                    : ['F', 'G', 'H', 'I'];


                $hasYearlyBooking = AusiBooking::where('unit_no', $unit)
                    ->whereYear('booking_date', $bookingDateCarbon->year)
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

        // Tuesday rule
        if (Carbon::parse($request->date)->isTuesday()) {
            $blockedForUser = array_merge($blockedForUser, [
                '8:00 AM - 8:30 AM',
                '8:30 AM - 9:00 AM',
                '9:00 AM - 9:30 AM',
                '9:30 AM - 10:00 AM',
            ]);
        }

        $blockedForUser = array_unique($blockedForUser);

        return response()->json([
            'booked_slots' => array_values($blockedForUser)
        ]);
    }

    public function getUpdatedAusiTable()
    {
        $bookings = AusiBooking::with('user', 'createdBy', 'cancelledBy', 'completedBy')
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

                'completed_at' => $b->completed_at
                    ? Carbon::parse($b->completed_at)->format('Y-m-d H:i:s')
                    : null,

                'completedBy' => $b->completedBy ? [
                    'name' => $b->completedBy->name
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

    public function importAusiBookings(Request $request)
    {
        Log::info('Import ausi booking route hit');

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

            $lastTransaction = AusiBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction
                ? ((int) str_replace('2SAUSI-', '', $lastTransaction->transaction_no))
                : 0;

            foreach ($csvData as $index => $row) {

                Log::info('Processing CSV row', ['index' => $index, 'row' => $row]);


                $lastNumber++;
                $transactionNo = '2SAUSI-' . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);


                try {
                    AusiBooking::create([
                        'transaction_no' => $transactionNo,
                        'unit_no' => trim($row[0] ?? null),
                        'unit_area' => preg_replace('/[^A-Z]/', '', trim($row[1] ?? null)),
                        'booking_date' => !empty(trim($row[2] ?? ''))
                            ? trim($row[2])
                            : null,
                        'booking_time_slot' => !empty(trim($row[3] ?? ''))
                            ? trim($row[3])
                            : null,
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

        $inspectionResults = AusiInspectionResult::with('inspectionItem')
            ->where('ausi_booking_id', $id)
            ->get();


        return response()->json([
            'name' => $ausiBooking->user->name ?? 'N/A',
            'unit_no' => $ausiBooking->unit_no,
            'resident_type' => $ausiBooking->resident_type,
            'remarks' => $ausiBooking->remarks,
            'transaction_no' => $ausiBooking->transaction_no,
            'booking_date' => Carbon::parse($ausiBooking->booking_date)->format('F d, Y'),
            'booking_time_slot' => $ausiBooking->booking_time_slot,
            'srf_no' => $ausiBooking->srf_no,
            'inspection_results' => $inspectionResults,
            'booking_status' => $ausiBooking->booking_status,
            'display_status' => $ausiBooking->display_status,
            'status_badge' => $ausiBooking->status_badge,
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

            // completed user
            ->leftJoin('users as completed_user', 'ausi_bookings.completed_by', '=', 'completed_user.id')

            // cancelled user
            ->leftJoin('users as cancelled_user', 'ausi_bookings.cancelled_by', '=', 'cancelled_user.id')

            // inspection results
            ->leftJoin('ausi_inspection_results as air', 'ausi_bookings.id', '=', 'air.ausi_booking_id')

            ->leftJoin('ausi_inspection_items as aii', 'air.inspection_item_id', '=', 'aii.id')

            ->select(
                'ausi_bookings.id',
                'ausi_bookings.transaction_no',
                'ausi_bookings.unit_no',
                'ausi_bookings.resident_type',
                'ausi_bookings.name as resident_name',

                'u.name as created_by_name',

                'ausi_bookings.booking_date',
                'ausi_bookings.booking_time_slot',
                'ausi_bookings.srf_no',
                'ausi_bookings.remarks',
                'ausi_bookings.emergency',
                'ausi_bookings.booking_status',

                'ausi_bookings.cancelled_at',
                'cancelled_user.name as cancelled_by_name',

                'ausi_bookings.completed_at',
                'completed_user.name as completed_by_name',

                'ausi_bookings.created_at',
                'ausi_bookings.updated_at',

                DB::raw("
            GROUP_CONCAT(
                CONCAT(
                    aii.item_name,
                    ': ',
                    CASE 
                        WHEN air.status = 1 THEN aii.option_1
                        ELSE aii.option_2
                    END
                )
                SEPARATOR ' | '
            ) as inspection_results
        ")
            )
            ->whereBetween('booking_date', [$fromDate, $toDate])
            ->groupBy('ausi_bookings.id')
            ->orderBy('booking_date', 'desc')
            ->get();

        Log::info("AUSI Data fetched", [
            'total_records' => count($data),
        ]);

        $fileName = "AUSI_Booking_Report_{$formattedFromDate}_to_{$formattedToDate}.csv";

        $response = new StreamedResponse(function () use ($data) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Booking ID',
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

                'Inspection Results',

                'Completed By',
                'Completed At',

                'Cancelled By',
                'Cancelled At',

                'Created By',
                'Created At',
                'Updated At'
            ]);

            foreach ($data as $row) {

                $bookingDate = Carbon::parse($row->booking_date)->format('F j, Y');
                $emergency = $row->emergency == 1 ? 'Yes' : 'No';

                $status = match ($row->booking_status) {
                    0 => 'Cancelled',
                    1 => 'Scheduled',
                    2 => 'Completed',
                    default => 'Booked'
                };

                fputcsv($handle, [

                    $row->id,

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


                    // inspection
                    $row->inspection_results ?? 'Not Inspected',


                    // completed
                    $row->completed_by_name,

                    $row->completed_at,


                    // cancelled
                    $row->cancelled_by_name,

                    $row->cancelled_at,


                    // created
                    $row->created_by_name,

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

    public function AdminAusiInspectionItem()
    {

        $AusiInspectionItems = AusiInspectionItem::paginate(10);
        return view('backend.ausi.ausi-inspection-item', compact('AusiInspectionItems'));
    }
    public function storeAusiInspectionItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'option_1' => 'required|string|max:255',
            'option_2' => 'required|string|max:255',
        ]);

        try {

            AusiInspectionItem::create([
                'item_name' => strtoupper(trim($request->item_name)),
                'option_1' => strtoupper(trim($request->option_1)),
                'option_2' => strtoupper(trim($request->option_2)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item added successfully'
            ]);

        } catch (\Exception $e) {

            \Log::error('Failed to create inspection item', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add item'
            ], 500);
        }
    }

    public function getUpdatedInspectionItemTable()
    {
        $inspectionItems = AusiInspectionItem::paginate(10);

        $inspectionItems->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'option_1' => $item->option_1,
                'option_2' => $item->option_2,
            ];
        });

        return response()->json($inspectionItems);
    }

    public function deleteInspectionItem(Request $request)
    {
        $inspectionItemId = $request->input('inspectionItemId');

        try {

            $inspectionItem = AusiInspectionItem::findOrFail($inspectionItemId);

            $inspectionItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Inspection item deleted successfully.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Inspection item deletion failed.'
            ], 500);
        }
    }

    public function fetchInspectionItem($id)
    {
        $inspectionItem = AusiInspectionItem::find($id);

        if (!$inspectionItem) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'id' => $inspectionItem->id,
            'item_name' => $inspectionItem->item_name ?? 'N/A',
            'option_1' => $inspectionItem->option_1,
            'option_2' => $inspectionItem->option_2,
        ]);
    }
    public function updateInspectionItem(Request $request)
    {
        $request->validate([
            'item_name' => 'required',
            'option_1' => 'required',
            'option_2' => 'required',
        ]);

        $item = AusiInspectionItem::findOrFail($request->id);

        $item->update([
            'item_name' => strtoupper(trim($request->item_name)),
            'option_1' => strtoupper(trim($request->option_1)),
            'option_2' => strtoupper(trim($request->option_2)),
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function fetchAusiInspection($id)
    {
        $booking = AusiBooking::with('user')
            ->findOrFail($id);


        $inspectionItems = AusiInspectionItem::all();


        return response()->json([

            'name' => $booking->user->name ?? 'N/A',

            'unit_no' => $booking->unit_no,

            'inspection_items' => $inspectionItems

        ]);
    }

    public function saveAusiInspection(Request $request)
    {
        DB::beginTransaction();

        try {
            $bookingId = $request->ausi_booking_id;
            AusiInspectionResult::where(
                'ausi_booking_id',
                $bookingId
            )->delete();
            foreach ($request->inspections as $inspection) {

                AusiInspectionResult::create([

                    'ausi_booking_id' => $bookingId,

                    'inspection_item_id' => $inspection['inspection_item_id'],

                    'status' => $inspection['status']

                ]);
            }
            $booking = AusiBooking::findOrFail($bookingId);
            $booking->booking_status = 2;
            $booking->completed_at = now();
            $booking->completed_by = auth()->id();
            $booking->remarks = strtoupper(trim($request->remarks ?? ''));
            $booking->save();
            DB::commit();
            return response()->json([

                'success' => true,

                'message' => 'Inspection completed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    public function getUpdatedAusiReportTable()
    {
        $bookings = AusiBooking::with('user', 'createdBy', 'cancelledBy', 'completedBy')
            ->whereDate('booking_date', '<', now()->toDateString())
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

                'completed_at' => $b->completed_at
                    ? Carbon::parse($b->completed_at)->format('Y-m-d H:i:s')
                    : null,

                'completedBy' => $b->completedBy ? [
                    'name' => $b->completedBy->name
                ] : null,


                'cancelledBy' => $b->cancelledBy ? [
                    'name' => $b->cancelledBy->name
                ] : null,
            ];
        });

        return response()->json($bookings);
    }

    public function AdminUpdateAusiBookingReport(Request $request)
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

            Log::error('AUSI Update Failed', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function saveAusiInspectionReport(Request $request)
    {
        DB::beginTransaction();

        try {
            $bookingId = $request->ausi_booking_id;
            AusiInspectionResult::where(
                'ausi_booking_id',
                $bookingId
            )->delete();
            foreach ($request->inspections as $inspection) {

                AusiInspectionResult::create([

                    'ausi_booking_id' => $bookingId,

                    'inspection_item_id' => $inspection['inspection_item_id'],

                    'status' => $inspection['status']

                ]);
            }
            $booking = AusiBooking::findOrFail($bookingId);
            $booking->booking_status = 2;
            $booking->completed_at = now();
            $booking->completed_by = auth()->id();
            $booking->remarks = strtoupper(trim($request->remarks ?? ''));
            $booking->save();
            DB::commit();
            return response()->json([

                'success' => true,

                'message' => 'Inspection completed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }
}
