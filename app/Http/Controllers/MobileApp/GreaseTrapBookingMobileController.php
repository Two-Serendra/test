<?php

namespace App\Http\Controllers\MobileApp;

use App\Http\Controllers\Controller;
use App\Mail\UserGreaseTrapBookingConfirmation;
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
    public function storeGreaseTrapBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $user = auth()->user();

                $resident = ResidentDetails::where('id', $request->resident_id)
                    ->where('email', $user->email)
                    ->first();

                if (!$resident) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Unauthorized unit selection.'
                    ], 403);
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
                        'message' => 'Slot already taken just now.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $existingUnitBooking = GreaseTrapBooking::whereDate('booking_date', $bookingDate)
                    ->where('unit_no', strtoupper($resident->unit_no))
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

                $booking = GreaseTrapBooking::create([
                    'user_id' => auth()->id(),
                    'unit_no' => strtoupper($resident->unit_no),
                    'resident_type' => $resident->resident_type,
                    'name' => strtoupper($user->name),
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'charged_type' => $chargedType,
                ]);

                $booking->transaction_no = '2SGT-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                $booking->load('user');
                if ($booking->user?->email) {
                    Mail::to($booking->user->email)
                        ->queue(new UserGreaseTrapBookingConfirmation($booking));
                }

                DB::commit();

                Mail::to('concierge@twoserendra.com')
                    ->queue(new ConciergeGreaseTrapBookingConfirmation($booking));

                event(new GreaseTrapBookingCreated($booking));
                return response()->json([
                    'message' => 'Grease trap booking submitted successfully.'
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if ($e->errorInfo[1] == 1062) {
                    return response()->json([
                        'message' => 'Slot already taken.'
                    ], 409);
                }

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
}
