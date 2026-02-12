<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GreaseTrapBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GreaseTrapBookingController extends Controller
{
   public function AdminBookingGreaseTrap()
   {
      $greaseTrapBookings = GreaseTrapBooking::orderBy('created_at', 'DESC')->paginate(10);
      return view('backend.grease-trap.grease-trap-booking', compact('greaseTrapBookings'));

   }

   public function getBookedSlotsAdmin(Request $request)
   {
      $request->validate([
         'date' => 'required|date'
      ]);

      $bookedSlots = GreaseTrapBooking::whereDate('booking_date', $request->date)
         ->where('booking_status', 1)
         ->pluck('booking_time_slot')
         ->toArray();

      return response()->json([
         'booked_slots' => $bookedSlots
      ]);
   }

   public function getUpdatedGreaseTrapTable()
   {
      $bookings = GreaseTrapBooking::with('user')
         ->orderByDesc('created_at') // newest first
         ->paginate(10); // 10 per page

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
            $lastId = (GreaseTrapBooking::max('id') ?? 0) + 1;
            $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
            $unitNo = strtoupper(trim($request->unit));

            // Yearly Free Booking Logic
            $freeBookingLimit = 2;
            $yearStart = Carbon::now()->startOfYear()->toDateString();
            $yearEnd = Carbon::now()->endOfYear()->toDateString();

            $unitBookingsCount = GreaseTrapBooking::where('unit_no', $request->unit)
               ->where('booking_status', 1)
               ->whereBetween('booking_date', [$yearStart, $yearEnd])
               ->count();

            $remainingFreeBookings = max($freeBookingLimit - $unitBookingsCount, 0);
            $chargedType = $unitBookingsCount < $freeBookingLimit ? 1 : 2;

            if ($chargedType == 2 && !$request->force_payment) {
               DB::rollBack();
               return response()->json([
                  'message' => "You have {$remainingFreeBookings} free bookings remaining. This booking will require payment. Do you want to continue?",
                  'requires_payment' => true,
                  'remaining_free_bookings' => $remainingFreeBookings
               ], 409);
            }

            // Prevent double slot booking
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

            // Create Booking (Admin booking, no user_id)
            $booking = GreaseTrapBooking::create([
               'user_id' => Auth::id(),
               'name' => $request->name,
               'unit_no' => $unitNo,
               'resident_type' => strtoupper($request->selectResidentType),
               'transaction_no' => $transactionNo,
               'booking_date' => $bookingDate,
               'booking_time_slot' => $request->booking_time_slot,
               'charged_type' => $chargedType,
               'remarks' => $request->remarks,
               'booking_status' => 1,
            ]);

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

   public function CancelGreaseTrapBookingAdmin(GreaseTrapBooking $booking)
   {
      try {
         $booking->load('user');

         $booking->booking_status = 2;
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


   public function AdminStoreEmergencyGreaseTrapBooking(Request $request)
   {

      $maxRetries = 3;
      $attempt = 0;

      while ($attempt < $maxRetries) {
         try {
            DB::beginTransaction();

            $bookingDate = Carbon::parse($request->booking_date)->toDateString();
            $lastId = (GreaseTrapBooking::max('id') ?? 0) + 1;
            $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

            // Yearly Free Booking Logic
            $freeBookingLimit = 2;
            $yearStart = Carbon::now()->startOfYear()->toDateString();
            $yearEnd = Carbon::now()->endOfYear()->toDateString();
            $unitNo = strtoupper(trim($request->unit));
            $unitBookingsCount = GreaseTrapBooking::where('unit_no', $unitNo)
               ->where('booking_status', 1)
               ->whereBetween('booking_date', [$yearStart, $yearEnd])
               ->count();

            $remainingFreeBookings = max($freeBookingLimit - $unitBookingsCount, 0);
            $chargedType = $unitBookingsCount < $freeBookingLimit ? 1 : 2;

            if ($chargedType == 2 && !$request->force_payment) {
               DB::rollBack();
               return response()->json([
                  'message' => "The Unit have {$remainingFreeBookings} free bookings remaining. This booking will require payment. Do you want to continue?",
                  'requires_payment' => true,
                  'remaining_free_bookings' => $remainingFreeBookings
               ], 409);
            }

            $booking = GreaseTrapBooking::create([
               'user_id' => Auth::id(),
               'name' => $request->name,
               'unit_no' => $unitNo,
               'resident_type' => strtoupper($request->selectResidentType),
               'transaction_no' => $transactionNo,
               'booking_date' => $bookingDate,
               'booking_time_slot' => $request->booking_time_slot,
               'charged_type' => $chargedType,
               'remarks' => $request->remarks,
               'emergency' => 2,
               'booking_status' => 1,
            ]);

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

   public function fetchGreaseTrapBooking($id)
   {
      $greaseTrapBooking = GreaseTrapBooking::with('user')->find($id);

      if (!$greaseTrapBooking) {
         return response()->json(['message' => 'Data not found'], 404);
      }

      return response()->json([
         'name' => $greaseTrapBooking->user->name ?? 'N/A',
         'unit_no' => $greaseTrapBooking->unit_no,
         'resident_type' => $greaseTrapBooking->resident_type,
         'remarks' => $greaseTrapBooking->remarks,
         'transaction_no' => $greaseTrapBooking->transaction_no,
         'booking_date' => Carbon::parse($greaseTrapBooking->booking_date)->format('F d, Y'),
         'booking_time_slot' => $greaseTrapBooking->booking_time_slot,
         'srf_no' => $greaseTrapBooking->srf_no,
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
      $currentDate = Carbon::today();

      $greaseTrapBookings = GreaseTrapBooking::with('user')
         ->where('booking_date', '>=', $currentDate)
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

      $greaseTrapBookings->appends(['searchGreaseTrapBooking' => $searchBooking]);

      return view('backend.grease-trap.grease-trap-booking', compact('greaseTrapBookings'));
   }


   public function AdminBookingGreaseTrapCalendar()
   {
      $schedules = GreaseTrapBooking::with('user')
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

      ]);
   }


}
