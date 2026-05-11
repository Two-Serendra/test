<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GreaseTrapBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GreaseTrapBookingController extends Controller
{
   public function AdminBookingGreaseTrap()
   {
      $greaseTrapBookings = GreaseTrapBooking::with([
         'user',
         'createdBy',
         'cancelledBy'
      ])->whereDate('booking_date', '>=', now()->toDateString())
         ->orderBy('created_at', 'DESC')
         ->paginate(10);
      return view('backend.grease-trap.grease-trap-booking', compact('greaseTrapBookings'));

   }

   public function getBookedSlotsAdmin(Request $request)
   {
      $request->validate([
         'date' => 'required|date'
      ]);

      $bookedSlots = GreaseTrapBooking::whereDate('booking_date', $request->date)
         ->where('booking_status', 1)
         ->where('emergency', 0) // Exclude emergency bookings
         ->pluck('booking_time_slot')
         ->toArray();

      return response()->json([
         'booked_slots' => $bookedSlots
      ]);
   }

   public function getUpdatedGreaseTrapTable()
   {
      $bookings = GreaseTrapBooking::with(['user', 'cancelledBy', 'createdBy'])
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

            'has_penalty' => $b->has_penalty,
            'penalty_amount' => $b->penalty_amount,

            // 👇 FORMAT DATES HERE (THIS FIXES YOUR ISSUE)
            'created_at' => optional($b->created_at)->format('Y-m-d H:i:s'),
            'cancelled_at' => $b->cancelled_at
               ? Carbon::parse($b->cancelled_at)->format('Y-m-d H:i:s')
               : null,
            // relations
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

   public function AdminStoreGreaseTrapBooking(Request $request)
   {
      $maxRetries = 3;
      $attempt = 0;

      while ($attempt < $maxRetries) {
         try {
            DB::beginTransaction();

            $bookingDate = Carbon::parse($request->booking_date)->toDateString();
            $unitNo = strtoupper(trim($request->unit));
            $freeBookingLimit = 2;
            $yearStart = Carbon::now()->startOfYear()->toDateString();
            $yearEnd = Carbon::now()->endOfYear()->toDateString();

            $unitBookingsCount = GreaseTrapBooking::where('unit_no', $unitNo)
               ->whereBetween('booking_date', [$yearStart, $yearEnd])
               ->where(function ($q) {
                  $q->where('booking_status', 1)
                     ->orWhere('cancelled_within_24hrs', 1);
               })
               ->count();

            $remainingFreeBookings = max($freeBookingLimit - $unitBookingsCount, 0);
            $chargedType = $unitBookingsCount < $freeBookingLimit ? 1 : 2;

            if ($chargedType == 2 && !$request->force_payment) {
               DB::rollBack();
               return response()->json([
                  'message' => "The unit has used all its free grease trap bookings for this year. This booking will cost ₱448.00. Continue with the booking?",
                  'requires_payment' => true,
                  'remaining_free_bookings' => $remainingFreeBookings
               ], 409);
            }

            $isAlreadyBooked = GreaseTrapBooking::whereDate('booking_date', $bookingDate)
               ->where('booking_time_slot', $request->booking_time_slot)
               ->where('booking_status', 1)
               ->lockForUpdate()
               ->exists();

            if ($isAlreadyBooked) {
               DB::rollBack();
               return response()->json([
                  'message' => 'This time slot is already booked.'
               ], 409);
            }

            $booking = GreaseTrapBooking::create([
               'user_id' => Auth::id(),
               'created_by' => auth()->id(),
               'name' => strtoupper($request->name),
               'unit_no' => $unitNo,
               'resident_type' => strtoupper($request->selectResidentType),
               'transaction_no' => null,
               'booking_date' => $bookingDate,
               'booking_time_slot' => $request->booking_time_slot,
               'charged_type' => $chargedType,
               'remarks' => $request->remarks,
               'booking_status' => 1,
            ]);

            $booking->transaction_no = '2SGT-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
            $booking->save();

            DB::commit();

            return response()->json([
               'message' => 'Grease trap booking created successfully.',
               'charged_type' => $chargedType
            ]);

         } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if (in_array($e->errorInfo[1], [1213, 1205])) {
               $attempt++;
               usleep(100000);
               continue;
            }

            Log::error('Admin Grease Trap Booking Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);

         } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Grease Trap Booking Fatal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);
         }
      }

      return response()->json(['message' => 'Could not complete booking. Try again.'], 500);
   }


   public function CancelGreaseTrapBookingAdmin(GreaseTrapBooking $booking, Request $request)
   {
      try {

         if ($booking->booking_status == GreaseTrapBooking::STATUS_CANCELLED) {
            return response()->json([
               'success' => false,
               'message' => 'Booking already cancelled.'
            ], 400);
         }

         if ($booking->getBookingDateTime()->lt(now())) {
            return response()->json([
               'success' => false,
               'message' => 'Cannot cancel a completed booking.'
            ], 400);
         }

         $within24Hours = $booking->isWithin24Hours();

         $usedFree = GreaseTrapBooking::getUsedFreeBookings($booking->unit_no);
         $freeLimit = 2;

         if (!$request->has('confirm')) {

            $message = '';

            if ($within24Hours) {
               if ($usedFree >= $freeLimit) {
                  $message = 'Cancelling within 24 hours will incur a penalty of ₱448 because the unit has already used its 2 free bookings.';
               } else {
                  $remaining = $freeLimit - $usedFree;
                  $message = "Cancelling within 24 hours will forfeit one of the remaining {$remaining} free grease trap bookings for this year.";
               }
            } else {
               $message = '<br>No penalty will be applied.';
            }

            return response()->json([
               'success' => true,
               'requires_confirmation' => true,
               'message' => $message
            ]);
         }

         $booking->booking_status = GreaseTrapBooking::STATUS_CANCELLED;
         $booking->cancelled_at = now();
         $booking->cancelled_by = auth()->id();

         if ($within24Hours) {
            if ($usedFree >= $freeLimit) {

               $booking->applyCancellationPenalty();
            } else {

               $booking->cancelled_within_24hrs = 1;
            }
         }

         $booking->save();

         return response()->json([
            'success' => true,
            'message' => $booking->has_penalty
               ? 'Booking cancelled. Penalty has been applied.'
               : 'Booking has been cancelled successfully.'
         ]);

      } catch (\Exception $e) {

         return response()->json([
            'success' => false,
            'message' => 'Failed to cancel booking.',
            'error' => $e->getMessage(),
         ], 500);
      }
   }

   public function AdminStoreEmergencyGreaseTrapBooking(Request $request)
   {
      $maxRetries = 3;
      $attempt = 0;

      while ($attempt < $maxRetries) {
         try {
            DB::beginTransaction();

            $bookingDate = Carbon::parse($request->booking_date)->toDateString();
            $unitNo = strtoupper(trim($request->unit));

            $freeBookingLimit = 2;
            $yearStart = Carbon::now()->startOfYear()->toDateString();
            $yearEnd = Carbon::now()->endOfYear()->toDateString();

            $unitBookingsCount = GreaseTrapBooking::where('unit_no', $unitNo)
               ->whereBetween('booking_date', [$yearStart, $yearEnd])
               ->where(function ($q) {
                  $q->where('booking_status', 1)
                     ->orWhere('cancelled_within_24hrs', 1);
               })
               ->count();

            $remainingFreeBookings = max($freeBookingLimit - $unitBookingsCount, 0);
            $chargedType = $unitBookingsCount < $freeBookingLimit ? 1 : 2;

            if ($chargedType == 2 && !$request->force_payment) {
               DB::rollBack();
               return response()->json([
                  'message' => "This unit has reached the free grease trap booking limit for the year. This booking will cost ₱448.00. Do you want to continue?",
                  'requires_payment' => true,
                  'remaining_free_bookings' => $remainingFreeBookings
               ], 409);
            }

            $lastId = (GreaseTrapBooking::max('id') ?? 0) + 1;
            $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

            $booking = GreaseTrapBooking::create([
               'user_id' => Auth::id(),
               'name' => $request->name,
               'created_by' => auth()->id(),
               'unit_no' => $unitNo,
               'resident_type' => strtoupper($request->selectResidentType),
               'transaction_no' => $transactionNo,
               'booking_date' => $bookingDate,
               'booking_time_slot' => $request->booking_time_slot,
               'charged_type' => $chargedType,
               'remarks' => $request->remarks,
               'emergency' => 1,
               'booking_status' => 1,
            ]);

            DB::commit();

            return response()->json([
               'message' => 'Emergency grease trap booking created successfully.',
               'charged_type' => $chargedType
            ]);

         } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            if (in_array($e->errorInfo[1], [1213, 1205])) {
               $attempt++;
               usleep(100000);
               continue;
            }

            Log::error('Admin Emergency Grease Trap Booking Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);


         } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Emergency Grease Trap Booking Fatal', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);
         }
      }

      return response()->json(['message' => 'Could not complete booking. Try again.'], 500);
   }



   public function fetchGreaseTrapBooking($id)
   {

      $greaseTrapBooking = GreaseTrapBooking::with('user')->find($id);

      if (!$greaseTrapBooking) {
         return response()->json(['message' => 'Data not found'], 404);
      }
      return response()->json([
         'name' => $greaseTrapBooking->name ?? 'N/A',
         'unit_no' => $greaseTrapBooking->unit_no,
         'resident_type' => $greaseTrapBooking->resident_type,
         'remarks' => $greaseTrapBooking->remarks,
         'transaction_no' => $greaseTrapBooking->transaction_no,
         'booking_date' => Carbon::parse($greaseTrapBooking->booking_date)->format('F d, Y'),
         'booking_time_slot' => $greaseTrapBooking->booking_time_slot,
         'srf_no' => $greaseTrapBooking->srf_no ?: null,
         'charged_type' => $greaseTrapBooking->charged_type, // <-- add this
      ]);
   }

   public function AdminUpdateGreaseTrapBooking(Request $request)
   {

      try {
         $booking = GreaseTrapBooking::findOrFail($request->id);

         $booking->srf_no = $request->srf_no;
         $booking->remarks = $request->remarks;
         $booking->save();

         return response()->json([
            'success' => true,
            'message' => 'Grease Trap Booking updated successfully'
         ]);

      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Update failed',
            'error' => $e->getMessage()
         ], 500);
      }
   }

   public function searchGreaseTrapBooking(Request $request)
   {
      $searchBooking = $request->input('searchGreaseTrapBooking');

      $greaseTrapBookings = GreaseTrapBooking::with('user')
         ->when($searchBooking, function ($query, $searchBooking) {
            $query->where(function ($q) use ($searchBooking) {
               $q->where('unit_no', 'LIKE', "%{$searchBooking}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchBooking) {
                     $userQuery->where('name', 'LIKE', "%{$searchBooking}%");
                  });
            });
         })
         ->orderBy('booking_date', 'desc')
         ->paginate(10);

      $greaseTrapBookings->appends([
         'searchGreaseTrapBooking' => $searchBooking
      ]);

      return view('backend.grease-trap.grease-trap-booking', compact('greaseTrapBookings', 'searchBooking'))
         ->with('searchGreaseTrapBooking', $searchBooking);
   }


   public function AdminBookingGreaseTrapCalendar()
   {
      $schedules = GreaseTrapBooking::with('user')
         ->where('booking_status', 1)
         ->get()
         ->map(function ($schedule) {

            if (!empty($schedule->booking_time_slot)) {

               $timeSlot = str_replace([' NN', ' MN'], [' PM', ' AM'], $schedule->booking_time_slot);
               $times = explode('-', $timeSlot);

               $start = trim($times[0]);
               $end = trim($times[1] ?? $times[0]);

               $startTime = Carbon::parse($start)->format('g:i A');
               $endTime = Carbon::parse($end)->format('g:i A');

               return [
                  'id' => $schedule->id,
                  'title' => $schedule->unit_no . ' (' . $startTime . ' - ' . $endTime . ')',
                  'start' => $schedule->booking_date . ' ' . Carbon::parse($start)->format('H:i:s'),
                  'end' => $schedule->booking_date . ' ' . Carbon::parse($end)->format('H:i:s'),
                  'allDay' => false,
               ];
            }

            // If booking_time_slot is empty
            return [
               'id' => $schedule->id,
               'title' => $schedule->unit_no . ' (No Time Slot)',
               'start' => $schedule->booking_date,
               'allDay' => true,
            ];
         });

      return view('backend.grease-trap.grease-trap-booking-calendar', [
         'events' => $schedules
      ]);
   }


   public function fetchGreaseTrapCalendarSchedule($id)
   {
      $schedule = GreaseTrapBooking::with('user')->find($id);

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

   public function AdminReportGreaseTrap()
   {
      $greaseTrapBookings = GreaseTrapBooking::whereDate('booking_date', '<', now()->toDateString())
         ->orderBy('booking_date', 'desc')
         ->paginate(10);
      return view('backend.grease-trap.grease-trap-report', compact('greaseTrapBookings'));

   }

   public function downloadGreaseTrapBookingRecords(Request $request)
   {
      $fromDate = $request->input('download_start_date_gt');
      $toDate = $request->input('download_end_date_gt');

      Log::info("Grease Trap Download request", [
         'download_start_date_gt' => $fromDate,
         'download_end_date_gt' => $toDate
      ]);

      $formattedFromDate = Carbon::parse($fromDate)->format('m-d-Y');
      $formattedToDate = Carbon::parse($toDate)->format('m-d-Y');

      // Fetch data (PAST BOOKINGS ONLY)
      $data = DB::table('grease_trap_bookings')
         ->leftJoin('users', 'grease_trap_bookings.user_id', '=', 'users.id')
         ->select(
            'grease_trap_bookings.transaction_no',
            'grease_trap_bookings.unit_no',
            'grease_trap_bookings.resident_type',
            'users.name',
            'grease_trap_bookings.booking_date',
            'grease_trap_bookings.booking_time_slot',
            'grease_trap_bookings.srf_no',
            'grease_trap_bookings.remarks',
            'grease_trap_bookings.charged_type',
            'grease_trap_bookings.emergency',
            'grease_trap_bookings.booking_status',
            'grease_trap_bookings.created_at',
            'grease_trap_bookings.updated_at'
         )
         ->whereBetween('booking_date', [$fromDate, $toDate])
         ->orderBy('booking_date', 'desc')
         ->get();

      Log::info("Grease Trap Data fetched", [
         'total_records' => count($data),
      ]);

      $fileName = "GreaseTrap_Booking_Report_{$formattedFromDate}_to_{$formattedToDate}.csv";

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
            'Created At',
            'Updated At'
         ]);

         foreach ($data as $row) {

            $bookingDate = Carbon::parse($row->booking_date)->format('F j, Y');
            $chargeType = $row->charged_type == 1 ? 'Free' : 'Billable';
            $emergency = $row->emergency == 1 ? 'Yes' : 'No';
            $status = match ($row->booking_status) {
               1 => 'Completed',
               2 => 'Cancelled',
               default => 'Booked'
            };

            fputcsv($handle, [
               $row->transaction_no,
               $row->name,
               $row->unit_no,
               $row->resident_type,
               $bookingDate,
               $row->booking_time_slot,
               $row->srf_no,
               $row->remarks,
               $chargeType,
               $emergency,
               $status,
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

      Log::info("Grease Trap CSV Export Completed", ['filename' => $fileName]);

      return $response;
   }



   // public function importGreaseTrapBookings(Request $request)
   // {
   //    Log::info('Import grease trap booking route hit');

   //    if (!$request->hasFile('file')) {
   //       Log::error('No file uploaded');
   //       return back()->with('error', 'No file uploaded');
   //    }

   //    $file = $request->file('file');
   //    Log::info('File received', [
   //       'filename' => $file->getClientOriginalName(),
   //       'size' => $file->getSize(),
   //       'mime' => $file->getMimeType(),
   //    ]);

   //    DB::beginTransaction();

   //    try {

   //       $csvData = array_map('str_getcsv', file($file->getRealPath()));

   //       if (count($csvData) <= 1) {
   //          Log::warning('CSV file is empty or only has headers');
   //          return back()->with('error', 'CSV file is empty');
   //       }

   //       $header = array_shift($csvData);

   //       $lastTransaction = GreaseTrapBooking::lockForUpdate()->latest('id')->first();
   //       $lastNumber = $lastTransaction
   //          ? ((int) str_replace('2SGT-', '', $lastTransaction->transaction_no))
   //          : 0;

   //       foreach ($csvData as $index => $row) {

   //          Log::info('Processing CSV row', ['index' => $index, 'row' => $row]);


   //          $lastNumber++;
   //          $transactionNo = '2SGT-' . str_pad($lastNumber, 5, '0', STR_PAD_LEFT);

   //          try {
   //             $bookingDate = Carbon::parse(trim($row[6]))->format('Y-m-d');
   //          } catch (\Exception $e) {
   //             Log::error('Booking date parse error', [
   //                'row' => $row,
   //                'error' => $e->getMessage()
   //             ]);
   //             continue;
   //          }

   //          try {
   //             GreaseTrapBooking::create([
   //                'transaction_no' => $transactionNo,
   //                'unit_no' => trim($row[4]),
   //                'resident_type' => trim($row[5]),
   //                'booking_date' => $bookingDate,
   //                'booking_time_slot' => trim($row[7]),
   //                'srf_no' => trim($row[8]),
   //                'remarks' => null,
   //                'charged_type' => trim($row[10]),
   //                'emergency' => trim($row[11]),
   //                'booking_status' => trim($row[12]),
   //                'cancelled_by' => null,
   //                'cancelled_at' => null,
   //                'has_penalty' => trim($row[14]),
   //                'penalty_amount' => null,
   //                'created_by' => null,
   //                'created_at' => null,
   //                'updated_at' => null,
   //             ]);

   //             Log::info('Booking created', ['transaction_no' => $transactionNo]);

   //          } catch (\Exception $e) {
   //             Log::error('Row insert failed', [
   //                'index' => $index,
   //                'row' => $row,
   //                'error' => $e->getMessage()
   //             ]);
   //          }
   //       }

   //       DB::commit();
   //       Log::info('CSV import completed successfully');
   //       return back()->with('success', 'Bookings imported successfully');

   //    } catch (\Exception $e) {
   //       DB::rollBack();
   //       Log::error('CSV import failed', ['error' => $e->getMessage()]);
   //       return back()->with('error', $e->getMessage());
   //    }
   // }

   public function importGreaseTrapBookings(Request $request)
   {
      Log::info('Import grease trap booking route hit');

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

         foreach ($csvData as $index => $row) {

            Log::info('Processing CSV row', ['index' => $index, 'row' => $row]);

            try {
               $bookingDate = Carbon::parse(trim($row[7]))->format('Y-m-d');
            } catch (\Exception $e) {
               Log::error('Booking date parse error', [
                  'row' => $row,
                  'error' => $e->getMessage()
               ]);
               continue;
            }

            try {
               // Create booking first without transaction_no
               $booking = GreaseTrapBooking::create([
                  'transaction_no' => null, // will assign after insert
                  'unit_no' => trim($row[4]),
                  'resident_type' => trim($row[5]),
                  'name' => trim($row[6]),
                  'booking_date' => $bookingDate,
                  'booking_time_slot' => trim($row[8]),
                  'srf_no' => trim($row[9]),
                  'remarks' => null,
                  'charged_type' => trim($row[11]),
                  'emergency' => trim($row[12]),
                  'booking_status' => trim($row[13]),
                  'cancelled_by' => null,
                  'cancelled_at' => null,
                  'has_penalty' => trim($row[15]),
                  'penalty_amount' => null,
                  'created_by' => null,
               ]);

               // Assign unique transaction_no based on auto-increment id
               $booking->transaction_no = '2SGT-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
               $booking->save();

               Log::info('Booking created', ['transaction_no' => $booking->transaction_no]);

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


   public function searchGreaseTrapReports(Request $request)
   {
      $searchBooking = $request->input('searchGreaseTrapReports');
      $currentDate = Carbon::today();

      $greaseTrapBookings = GreaseTrapBooking::with('user')
         ->where('booking_date', '<', $currentDate)
         ->when($searchBooking, function ($query, $searchBooking) {
            $query->where(function ($q) use ($searchBooking) {
               $q->where('unit_no', 'LIKE', "%{$searchBooking}%")
                  ->orWhereHas('user', function ($userQuery) use ($searchBooking) {
                     $userQuery->where('name', 'LIKE', "%{$searchBooking}%");
                  });
            });
         })
         ->orderBy('booking_date', 'desc')
         ->paginate(10);

      $greaseTrapBookings->appends(['searchGreaseTrapReports' => $searchBooking]);

      return view('backend.grease-trap.grease-trap-report', compact('greaseTrapBookings', 'searchBooking'))
         ->with('searchGreaseTrapReports', $searchBooking);
   }


   public function getUpdatedGreaseTrapReportTable()
   {
      $bookings = GreaseTrapBooking::with(['user', 'cancelledBy', 'createdBy'])
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
            'charged_type' => $b->charged_type,
            'emergency' => $b->emergency,
            'remarks' => $b->remarks,
            'booking_status' => $b->booking_status,

            'has_penalty' => $b->has_penalty,
            'penalty_amount' => $b->penalty_amount,

            // 👇 FORMAT DATES HERE (THIS FIXES YOUR ISSUE)
            'created_at' => optional($b->created_at)->format('Y-m-d H:i:s'),
            'cancelled_at' => $b->cancelled_at
               ? Carbon::parse($b->cancelled_at)->format('Y-m-d H:i:s')
               : null,
            // relations
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

   public function AdminUpdateGreaseTrapReport(Request $request)
   {

      try {
         $booking = GreaseTrapBooking::findOrFail($request->id);

         $booking->srf_no = $request->srf_no;
         $booking->remarks = $request->remarks;
         $booking->save();

         return response()->json([
            'success' => true,
            'message' => 'Grease Trap Booking updated successfully'
         ]);

      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Update failed',
            'error' => $e->getMessage()
         ], 500);
      }
   }
}
