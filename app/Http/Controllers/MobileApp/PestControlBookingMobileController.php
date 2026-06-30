<?php

namespace App\Http\Controllers\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResidentDetails;
use App\Models\TestPestControlBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestUserPestControlBookingConfirmation;
use App\Mail\TestUserPestControlBookingCancellation;
class PestControlBookingMobileController extends Controller
{
    public function pestControlBookingUserMobile(Request $request)
    {
        return response()
            ->view('mobile-app.pest-control.pest-control-booking-mobile', [
                'cache_bust' => microtime(true),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private, no-transform')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '"' . uniqid() . '"');
    }

    public function getBookedSlotsPestControlMobile(Request $request)
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
        $blockedSlots = TestPestControlBooking::whereDate('booking_date', $request->date)
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

    public function storePestControlBookingMobile(Request $request)
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
                    ->lockForUpdate()
                    ->first();

                if (!$resident) {
                    return response()->json([
                        'message' => 'Resident not found'
                    ], 404);
                }

                $bookingDate = Carbon::parse(
                    $request->booking_date
                )->toDateString();

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

                $areaLetter = preg_replace('/[^A-Z]/', '', strtoupper($resident->unit_no));

                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Unknown area for your unit.'
                    ], 422);
                }

                $towerAreas = $towerGroup == 'lowrise'
                    ? ['A', 'B', 'C', 'D', 'E']
                    : ['F', 'G', 'H', 'I'];

                $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
                $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();

                $unitBookingsThisMonth = TestPestControlBooking::where(
                    'unit_no',
                    strtoupper($resident->unit_no)
                )
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$monthStart, $monthEnd])
                    ->lockForUpdate()
                    ->count();

                $freeBookingLimit = 1;

                $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;

                if ($chargedType == 2 && !$request->boolean('force_payment')) {

                    DB::rollBack();

                    return response()->json([
                        'message' => "You’ve used your free pest control booking for this month. This booking will cost ₱350.00. Continue with the booking?",
                        'requires_payment' => true,
                        'remaining_free_bookings' => max($freeBookingLimit - $unitBookingsThisMonth, 0)
                    ], 409);
                }

                $existingBookings = TestPestControlBooking::whereDate(
                    'booking_date',
                    $bookingDate
                )
                    ->whereIn('unit_area', $towerAreas)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                $slotTaken = $existingBookings->contains(
                    'booking_time_slot',
                    $request->booking_time_slot
                );

                if ($slotTaken) {

                    DB::rollBack();

                    return response()->json([
                        'message' => 'Slot already taken just now.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $existingUnitBooking = TestPestControlBooking::whereDate(
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
                        'message' => 'This unit already has a pest control booking for the selected date.',
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $booking = TestPestControlBooking::create([
                    'user_id' => $resident->user_id,
                    'unit_no' => strtoupper($resident->unit_no),
                    'unit_area' => $areaLetter,
                    'resident_type' => $resident->resident_type,
                    'email' => $email,
                    'name' => strtoupper($resident->name ?? 'RESIDENT'),
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'charged_type' => $chargedType,
                ]);
                $booking->transaction_no =
                    '2SPC-' .
                    str_pad($booking->id, 5, '0', STR_PAD_LEFT);

                $booking->save();

                DB::commit();

                DB::afterCommit(function () use ($booking, $email) {

                    Mail::to($email)
                        ->queue(
                            new TestUserPestControlBookingConfirmation($booking)
                        );

                    // Mail::to('concierge@twoserendra.com')
                    //     ->queue(
                    //         new ConciergeGreaseTrapBookingConfirmation($booking)
                    //     );

                    // event(new GreaseTrapBookingCreated($booking));
                });

                return response()->json([
                    'message' => 'Pest control booking submitted successfully.'
                ]);

            } catch (\Throwable $e) {

                DB::rollBack();

                Log::error('Mobile Pest Control Booking Error', [
                    'request_email' => $request->email,
                    'all_request' => $request->all(),
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

}
