<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AusiBooking;
use App\Models\ResidentDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserAusiBookingConfirmation;
use App\Mail\ConciergeAusiBookingConfirmation;
use App\Events\AusiBookingCreated;
// use App\Notifications\UserAusiBookingBellNotification;

class MobileAppController extends Controller
{
    public function ausiBookingUserMobile(Request $request)
    {
        return response()
            ->view('mobile-app.ausi-booking-mobile', [
                'cache_bust' => microtime(true),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private, no-transform')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '"' . uniqid() . '"');
    }

    public function getBookedSlotsAusiMobile(Request $request)
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
                'message' => 'Unknown tower',
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

        $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = AusiBooking::whereDate('booking_date', $request->date)
            ->where('booking_status', 1)
            ->get(['booking_time_slot', 'unit_area']);

        $slotStatus = [];

        foreach ($bookings as $b) {
            $slot = $b->booking_time_slot;
            $area = $b->unit_area;

            if (!isset($slotStatus[$slot])) {
                $slotStatus[$slot] = [
                    'lowrise' => false,
                    'highrise' => false
                ];
            }

            if (in_array($area, $lowrise)) {
                $slotStatus[$slot]['lowrise'] = true;
            }

            if (in_array($area, $highrise)) {
                $slotStatus[$slot]['highrise'] = true;
            }
        }

        $blockedForUser = [];

        foreach ($slotStatus as $slot => $status) {
            if ($status[$userGroup]) {
                $blockedForUser[] = $slot;
            }
        }

        \Log::info('MOBILE AUSI SLOT REQUEST', [
            'unit_name' => $unitName,
            'unit_no' => $unitNo,
            'resident_id' => $resident->id ?? null,
            'blocked_for_user' => $blockedForUser,
        ]);

        return response()->json([
            'blocked_for_user' => $blockedForUser,
            'raw_status' => $slotStatus
        ]);
    }

    public function storeAusiBookingMobile(Request $request)
    {
        \Log::info('AUSI MOBILE HIT', $request->all());
        $maxRetries = 3;
        $attempt = 0;
        $debug = [];

        $unitName = trim($request->mobile_unit_name);

        if (!$unitName) {
            return response()->json([
                'message' => 'mobile_unit_name is required'
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
                'message' => 'Unknown tower',
                'tower' => $tower
            ], 422);
        }

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $email = $request->email;

                if (!$email) {
                    return response()->json(['message' => 'Missing email'], 401);
                }

                $resident = ResidentDetails::where('email', $email)->first();

                if (!$resident) {
                    return response()->json([
                        'message' => 'Resident not found',
                        'debug' => $email
                    ], 404);
                }

                $userId = $resident->user_id;
                if (!$resident) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Unauthorized unit selection.'
                    ], 403);
                }

                $debug[] = "Resident found: YES";
                $debug[] = "Unit No: " . $resident->unit_no;

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();

             

                $lowrise = ['A', 'B', 'C', 'D', 'E'];
                $highrise = ['F', 'G', 'H', 'I'];

                $areaLetter = preg_replace('/[^A-Z]/', '', strtoupper($resident->unit_no));

                $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

                $groupAreas = $userGroup === 'lowrise'
                    ? $lowrise
                    : $highrise;

                $slotTaken = AusiBooking::whereDate('booking_date', $bookingDate)
                    ->where('booking_time_slot', $request->booking_time_slot)
                    ->where('booking_status', 1)
                    ->whereIn('unit_area', $groupAreas)
                    ->lockForUpdate()
                    ->exists();

                if ($slotTaken) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Slot already taken just now.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $existingUnitBooking = AusiBooking::where('unit_no', strtoupper($resident->unit_no))
                    ->where('booking_status', 1)
                    ->whereYear('booking_date', $bookingDate)
                    ->lockForUpdate()
                    ->exists();

                if ($existingUnitBooking && !$request->boolean('force_override')) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Unit already has a booking this year.',
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $email = $request->email;

                if (!$email) {
                    return response()->json([
                        'message' => 'Missing email from request'
                    ], 401);
                }
                
                if (!$resident) {
                    return response()->json([
                        'message' => 'Resident not found for email'
                    ], 404);
                }

                $userId = $resident->user_id;


                $booking = AusiBooking::create([
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'email' => $email,
                    'transaction_no' => '',
                    'unit_no' => strtoupper($resident->unit_no),
                    'resident_type' => $resident->resident_type,
                    'name' => strtoupper($request->name ?? 'MOBILE USER'),
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                ]);

                $booking->transaction_no = '2AUSI-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                DB::afterCommit(function () use ($booking, $email) {

                    Mail::to($email)
                        ->queue(new UserAusiBookingConfirmation($booking));

                    Mail::to('concierge@twoserendra.com')
                        ->queue(new ConciergeAusiBookingConfirmation($booking));

                    event(new AusiBookingCreated($booking));
                });

                return response()->json([
                    'message' => 'Booking submitted successfully.',
                    'debug' => $debug,
                    'data' => $booking
                ]);

            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Mobile AUSI Booking Error', [
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'message' => 'Server error',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }
}
