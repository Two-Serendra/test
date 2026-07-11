<?php

namespace App\Http\Controllers\MobileApp;

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
use App\Mail\UserAusiBookingCancellation;
use App\Mail\ConciergeAusiBookingCancellation;
use App\Models\AusiInspectionResult;
use App\Http\Controllers\Controller;

class AusiBookingMobileController extends Controller
{
    public function MobileServices()
    {
        return view('mobile-app.mobile-services');
    }
    public function ausiBookingUserMobile(Request $request)
    {
        return response()
            ->view('mobile-app.ausi.ausi-booking-mobile', [
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
            ->where('booking_status', '!=', 0)
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

        $areaLetter = strtoupper($towerLetter);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $groupAreas = $userGroup === 'lowrise'
            ? $lowrise
            : $highrise;

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

                $debug[] = "Resident found: YES";
                $debug[] = "Unit No: " . $resident->unit_no;

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();
                $slotTaken = AusiBooking::whereDate('booking_date', $bookingDate)
                    ->where('booking_time_slot', $request->booking_time_slot)
                    ->where('booking_status', '!=', 0)
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
                    ->where('booking_status', '!=', 0)
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

                if (!$email) {
                    return response()->json([
                        'message' => 'Missing email from request'
                    ], 401);
                }

                $booking = AusiBooking::create([
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'email' => $email,
                    'transaction_no' => '',
                    'unit_no' => strtoupper($resident->unit_no),
                    'resident_type' => $resident->resident_type,
                    'name' => strtoupper($request->name ?? 'Resident'),
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

    public function viewAusiBookingMobileHistory()
    {


        return view('mobile-app.ausi.ausi-booking-mobile-history');

    }

    public function getAusiBookingMobileHistory(Request $request)
    {

        $email = $request->email;
        $mobileUnit = $request->unit_name;


        $unitNo = $this->convertMobileUnitToUnitNo($mobileUnit);


        if (!$unitNo) {

            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $mobileUnit
            ], 422);

        }

        $bookings = AusiBooking::where('email', $email)
            ->where('unit_no', $unitNo)
            ->orderBy('created_at', 'desc')
            ->get();


        return response()->json([
            'unit_no' => $unitNo,
            'bookings' => $bookings
        ]);

    }

    private function convertMobileUnitToUnitNo($unitName)
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



    public function CancelAusiBookingMobile(AusiBooking $booking)
    {
        try {

            return DB::transaction(function () use ($booking) {

                $userEmail = request('email');
                $booking = AusiBooking::where('id', $booking->id)
                    ->lockForUpdate()
                    ->firstOrFail();


                if ($booking->booking_status == 0) {

                    return response()->json([
                        'message' => 'Booking is already cancelled.'
                    ], 422);

                }


                if ($booking->email !== $userEmail) {

                    return response()->json([
                        'message' => 'You are not allowed to cancel this booking.'
                    ], 403);

                }


                $startTime = explode('-', $booking->booking_time_slot)[0];

                $bookingDateTime = Carbon::parse(
                    $booking->booking_date . ' ' . trim($startTime)
                );


                $hoursRemaining = now()->diffInHours($bookingDateTime, false);


                if ($hoursRemaining < 12) {

                    return response()->json([
                        'message' => 'Cancellation is only allowed at least 12 hours before the booking schedule.'
                    ], 422);

                }

                $booking->load('user');
                $booking->booking_status = 0;
                $booking->cancelled_at = now();
                $booking->save();

                DB::afterCommit(function () use ($booking) {


                    if ($booking->email) {

                        Mail::to($booking->email)
                            ->queue(
                                new UserAusiBookingCancellation($booking)
                            );

                    }
                    Mail::to('concierge@twoserendra.com')
                        ->queue(
                            new ConciergeAusiBookingCancellation($booking)
                        );
                });

                return response()->json([
                    'message' => 'Booking cancelled successfully.'
                ]);

            });

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Failed to cancel booking.',
                'error' => $e->getMessage()
            ], 500);

        }
    }

    public function fetchAusiBookingMobile($id)
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
}
