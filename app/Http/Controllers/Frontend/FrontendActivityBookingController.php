<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityBooking;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\ResidentDetails;
use App\Models\ActivitySchedule;
use Carbon\Carbon;
use App\Events\NewRequestSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityDateBlocking;
use App\Events\GreaseTrapBookingCreated;
use Illuminate\Support\Facades\Mail;
use App\Notifications\UserAmenityBookingBellNotification;
use App\Mail\UserAmenityBookingConfirmation;
use App\Mail\ConciergeAmenityBookingConfirmation;
use App\Mail\UserAmenityBookingCancellation;
use App\Mail\ConciergeAmenityBookingCancellation;


class FrontendActivityBookingController extends Controller
{
    public function ActivityNewBooking(Request $request)
    {
        try {
            DB::beginTransaction();

            $selectedSlots = explode(',', $request->input('selected_slots_user'));
            $repeatCount = count($selectedSlots);
            $activityIds = is_array($request->activity_id) ? $request->activity_id : explode(',', $request->activity_id);

            $bookingStartTime = Carbon::createFromFormat('h:i A', $request->booking_start_time)->format('H:i:s');
            $bookingEndTime = Carbon::createFromFormat('h:i A', $request->booking_end_time)->format('H:i:s');

            foreach ($activityIds as $activityId) {
                $activity = Activity::find($activityId);

                if (!$activity) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Invalid activity selected.'], 400);
                }

                $activitySpace = $activity->activity_space;
                $amenityId = $activity->amenity_id;

                $existingBookings = ActivityBooking::where('booking_date', $request->booking_date)
                    ->where('booking_start_time', '<', $bookingEndTime)
                    ->where('booking_end_time', '>', $bookingStartTime)
                    ->where('booking_status', 1)
                    ->whereHas('activity', function ($q) use ($amenityId) {
                        $q->where('amenity_id', $amenityId);
                    })
                    ->lockForUpdate()
                    ->get();

                $hasDifferentActivitySpace = $existingBookings->contains(function ($b) use ($activitySpace) {
                    return (int) optional($b->activity)->activity_space !== (int) $activitySpace;
                });

                if ($hasDifferentActivitySpace) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This timeslot is already taken by an activity with a different activity space under this amenity.'
                    ], 409);
                }


                $requestedStart = Carbon::createFromFormat('H:i:s', $bookingStartTime);
                $requestedEnd = Carbon::createFromFormat('H:i:s', $bookingEndTime);

                $isConflict = false;

                for ($time = $requestedStart->copy(); $time < $requestedEnd; $time->addHour()) {
                    $hourStart = $time->copy();
                    $hourEnd = $time->copy()->addHour();

                    $hourlyCount = $existingBookings->filter(function ($b) use ($hourStart, $hourEnd, $activitySpace) {

                        return (int) optional($b->activity)->activity_space === (int) $activitySpace
                            && $b->booking_start_time < $hourEnd->format('H:i:s')
                            && $b->booking_end_time > $hourStart->format('H:i:s');

                    })->count();


                    if ($hourlyCount >= $activitySpace) {
                        $isConflict = true;
                        break;
                    }
                }

                if ($isConflict) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This timeslot is already booked or has reached the maximum limit for this activity.'
                    ], 409);
                }
            }

            $lastTransaction = ActivityBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction ? ((int) str_replace('2s-', '', $lastTransaction->transaction_no)) : 0;
            $newTransactionNo = '2s-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            $resident = ResidentDetails::findOrFail($request->resident_email_id); // Selected resident
            $user = auth()->user();
            $firstBookingId = null;
            Log::info('Selected Count', ['count' => $repeatCount]);
            foreach ($activityIds as $activityId) {
                for ($i = 0; $i < $repeatCount; $i++) {
                    $booking = new ActivityBooking();
                    $booking->activity_id = $activityId;
                    $booking->user_id = $user->id;
                    $booking->lobby = strtoupper($user->name);
                    $booking->unit = strtoupper($resident->unit_no);
                    $booking->resident_type = strtoupper($resident->resident_type);
                    $booking->name = strtoupper($user->name);
                    $booking->contact_number = $user->contact_number ?? null;
                    $booking->booking_type = strtoupper($request->booking_type);
                    $booking->booking_date = $request->booking_date;
                    $booking->booking_start_time = $bookingStartTime;
                    $booking->booking_end_time = $bookingEndTime;
                    $booking->booking_status = 1;
                    $booking->transaction_no = $newTransactionNo;
                    $booking->save();

                    if ($firstBookingId === null)
                        $firstBookingId = $booking->id;
                }

            }

            $firstBooking = ActivityBooking::with('activity')->find($firstBookingId);


            Mail::to($user->email)
                ->queue(new UserAmenityBookingConfirmation($firstBooking));

            Mail::to('concierge@twoserendra.com')
                ->queue(new ConciergeAmenityBookingConfirmation($firstBooking));
            $firstBooking->user->notify(new UserAmenityBookingBellNotification($firstBooking));
            DB::commit();
            if ($firstBookingId)
                event(new NewRequestSubmitted($request->unit, $firstBookingId));

            return response()->json([
                'success' => true,
                'message' => 'Booking submitted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function fetchBlockDates(Request $request)
    {
        $amenityId = $request->input('amenity_id');
        $blockedDatesQuery = ActivityDateBlocking::where('blocking_status', 1);

        if ($amenityId) {
            $blockedDatesQuery->where('amenity_id', $amenityId);
        }
        $blockedDates = $blockedDatesQuery->get();
        $formattedDates = [];

        foreach ($blockedDates as $block) {
            $start = new \DateTime($block->date_blocking_start);
            $end = new \DateTime($block->date_blocking_end);

            while ($start <= $end) {
                $formattedDates[] = $start->format('Y-m-d');
                $start->modify('+1 day');
            }
        }
        return response()->json($formattedDates);
    }

    public function checkUnitBooking(Request $request)
    {
        // dd($request->all());
        $unit = $request->input('unit');
        $activity_id = (int) $request->input('activity_id');
        $selectedDate = $request->input('dateField');

        if (!$unit || !$activity_id) {
            Log::warning('Invalid request: Missing unit or activity_id');
            return response()->json([
                'success' => false,
                'message' => 'Residence is required.',
            ]);
        }

        if (!$selectedDate) {
            Log::warning('Invalid request: Missing dateField');
            return response()->json([
                'success' => false,
                'message' => 'Please select a date before checking bookings.',
            ]);
        }

        Log::info('Check Unit Booking Request:', [
            'unit' => $unit,
            'activity_id' => $activity_id,
            'selectedDate' => $selectedDate
        ]);

        $activity = Activity::find($activity_id);
        if (!$activity) {
            Log::warning('Activity not found', ['activity_id' => $activity_id]);
            return response()->json([
                'success' => false,
                'message' => 'Activity not found.',
            ]);
        }

        $maxBookings = $activity->activity_max_booking ?? 2;
        Log::info('Max Bookings Retrieved:', ['maxBookings' => $maxBookings]);
        $selectedMonth = Carbon::parse($selectedDate)->month;
        $selectedYear = Carbon::parse($selectedDate)->year;
        Log::info('Selected Month & Year:', ['month' => $selectedMonth, 'year' => $selectedYear]);

        $sharedActivityGroups = [
            'almond_basketball' => [1, 2],
            'sequoia_basketball' => [4, 5],
        ];

        $sharedActivityIds = [];

        foreach ($sharedActivityGroups as $groupActivities) {
            if (in_array($activity_id, $groupActivities)) {
                $sharedActivityIds = $groupActivities;
                break;
            }
        }

        Log::info('Shared Activity IDs:', ['sharedActivityIds' => $sharedActivityIds]);

        $query = ActivityBooking::where('unit', $unit)
            ->whereMonth('booking_date', $selectedMonth)
            ->whereYear('booking_date', $selectedYear)
            ->where('booking_status', 1)
            ->whereRaw("TRIM(LOWER(booking_type)) = 'advanced booking'");

        if (!empty($sharedActivityIds)) {
            $query->whereIn('activity_id', $sharedActivityIds);
        } else {
            $query->where('activity_id', $activity_id);
        }


        $uniqueBookingCount = $query->select('transaction_no')
            ->groupBy('transaction_no')
            ->get()
            ->count();

        Log::info('Booking Count:', ['unit' => $unit, 'count' => $uniqueBookingCount, 'maxBookings' => $maxBookings]);

        return response()->json([
            'success' => true,
            'count' => $uniqueBookingCount,
            'maxBookings' => $maxBookings,
        ]);
    }

    public function fetchAvailableTimesUser(Request $request)
    {

        $bookingType = $request->input('booking_type'); // '20hrs' or 'Advanced Booking'
        $now = Carbon::now();

        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');
        \Log::info("Fetching available times for Activity ID: $activityId, Date: $date");

        $activity = Activity::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $dayOfWeek = Carbon::parse($date)->format('l');
        $schedule = ActivitySchedule::where('activity_id', $activityId)
            ->where('day', $dayOfWeek)
            ->first();

        if (!$schedule || is_null($schedule->start_time) || is_null($schedule->end_time)) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse($date . ' ' . $schedule->start_time);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        $bookedSlots = ActivityBooking::where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($query) use ($amenityId) {
                $query->where('amenity_id', $amenityId);
            })
            ->with('activity')
            ->get();

        $occupiedSlots = [];

        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            $bookingActivitySpace = $booking->activity->activity_space;

            for ($time = $bookingStart->copy(); $time < $bookingEnd; $time->addHour()) {
                $slotKey = $time->format('Y-m-d H:i');

                if (!isset($occupiedSlots[$slotKey])) {
                    $occupiedSlots[$slotKey] = [
                        'count' => 0,
                        'activity_space' => null,
                    ];
                }
                $occupiedSlots[$slotKey]['count']++;
                $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;
            }
        }

        $availableTimePairs = [];
        $currentSlotStart = $start->copy();

        while ($currentSlotStart < $end) {
            $currentSlotEnd = $currentSlotStart->copy()->addHour();
            $slotKey = $currentSlotStart->format('Y-m-d H:i');
            $hoursFromNow = $now->diffInHours($currentSlotStart, false);

            if ($hoursFromNow < 1) {
                $currentSlotStart->addHour();
                continue;
            }

            if ($bookingType === '20hrs') {
                if ($hoursFromNow > 20) {
                    $currentSlotStart->addHour();
                    continue;
                }
            }


            if ($bookingType === 'Advanced Booking') {
                if ($hoursFromNow < 25 || $hoursFromNow > (9 * 24)) {
                    $currentSlotStart->addHour();
                    continue;
                }
            }

            $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];

            if (
                $bookingType === '20hrs' ||
                $existingBooking['count'] < $activitySpace
            ) {
                if ($existingBooking['count'] == 0 || $existingBooking['activity_space'] == $activitySpace) {
                    $availableTimePairs[] = [
                        'start' => $currentSlotStart->format('h:i A'),
                        'end' => $currentSlotEnd->format('h:i A'),
                    ];
                }
            }

            $currentSlotStart->addHour();
        }

        return response()->json($availableTimePairs);
    }



    public function fetchEndTimesUser(Request $request)
    {
        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');
        $startTime = $request->input('start_time');

        \Log::info("Fetching end times for Activity ID: $activityId, Date: $date, Start Time: $startTime");

        $activity = Activity::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        $dayOfWeek = Carbon::parse($date)->format('l');
        $schedule = ActivitySchedule::where('activity_id', $activityId)
            ->where('day', $dayOfWeek)
            ->first();

        if (!$schedule || is_null($schedule->start_time) || is_null($schedule->end_time)) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse($date . ' ' . $startTime);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $bookedSlots = ActivityBooking::where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($query) use ($amenityId) {
                $query->where('amenity_id', $amenityId);
            })
            ->with('activity')
            ->get();

        $occupiedSlots = [];
        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            $bookingActivitySpace = $booking->activity->activity_space;

            for ($time = $bookingStart->copy(); $time < $bookingEnd; $time->addHour()) {
                $slotKey = $time->format('Y-m-d H:i');
                if (!isset($occupiedSlots[$slotKey])) {
                    $occupiedSlots[$slotKey] = [
                        'count' => 0,
                        'activity_space' => null,
                    ];
                }
                $occupiedSlots[$slotKey]['count']++;
                $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;
            }
        }
        // $availableEndTimes = [];
        // $currentSlotStart = $start->copy();

        // while ($currentSlotStart < $end) {
        //     $currentSlotEnd = $currentSlotStart->copy()->addHour();
        //     $slotKey = $currentSlotStart->format('Y-m-d H:i');

        //     $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];
        //     if ($existingBooking['count'] < $activitySpace) {
        //         if ($existingBooking['count'] == 0 || $existingBooking['activity_space'] == $activitySpace) {
        //             $availableEndTimes[] = $currentSlotEnd->format('h:i A');
        //             $currentSlotStart->addHour();
        //             continue;
        //         }
        //     }
        //     break;
        // }

        $availableEndTimes = [];
        $currentSlotStart = $start->copy();
        $maxHours = 2;
        $addedHours = 0;

        while ($currentSlotStart < $end && $addedHours < $maxHours) {
            $currentSlotEnd = $currentSlotStart->copy()->addHour();
            $slotKey = $currentSlotStart->format('Y-m-d H:i');

            $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];

            if ($existingBooking['count'] < $activitySpace) {
                if (
                    $existingBooking['count'] == 0 ||
                    $existingBooking['activity_space'] == $activitySpace
                ) {
                    $availableEndTimes[] = $currentSlotEnd->format('h:i A');
                    $addedHours++;
                    $currentSlotStart->addHour();
                    continue;
                }
            }

            break; // stop on first blocked slot
        }

        return response()->json([
            'availableEndTimes' => $availableEndTimes,
        ]);
    }





    public function fetchAvailableSlotsUser(Request $request)
    {
        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');
        $startTime = Carbon::parse($date . ' ' . $request->input('start_time'));
        $endTime = Carbon::parse($date . ' ' . $request->input('end_time'));

        if ($endTime->lessThanOrEqualTo($startTime)) {
            $endTime->addDay(); // Handle overnight end time
        }

        $activity = Activity::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        // Fetch all bookings that overlap with the selected time range
        $overlappingBookings = ActivityBooking::whereHas('activity', function ($query) use ($amenityId) {
            $query->where('amenity_id', $amenityId);
        })
            ->where('booking_status', 1)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereRaw("
            (
                STR_TO_DATE(CONCAT(booking_date, ' ', booking_start_time), '%Y-%m-%d %H:%i') < ?
                AND
                (
                    CASE 
                        WHEN TIME_TO_SEC(booking_end_time) <= TIME_TO_SEC(booking_start_time) 
                        THEN DATE_ADD(STR_TO_DATE(CONCAT(booking_date, ' ', booking_end_time), '%Y-%m-%d %H:%i'), INTERVAL 1 DAY)
                        ELSE STR_TO_DATE(CONCAT(booking_date, ' ', booking_end_time), '%Y-%m-%d %H:%i')
                    END
                ) > ?
            )
        ", [$endTime, $startTime]);
            })
            ->with('activity')
            ->get();

        $timeSlots = [];

        foreach ($overlappingBookings as $booking) {
            $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay(); // Handle overnight booking
            }

            for ($time = $bookingStart->copy(); $time < $bookingEnd; $time->addMinutes(1)) {
                $key = $time->format('Y-m-d H:i');
                $timeSlots[$key] = ($timeSlots[$key] ?? 0) + 1;
            }
        }

        $maxConcurrent = 0;
        for ($time = $startTime->copy(); $time < $endTime; $time->addMinutes(1)) {
            $key = $time->format('Y-m-d H:i');
            $maxConcurrent = max($maxConcurrent, $timeSlots[$key] ?? 0);
        }

        $bookedSlots = [];
        for ($i = 1; $i <= $maxConcurrent; $i++) {
            $bookedSlots[] = $i;
        }

        return response()->json([
            'activity_space' => $activitySpace,
            'booked_slots' => $bookedSlots
        ]);
    }

    public function getActivityBookingDetails($id)
    {
        $booking = ActivityBooking::with('activity')->find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        return response()->json([
            'booking' => $booking,
        ]);
    }

    public function cancelAmenityBooking(ActivityBooking $booking)
    {
        try {
            $booking->load('activity', 'user');

            $penaltyHours = 12;
            $penaltyAmount = 1000;
            $bookingDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $now = now();

            $hoursDiff = $now->diffInHours($bookingDateTime, false);
            $withPenalty = $hoursDiff < $penaltyHours;

            $booking->booking_status = $withPenalty ? 3 : 2;
            $booking->save();

            $booking->user?->notify(new UserAmenityBookingBellNotification($booking));


            if ($booking->user?->email) {
                Mail::to($booking->user->email)->queue(
                    new UserAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
                );
            }
            Mail::to('concierge@twoserendra.com')->queue(
                new ConciergeAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
            );

            return response()->json([
                'success' => true,
                'withPenalty' => $withPenalty,
                'penaltyAmount' => $withPenalty ? $penaltyAmount : 0,
                'message' => $withPenalty
                    ? "Booking cancelled. ₱{$penaltyAmount} penalty will be applied."
                    : "Booking has been cancelled successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }

    public function showAmenityBookingDetails($id)
    {
        $booking = ActivityBooking::with('user', 'activity')->findOrFail($id);
        return view('frontend.user-amenity-booking-details', compact('booking'));
    }



}
