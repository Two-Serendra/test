<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\UserGreaseTrapBookingConfirmation;
use App\Mail\ConciergeGreaseTrapBookingConfirmation;
use App\Mail\UserGreaseTrapBookingCancellation;
use App\Mail\ConciergeGreaseTrapBookingCancellation;
use App\Models\GreaseTrapBooking;
use App\Models\ResidentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\GreaseTrapBookingCreated;
use Illuminate\Support\Facades\Mail;
use App\Notifications\UserGreaseTrapBookingBellNotification;
class GreaseTrapController extends Controller
{
    public function greeseTrap()
    {
        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();
        return view('frontend.grease-trap-booking', compact('residences'));
    }

    //V1 

    // public function storeGreaseTrapBooking(Request $request)
    // {
    //     $maxRetries = 3;
    //     $attempt = 0;

    //     while ($attempt < $maxRetries) {
    //         try {
    //             DB::beginTransaction();

    //             $resident = ResidentDetails::where('id', $request->resident_id)->first();

    //             if (!$resident) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => 'Invalid residence selected.'
    //                 ], 422);
    //             }

    //             $bookingDate = Carbon::parse($request->booking_date)->toDateString();

    //             $lastId = GreaseTrapBooking::max('id') + 1;
    //             $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

    //             $unitBookingsCount = GreaseTrapBooking::where('unit_no', $resident->unit_no)
    //                 ->where('booking_status', '!=', 2)
    //                 ->count();

    //             $freeBookingLimit = 2;
    //             $unitBookingsCount = GreaseTrapBooking::where('unit_no', $resident->unit_no)
    //                 ->where('booking_status', '!=', 2)
    //                 ->count();

    //             $remainingFreeBookings = max($freeBookingLimit - $unitBookingsCount, 0);
    //             $isFreeBooking = $unitBookingsCount < $freeBookingLimit;

    //             if (!$isFreeBooking && !$request->force_payment) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => "You have {$remainingFreeBookings} free bookings remaining. This booking will require payment. Do you want to continue?",
    //                     'requires_payment' => true,
    //                     'remaining_free_bookings' => $remainingFreeBookings
    //                 ], 409);
    //             }

    //             $isAlreadyBooked = GreaseTrapBooking::whereDate('booking_date', $bookingDate)
    //                 ->where('booking_time_slot', $request->booking_time_slot)
    //                 ->where('booking_status', '!=', 2)
    //                 ->lockForUpdate()
    //                 ->exists();

    //             if ($isAlreadyBooked) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => 'This time slot is already booked.'
    //                 ], 409);
    //             }

    //             $booking = GreaseTrapBooking::create([
    //                 'user_id' => auth()->id(),
    //                 'transaction_no' => $transactionNo,
    //                 'unit_no' => $resident->unit_no,
    //                 'resident_type' => $resident->resident_type,
    //                 'booking_date' => $bookingDate,
    //                 'booking_time_slot' => $request->booking_time_slot,
    //                 'is_paid' => !$isFreeBooking
    //             ]);

    //             $booking->load('user');

    //             Mail::to($booking->user->email)
    //                 ->queue(new UserGreaseTrapBookingConfirmation($booking));
    //             Mail::to('concierge@twoserendra.com')
    //                 ->queue(new ConciergeGreaseTrapBookingConfirmation($booking));
    //             event(new GreaseTrapBookingCreated($booking));
    //             $booking->user->notify(new UserGreaseTrapBookingBellNotification($booking));
    //             DB::commit();

    //             return response()->json([
    //                 'message' => 'Grease trap booking submitted successfully.'
    //             ]);




    //         } catch (\Illuminate\Database\QueryException $e) {
    //             DB::rollBack();

    //             if (in_array($e->errorInfo[1], [1213, 1205])) {
    //                 $attempt++;
    //                 usleep(100000);
    //                 continue;
    //             }

    //             Log::error('Grease Trap Booking Error', ['error' => $e->getMessage()]);
    //             return response()->json([
    //                 'message' => 'Something went wrong while saving the booking.'
    //             ], 500);
    //         } catch (\Throwable $e) {
    //             DB::rollBack();
    //             Log::error('Grease Trap Booking Fatal Error', ['error' => $e->getMessage()]);
    //             return response()->json([
    //                 'message' => 'Something went wrong.'
    //             ], 500);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Could not complete booking. Please try again.'
    //     ], 500);
    // }


    public function storeGreaseTrapBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $resident = ResidentDetails::find($request->resident_id);
                if (!$resident) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Invalid residence selected.'
                    ], 422);
                }

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();
                $lastId = (GreaseTrapBooking::max('id') ?? 0) + 1;
                $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

                $freeBookingLimit = 2;
                $yearStart = Carbon::now()->startOfYear()->toDateString();
                $yearEnd = Carbon::now()->endOfYear()->toDateString();

                $unitBookingsCount = GreaseTrapBooking::where('unit_no', $resident->unit_no)
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
                    'user_id' => auth()->id(),
                    'transaction_no' => $transactionNo,
                    'unit_no' => $resident->unit_no,
                    'resident_type' => $resident->resident_type,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'charged_type' => $chargedType,
                ]);

                $booking->load('user');
                if ($booking->user?->email) {
                    Mail::to($booking->user->email)
                        ->queue(new UserGreaseTrapBookingConfirmation($booking));
                }

                Mail::to('concierge@twoserendra.com')
                    ->queue(new ConciergeGreaseTrapBookingConfirmation($booking));

                event(new GreaseTrapBookingCreated($booking));

                $booking->user?->notify(
                    new UserGreaseTrapBookingBellNotification($booking)
                );

                DB::commit();

                return response()->json([
                    'message' => 'Grease trap booking submitted successfully.'
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();


                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000); 
                    continue;
                }

                Log::error('Grease Trap Booking Error', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Something went wrong while saving the booking.'
                ], 500);

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Grease Trap Booking Fatal Error', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Something went wrong.'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Could not complete booking. Please try again.'
        ], 500);
    }




    public function getBookedSlots(Request $request)
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
    // public function CancelGreaseTrapBooking($id)
    // {
    //     $booking = GreaseTrapBooking::findOrFail($id);

    //     if ($booking->booking_status != 1) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Booking is already cancelled or completed.'
    //         ], 400);
    //     }

    //     $booking->booking_status = 0; // Cancelled
    //     $booking->save();

    //     $booking->user?->notify(new UserGreaseTrapBookingBellNotification($booking));


    //     if ($booking->user?->email) {
    //         Mail::to($booking->user->email)->queue(
    //             new UserGreaseTrapBookingCancellation($booking)
    //         );
    //     }
    //     Mail::to('concierge@twoserendra.com')->queue(
    //         new ConciergeGreaseTrapBookingCancellation($booking)
    //     );


    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Booking cancelled successfully.'
    //     ]);
    // }


    public function cancelGreaseTrapBooking(GreaseTrapBooking $booking)
    {
        try {
            $booking->load('user');

            $booking->booking_status = 2;
            $booking->save();


            if ($booking->user) {
                $booking->user->notify(new UserGreaseTrapBookingBellNotification($booking));
            }

            if ($booking->user?->email) {
                Mail::to($booking->user->email)->queue(
                    new UserGreaseTrapBookingCancellation($booking)
                );
            }

            Mail::to('concierge@twoserendra.com')->queue(
                new ConciergeGreaseTrapBookingCancellation($booking)
            );

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

    public function showGreaseTrapBookingDetails($id)
    {
        $booking = GreaseTrapBooking::with('user')->findOrFail($id);
        return view('frontend.user-grease-trap-booking-details', compact('booking'));
    }


}
