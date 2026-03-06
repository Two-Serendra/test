<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\UserGreaseTrapBookingConfirmation;
use App\Mail\ConciergeGreaseTrapBookingConfirmation;
use App\Mail\UserGreaseTrapBookingCancellation;
use App\Mail\ConciergeGreaseTrapBookingCancellation;
use App\Models\GreaseTrapBooking;
use App\Models\PestControlBooking;
use App\Models\ResidentDetails;
use App\Models\FunctionRoomBooking;
use App\Models\ActivityBooking;
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
                $freeBookingLimit = 2;
                $yearStart = Carbon::now()->startOfYear()->toDateString();
                $yearEnd = Carbon::now()->endOfYear()->toDateString();

                $unitBookingsCount = GreaseTrapBooking::where('unit_no', $resident->unit_no)
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
                        'message' => "You’ve used your free grease trap booking for this year. This booking will cost ₱448.00. Continue with the booking?",
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
                        'message' => 'Slot already taken just now.'
                    ], 409);
                }
                $lastId = (GreaseTrapBooking::max('id') ?? 0) + 1;
                $transactionNo = '2SGT-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

                $booking = GreaseTrapBooking::create([
                    'user_id' => auth()->id(),
                    'unit_no' => $resident->unit_no,
                    'resident_type' => $resident->resident_type,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'charged_type' => $chargedType,
                    'transaction_no' => $transactionNo,
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


    // public function cancelGreaseTrapBooking(GreaseTrapBooking $booking, Request $request)
    // {
    //     try {
    //         $booking->load('user');

    //         if ($booking->booking_status == GreaseTrapBooking::STATUS_CANCELLED) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Booking already cancelled.'
    //             ], 400);
    //         }

    //         if ($booking->getBookingDateTime()->lt(now())) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Cannot cancel a completed booking.'
    //             ], 400);
    //         }

    //         $within24Hours = $booking->isWithin24Hours();
    //         if (!$request->has('confirm')) {
    //             return response()->json([
    //                 'success' => true,
    //                 'requires_confirmation' => true, // ALWAYS require confirmation
    //                 'message' => $within24Hours
    //                     ? 'Cancelling this booking within 24 hours will incur a penalty of ₱448.'
    //                     : '<br>No penalty will be applied.'
    //             ]);
    //         }

    //         $booking->booking_status = GreaseTrapBooking::STATUS_CANCELLED;
    //         $booking->cancelled_at = now();
    //         $booking->cancelled_by = auth()->id();

    //         $booking->applyCancellationPenalty();
    //         $booking->save();

    //         if ($booking->user) {
    //             $booking->user->notify(new UserGreaseTrapBookingBellNotification($booking));
    //         }

    //         if ($booking->user?->email) {
    //             Mail::to($booking->user->email)->queue(new UserGreaseTrapBookingCancellation($booking));
    //         }

    //         Mail::to('concierge@twoserendra.com')->queue(new ConciergeGreaseTrapBookingCancellation($booking));

    //         return response()->json([
    //             'success' => true,
    //             'message' => $booking->has_penalty
    //                 ? 'Booking cancelled. Penalty has been applied.'
    //                 : 'Booking has been cancelled successfully.'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to cancel booking.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function cancelGreaseTrapBooking(GreaseTrapBooking $booking, Request $request)
    {
        try {

            $booking->load('user');

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

                    $message = 'Cancelling within 24 hours incurred a penalty of ₱448 because the unit has already used its 2 free bookings.';

                } else {

                    $booking->cancelled_within_24hrs = 1;

                    $remaining = $freeLimit - $usedFree - 1;

                    if ($remaining <= 0) {
                        $message = 'Cancelling within 24 hours used up your last free grease trap booking for this year.';
                    } else {
                        $message = "Cancelling within 24 hours forfeited one free booking. Remaining free bookings after this: {$remaining}.";
                    }

                }

            } else {

                $message = 'Booking cancelled successfully. No penalty applied.';

            }


            $booking->save();

            if ($booking->user) {
                $booking->user->notify(new UserGreaseTrapBookingBellNotification($booking));
            }

            if ($booking->user?->email) {
                Mail::to($booking->user->email)->queue(new UserGreaseTrapBookingCancellation($booking));
            }

            Mail::to('concierge@twoserendra.com')->queue(new ConciergeGreaseTrapBookingCancellation($booking));

            return response()->json([
                'success' => true,
                'message' => $booking->has_penalty
                    ? 'Booking cancelled. Penalty has been applied.'
                    : 'Booking cancelled successfully.'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showGreaseTrapBookingDetails($id)
    {
        $booking = GreaseTrapBooking::with('user')->findOrFail($id);
        return view('frontend.user-grease-trap-booking-details', compact('booking'));
    }
    public function GreaseTrapBookiingTableReload(Request $request)
    {
        $selectedUnit = $request->unit_no;
        $bookingType = $request->booking_type ?? 'function_room';

        $bookings = collect();

        switch ($bookingType) {
            case 'function_room':
                $bookings = FunctionRoomBooking::with('functionRoom')
                    ->when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                    ->latest('function_room_booking_date')
                    ->paginate(5)
                    ->withQueryString();
                break;

            case 'amenity':
                $bookings = ActivityBooking::with('activity')
                    ->when($selectedUnit, fn($q) => $q->where('unit', $selectedUnit))
                    ->latest('booking_date')
                    ->paginate(5)
                    ->withQueryString();
                break;

            case 'grease_trap':
                $bookings = GreaseTrapBooking::when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                    ->latest('booking_date')
                    ->paginate(5)
                    ->withQueryString();
                break;

            case 'pest_control':
                $bookings = PestControlBooking::when($selectedUnit, fn($q) => $q->where('unit_no', $selectedUnit))
                    ->latest('booking_date')
                    ->paginate(5)
                    ->withQueryString();
                break;
        }

        $view = match ($bookingType) {
            'function_room' => 'frontend.resident-function-room-booking-table',
            'amenity' => 'frontend.resident-activity-booking-table',
            'grease_trap' => 'frontend.resident-grease-trap-booking-table',
            'pest_control' => 'frontend.resident-pest-control-booking-table',
        };

        return view($view, compact('bookings', 'selectedUnit', 'bookingType'))->render();
    }


}
