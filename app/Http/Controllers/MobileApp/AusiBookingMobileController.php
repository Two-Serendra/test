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
            "Red Oak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];

        $lastSpace = strrpos($unitName, ' ');

        if ($lastSpace === false) {
            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $unitName
            ], 422);
        }

        $tower = trim(substr($unitName, 0, $lastSpace));
        $number = trim(substr($unitName, $lastSpace + 1));

        $towerLetter = $map[$tower] ?? null;

        if (!$towerLetter) {
            return response()->json([
                'message' => 'Unknown tower',
                'tower' => $tower,
            ], 422);
        }

        $unitNo = $number . $towerLetter;

        $areaLetter = strtoupper($towerLetter);
        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        if (!$request->filled('date')) {

            $slots = [
                '8:00 AM - 8:30 AM',
                '8:30 AM - 9:00 AM',
                '9:00 AM - 9:30 AM',
                '9:30 AM - 10:00 AM',
                '10:00 AM - 10:30 AM',
                '10:30 AM - 11:00 AM',
                '11:00 AM - 11:30 AM',
                '11:30 AM - 12:00 PM',
                '1:00 PM - 1:30 PM',
                '1:30 PM - 2:00 PM',
                '2:00 PM - 2:30 PM',
                '2:30 PM - 3:00 PM',
                '3:00 PM - 3:30 PM',
                '3:30 PM - 4:00 PM',
                '4:00 PM - 4:30 PM',
                '4:30 PM - 5:00 PM',
            ];

            $bookings = AusiBooking::where('booking_status', '!=', 0)
                ->get([
                    'booking_date',
                    'booking_time_slot',
                    'unit_area'
                ]);

            $dates = [];

            foreach ($bookings as $booking) {

                $date = Carbon::parse($booking->booking_date)->format('Y-m-d');

                if (!isset($dates[$date])) {
                    $dates[$date] = [];
                }

                if (!isset($dates[$date][$booking->booking_time_slot])) {

                    $dates[$date][$booking->booking_time_slot] = [
                        'lowrise' => false,
                        'highrise' => false,
                    ];
                }

                if (in_array($booking->unit_area, $lowrise)) {
                    $dates[$date][$booking->booking_time_slot]['lowrise'] = true;
                }

                if (in_array($booking->unit_area, $highrise)) {
                    $dates[$date][$booking->booking_time_slot]['highrise'] = true;
                }
            }

            $disabledDates = [];

            foreach ($dates as $date => $slotData) {

                $requiredSlots = $slots;

                // Tuesday morning slots are unavailable to everyone.
                if (Carbon::parse($date)->isTuesday()) {
                    $requiredSlots = array_diff($requiredSlots, [
                        '8:00 AM - 8:30 AM',
                        '8:30 AM - 9:00 AM',
                        '9:00 AM - 9:30 AM',
                        '9:30 AM - 10:00 AM',
                    ]);
                }

                $full = true;

                foreach ($requiredSlots as $slot) {

                    if (
                        empty($slotData[$slot]) ||
                        !$slotData[$slot][$userGroup]
                    ) {
                        $full = false;
                        break;
                    }
                }

                if ($full) {
                    $disabledDates[] = $date;
                }
            }

            return response()->json([
                'disabled_dates' => array_values($disabledDates)
            ]);
        }

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

        if (Carbon::parse($request->date)->isTuesday()) {
            $blockedForUser = array_merge($blockedForUser, [
                '8:00 AM - 8:30 AM',
                '8:30 AM - 9:00 AM',
                '9:00 AM - 9:30 AM',
                '9:30 AM - 10:00 AM',
            ]);
        }

        $blockedForUser = array_unique($blockedForUser);

        \Log::info('MOBILE AUSI SLOT REQUEST', [
            'unit_name' => $unitName,
            'unit_no' => $unitNo,
            'blocked_for_user' => $blockedForUser,
        ]);

        return response()->json([
            'blocked_for_user' => array_values($blockedForUser),
            'raw_status' => $slotStatus
        ]);
    }

    public function storeAusiBookingMobile(Request $request)
    {
        \Log::info('AUSI MOBILE HIT', $request->all());

        \Log::info('SESSION DEBUG', [
            'session_id' => session()->getId(),
            'csrf_session' => session()->token(),
            'csrf_request' => request()->header('X-CSRF-TOKEN'),
            'cookies' => request()->cookies->all(),
        ]);

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
            "Red Oak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];

        $lastSpace = strrpos($unitName, ' ');

        if ($lastSpace === false) {
            return response()->json([
                'message' => 'Invalid unit format',
                'unit' => $unitName
            ], 422);
        }

        $tower = trim(substr($unitName, 0, $lastSpace));
        $number = trim(substr($unitName, $lastSpace + 1));

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
                $residentType = $request->mobile_unit_role;
                $unitNo = strtoupper($number . $areaLetter);

                if (!$email) {
                    return response()->json([
                        'message' => 'Missing email'
                    ], 401);
                }

                $debug[] = "Unit No: " . $unitNo;
                $debug[] = "Resident Type: " . $residentType;

                $bookingDateCarbon = Carbon::parse($request->booking_date);
                $bookingDate = $bookingDateCarbon->toDateString();

                // Tuesday rule
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
                            'message' => 'On Tuesdays, AUSI bookings start at 10:00 AM.',
                            'type' => 'tuesday_restricted_slot'
                        ], 422);
                    }
                }

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

                $bookingYear = $bookingDateCarbon->year;

                $existingUnitBooking = AusiBooking::where('unit_no', $unitNo)
                    ->where('booking_status', '!=', 0)
                    ->whereYear('booking_date', $bookingYear)
                    ->lockForUpdate()
                    ->exists();

                if ($existingUnitBooking) {
                    DB::rollBack();

                    return response()->json([
                        'message' => "This unit has already used its AUSI booking for {$bookingYear}. If you wish to book again, please contact the Engineering Department for assistance.",
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $booking = AusiBooking::create([
                    'user_id' => null,
                    'created_by' => null,
                    'email' => $email,
                    'transaction_no' => '',
                    'unit_no' => $unitNo,
                    'resident_type' => $residentType,
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
            "Red Oak" => "G",
            "Meranti" => "H",
            "Sequoia" => "I",
        ];

        $unitName = trim($unitName);

        if (!$unitName) {
            return null;
        }

        $lastSpace = strrpos($unitName, ' ');

        if ($lastSpace === false) {
            return null;
        }

        $tower = trim(substr($unitName, 0, $lastSpace));
        $number = trim(substr($unitName, $lastSpace + 1));

        if (!$tower || !$number) {
            return null;
        }

        $towerLetter = $map[$tower] ?? null;

        if (!$towerLetter) {
            return null;
        }

        return strtoupper($number . $towerLetter);
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
