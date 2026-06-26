<?php

namespace App\Http\Controllers\MobileApp;

use App\Http\Controllers\Controller;
use App\Mail\TestUserGreaseTrapBookingConfirmation;
use App\Mail\TestUserGreaseTrapBookingCancellation;
use App\Mail\ConciergeGreaseTrapBookingConfirmation;
use App\Mail\UserGreaseTrapBookingCancellation;
use App\Mail\ConciergeGreaseTrapBookingCancellation;
use App\Models\TestGreaseTrapBooking;
use App\Models\ResidentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\GreaseTrapBookingCreated;
use Illuminate\Support\Facades\Mail;

class GreaseTrapBookingMobileController extends Controller
{
    public function greasetrapBookingUserMobile(Request $request)
    {
        return response()
            ->view('mobile-app.grease-trap.grease-trap-booking-mobile', [
                'cache_bust' => microtime(true),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private, no-transform')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '"' . uniqid() . '"');
    }

    public function getBookedSlotsGreaseTrapMobile(Request $request)
    {
        $unitName = trim($request->unit_name);

        if (!$unitName) {
            return response()->json([
                'message' => 'unit_name is required'
            ], 422);
        }

        $map = [
            "Almond" => "A",
            "Belize" => "B",
            "Callery" => "C",
            "Dolce" => "D",
            "Encino" => "E",
            "Aston" => "F",
            "ReadOak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];

        $parts = explode(' ', $unitName);

        if (count($parts) !== 2) {
            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $unitName
            ], 422);
        }

        [$tower, $number] = $parts;

        $towerLetter = $map[$tower] ?? null;

        if (!$towerLetter) {
            return response()->json([
                'message' => 'Unknown tower'
            ], 422);
        }

        $unitNo = $number . $towerLetter;

        $resident = ResidentDetails::where('unit_no', $unitNo)->first();

        if (!$resident) {
            return response()->json([
                'message' => 'Resident not found',
                'unit_no' => $unitNo
            ], 404);
        }

        // One booking per slot regardless of tower
        $blockedSlots = TestGreaseTrapBooking::whereDate('booking_date', $request->date)
            ->where('booking_status', 1)
            ->pluck('booking_time_slot')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        \Log::info('MOBILE GREASE TRAP SLOT REQUEST', [
            'unit_name' => $unitName,
            'unit_no' => $unitNo,
            'resident_id' => $resident->id,
            'date' => $request->date,
            'blocked_slots' => $blockedSlots,
        ]);

        return response()->json([
            'blocked_slots' => $blockedSlots
        ]);
    }
    public function storeGreaseTrapBookingMobile(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0; 

        while ($attempt < $maxRetries) {

            try {

                DB::beginTransaction();

                $email = $request->email;
                $unitName = trim($request->mobile_unit_name);

                if (!$email) {
                    return response()->json([
                        'message' => 'Missing email'
                    ], 401);
                }

                if (!$unitName) {
                    return response()->json([
                        'message' => 'Missing unit'
                    ], 422);
                }

                $map = [
                    "Almond" => "A",
                    "Belize" => "B",
                    "Callery" => "C",
                    "Dolce" => "D",
                    "Encino" => "E",
                    "Aston" => "F",
                    "ReadOak" => "G",
                    "Meranti" => "H",
                    "Sequoia" => "I",
                ];

                $parts = explode(' ', $unitName);

                if (count($parts) !== 2) {
                    return response()->json([
                        'message' => 'Invalid unit format'
                    ], 422);
                }

                [$tower, $number] = $parts;

                $towerLetter = $map[$tower] ?? null;

                if (!$towerLetter) {
                    return response()->json([
                        'message' => 'Unknown tower'
                    ], 422);
                }

                $unitNo = $number . $towerLetter;

                $resident = ResidentDetails::where('email', $email)
                    ->where('unit_no', strtoupper($unitNo))
                    ->first();

                if (!$resident) {
                    return response()->json([
                        'message' => 'Resident not found'
                    ], 404);
                }

                $bookingDate = Carbon::parse(
                    $request->booking_date
                )->toDateString();

                $freeBookingLimit = 2;

                $yearStart = Carbon::now()
                    ->startOfYear()
                    ->toDateString();

                $yearEnd = Carbon::now()
                    ->endOfYear()
                    ->toDateString();

                $unitBookingsCount = TestGreaseTrapBooking::where(
                    'unit_no',
                    strtoupper($resident->unit_no)
                )
                    ->whereBetween('booking_date', [$yearStart, $yearEnd])
                    ->where(function ($q) {
                        $q->where('booking_status', 1)
                            ->orWhere('cancelled_within_24hrs', 1);
                    })
                    ->count();

                $remainingFreeBookings = max(
                    $freeBookingLimit - $unitBookingsCount,
                    0
                );

                $chargedType = $unitBookingsCount < $freeBookingLimit
                    ? 1
                    : 2;

                if (
                    $chargedType == 2 &&
                    !$request->boolean('force_payment')
                ) {

                    DB::rollBack();

                    return response()->json([
                        'message' => "You've already used your free grease trap bookings for this year. This booking will cost ₱448.00. Continue?",
                        'requires_payment' => true,
                        'remaining_free_bookings' => $remainingFreeBookings
                    ], 409);
                }

                $slotTaken = TestGreaseTrapBooking::whereDate(
                    'booking_date',
                    $bookingDate
                )
                    ->where(
                        'booking_time_slot',
                        $request->booking_time_slot
                    )
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->exists();

                if ($slotTaken) {

                    DB::rollBack();

                    return response()->json([
                        'message' => 'Slot already taken just now.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $existingUnitBooking = TestGreaseTrapBooking::whereDate(
                    'booking_date',
                    $bookingDate
                )
                    ->where(
                        'unit_no',
                        strtoupper($resident->unit_no)
                    )
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->exists();

                if ($existingUnitBooking) {

                    DB::rollBack();

                    return response()->json([
                        'message' => 'This unit already has a grease trap booking for the selected date.',
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $booking = TestGreaseTrapBooking::create([
                    'user_id' => $resident->user_id,
                    'unit_no' => strtoupper($resident->unit_no),
                    'resident_type' => $resident->resident_type,
                    'email' => $email,
                    'name' => strtoupper($resident->name ?? 'RESIDENT'),
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'charged_type' => $chargedType,
                ]);

                $booking->transaction_no =
                    '2SGT-' .
                    str_pad($booking->id, 5, '0', STR_PAD_LEFT);

                $booking->save();

                DB::commit();

                DB::afterCommit(function () use ($booking, $email) {

                    Mail::to($email)
                        ->queue(
                            new TestUserGreaseTrapBookingConfirmation($booking)
                        );

                    // Mail::to('concierge@twoserendra.com')
                    //     ->queue(
                    //         new ConciergeGreaseTrapBookingConfirmation($booking)
                    //     );

                    // event(new GreaseTrapBookingCreated($booking));
                });

                return response()->json([
                    'message' => 'Grease trap booking submitted successfully.'
                ]);

            } catch (\Throwable $e) {

                DB::rollBack();

                Log::error('Mobile Grease Trap Booking Error', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Server error',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Could not complete booking. Please try again.'
        ], 500);
    }

    public function viewGreaseTrapBookingMobileHistory()
    {
        return view('mobile-app.grease-trap.grease-trap-booking-mobile-history');
    }

    public function getGreaseTrapBookingMobileHistory(Request $request)
    {

        $email = $request->email;
        $mobileUnit = $request->unit_name;


        $unitNo = $this->convertMobileUnitToUnitNoGt($mobileUnit);


        if (!$unitNo) {

            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $mobileUnit
            ], 422);

        }

        $bookings = TestGreaseTrapBooking::where('email', $email)
            ->where('unit_no', $unitNo)
            ->orderBy('booking_date', 'desc')
            ->get();

        return response()->json([
            'unit_no' => $unitNo,
            'bookings' => $bookings
        ]);

    }

    private function convertMobileUnitToUnitNoGt($unitName)
    {
        $map = [
            "Almond" => "A",
            "Belize" => "B",
            "Callery" => "C",
            "Dolce" => "D",
            "Encino" => "E",
            "Aston" => "F",
            "ReadOak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];


        $parts = explode(' ', trim($unitName));


        if (count($parts) !== 2) {
            return null;
        }


        [$tower, $number] = $parts;


        $towerLetter = $map[$tower] ?? null;


        if (!$towerLetter) {
            return null;
        }


        return $number . $towerLetter;
    }

    public function CancelGreaseTrapBookingMobile(TestGreaseTrapBooking $booking, Request $request)
    {
        try {

            return DB::transaction(function () use ($booking, $request) {

                $userEmail = $request->email;

                $units = ResidentDetails::where('email', $userEmail)
                    ->pluck('unit_no');

                $booking = TestGreaseTrapBooking::where('id', $booking->id)
                    ->whereIn('unit_no', $units)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($booking->email !== $userEmail) {

                    return response()->json([
                        'success' => false,
                        'message' => 'This booking was created by another resident in your unit. Only the creator can cancel it.'
                    ], 403);

                }

                if ($booking->booking_status !== TestGreaseTrapBooking::STATUS_CONFIRMED) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Only active bookings can be cancelled.'
                    ], 400);

                }

                if ($booking->getBookingDateTime()->lt(now())) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot cancel a completed booking.'
                    ], 400);

                }

                $within24Hours = $booking->isWithin24Hours();

                $usedFree = TestGreaseTrapBooking::getUsedFreeBookings($booking->unit_no);

                $freeLimit = 2;

                // First request - confirmation only
                if (!$request->has('confirm')) {

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

                // Actual cancellation
                $booking->booking_status = TestGreaseTrapBooking::STATUS_CANCELLED;
                $booking->cancelled_at = now();

                // Since mobile doesn't use auth(), remove this or set it to null
                $booking->cancelled_by = null;

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

                    $message = 'Booking cancelled successfully';

                }

                $booking->save();

                DB::afterCommit(function () use ($booking) {

                    if ($booking->email) {

                        Mail::to($booking->email)
                            ->queue(new TestUserGreaseTrapBookingCancellation($booking));

                    }


                    // Mail::to('concierge@twoserendra.com')
                    //     ->queue(new ConciergeGreaseTrapBookingCancellation($booking));

                });

                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);

            });

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

}
