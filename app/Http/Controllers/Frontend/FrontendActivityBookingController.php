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
use App\Models\ActivityBlocking;
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
    // public function ActivityNewBooking(Request $request)
    // {
    //     try {

    //         return DB::transaction(function () use ($request) {

    //             $selectedSlots = explode(',', $request->input('selected_slots_user'));
    //             $repeatCount = min(count($selectedSlots), 3);

    //             $activityIds = is_array($request->activity_id)
    //                 ? $request->activity_id
    //                 : explode(',', $request->activity_id);

    //             $activities = Activity::whereIn('id', $activityIds)->get()->keyBy('id');

    //             if (empty($activityIds)) {
    //                 throw new \Exception('Invalid activity selection.');
    //             }

    //             $bookingStartTime = Carbon::createFromFormat('h:i A', $request->booking_start_time)->format('H:i:s');
    //             $bookingEndTime = Carbon::createFromFormat('h:i A', $request->booking_end_time)->format('H:i:s');

    //             $resident = ResidentDetails::where('email', auth()->user()->email)->firstOrFail();
    //             if (!$resident) {
    //                 throw new \Exception('Unauthorized resident selection.');
    //             }

    //             $bookingType = strtoupper(trim($request->booking_type));
    //             $allowedTypes = ['20HRS', 'ADVANCED BOOKING'];

    //             if (!in_array($bookingType, $allowedTypes)) {
    //                 throw new \Exception('Invalid booking type.');
    //             }

    //             $now = Carbon::now();
    //             $requestedStart = Carbon::parse($request->booking_date . ' ' . $request->booking_start_time);
    //             $requestedEnd = Carbon::parse($request->booking_date . ' ' . $request->booking_end_time);

    //             $diffFromNow = $now->diffInHours($requestedStart, false);

    //             if ($diffFromNow < 1) {
    //                 throw new \Exception('Bookings must be made at least 1 hour in advance.');
    //             }

    //             foreach ($activityIds as $activityId) {

    //                 $maxHours = ($activityId == 3) ? 1 : 2;

    //                 $requestedDuration = $requestedStart->diffInHours($requestedEnd);

    //                 if ($requestedDuration > $maxHours) {
    //                     throw new \Exception('Maximum booking duration exceeded for this activity.');
    //                 }

    //                 $activity = $activities[$activityId] ?? null;

    //                 if (!$activity) {
    //                     throw new \Exception('Invalid activity selected.');
    //                 }

    //                 $activitySpace = $activity->activity_space;
    //                 $amenityId = $activity->amenity_id;

    //                 if ($bookingType === '20HRS') {

    //                     $exists = ActivityBooking::where('booking_date', $request->booking_date)
    //                         ->where('unit', strtoupper($resident->unit_no))
    //                         ->where('activity_id', $activityId)
    //                         ->where('booking_type', '20HRS')
    //                         ->where('booking_status', 1)
    //                         ->lockForUpdate()
    //                         ->exists();

    //                     if ($exists) {
    //                         throw new \Exception('This unit already has a 20HRS booking for this activity.');
    //                     }
    //                 }

    //                 $sharedActivityGroups = [
    //                     'almond_basketball' => [1, 2],
    //                     'almond_futsal' => [4, 5],
    //                     'almond_badminton' => [6, 7],
    //                     'sequoia_basketball' => [9, 10],
    //                     'sequoia_futsal' => [12, 13],
    //                 ];

    //                 $sharedActivityIds = [];

    //                 foreach ($sharedActivityGroups as $groupActivities) {
    //                     if (in_array($activityId, $groupActivities)) {
    //                         $sharedActivityIds = $groupActivities;
    //                         break;
    //                     }
    //                 }

    //                 $query = ActivityBooking::where('unit', strtoupper($resident->unit_no))
    //                     ->whereMonth('booking_date', Carbon::parse($request->booking_date)->month)
    //                     ->whereYear('booking_date', Carbon::parse($request->booking_date)->year)
    //                     ->where('booking_status', 1)
    //                     ->whereRaw("LOWER(TRIM(booking_type)) = 'advanced booking'")
    //                     ->lockForUpdate();

    //                 if (!empty($sharedActivityIds)) {
    //                     $query->whereIn('activity_id', $sharedActivityIds);
    //                 } else {
    //                     $query->where('activity_id', $activityId);
    //                 }

    //                 $count = $query->distinct('transaction_no')->count('transaction_no');

    //                 if ($count >= $activity->activity_max_booking) {
    //                     throw new \Exception('Booking limit reached for this unit.');
    //                 }

    //                 $blocked = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
    //                     $q->where('amenity_id', $amenityId);
    //                 })
    //                     ->where(function ($q) use ($request) {
    //                         $dayOfWeek = Carbon::parse($request->booking_date)->format('l');

    //                         $q->where(function ($q2) use ($dayOfWeek) {
    //                             $q2->where('repeat_weekly', true)
    //                                 ->where('day', $dayOfWeek);
    //                         });
    //                     })
    //                     ->get();

    //                 foreach ($blocked as $block) {
    //                     $blockStart = Carbon::parse("{$request->booking_date} {$block->start_time}");
    //                     $blockEnd = Carbon::parse("{$request->booking_date} {$block->end_time}");

    //                     if ($blockEnd <= $blockStart) {
    //                         $blockEnd->addDay();
    //                     }

    //                     if (
    //                         $bookingStartTime < $blockEnd->format('H:i:s') &&
    //                         $bookingEndTime > $blockStart->format('H:i:s')
    //                     ) {
    //                         throw new \Exception('This time is blocked.');
    //                     }
    //                 }

    //                 $existingBookings = ActivityBooking::where('booking_date', $request->booking_date)
    //                     ->where('booking_start_time', '<', $bookingEndTime)
    //                     ->where('booking_end_time', '>', $bookingStartTime)
    //                     ->where('booking_status', 1)
    //                     ->whereHas('activity', function ($q) use ($amenityId) {
    //                         $q->where('amenity_id', $amenityId);
    //                     })
    //                     ->lockForUpdate()
    //                     ->get();

    //                 $hasConflict = $existingBookings->contains(function ($b) use ($activitySpace) {
    //                     return (int) optional($b->activity)->activity_space !== (int) $activitySpace;
    //                 });

    //                 if ($hasConflict) {
    //                     throw new \Exception('This timeslot is already taken by an activity with a different activity space under this amenity.');
    //                 }

    //                 $schedule = ActivitySchedule::where('activity_id', $activityId)
    //                     ->where('day', Carbon::parse($request->booking_date)->format('l'))
    //                     ->first();

    //                 if (!$schedule) {
    //                     throw new \Exception('No schedule available.');
    //                 }

    //                 $scheduleStart = Carbon::parse($request->booking_date . ' ' . $schedule->start_time);
    //                 $scheduleEnd = Carbon::parse($request->booking_date . ' ' . $schedule->end_time);

    //                 if ($scheduleEnd->lessThanOrEqualTo($scheduleStart)) {
    //                     $scheduleEnd->addDay();
    //                 }

    //                 if ($requestedStart < $scheduleStart || $requestedEnd > $scheduleEnd) {
    //                     throw new \Exception('Outside allowed schedule.');
    //                 }

    //                 $durationHours = $requestedStart->diffInHours($requestedEnd);

    //                 if ($durationHours <= 0) {
    //                     throw new \Exception('Invalid booking duration.');
    //                 }

    //                 if ($durationHours !== count($selectedSlots)) {
    //                     throw new \Exception('Slot mismatch detected.');
    //                 }

    //                 if ($repeatCount <= 0) {
    //                     throw new \Exception('Invalid booking duration.');
    //                 }

    //                 $serverSlots = [];
    //                 $temp = $requestedStart->copy();

    //                 while ($temp < $requestedEnd) {
    //                     $serverSlots[] = $temp->format('H:i');
    //                     $temp->addHour();
    //                 }

    //                 $selectedSlots = array_map('strtoupper', $selectedSlots);
    //                 $serverSlots = array_map('strtoupper', $serverSlots);

    //                 if ($serverSlots !== $selectedSlots) {
    //                     throw new \Exception('Invalid slot selection (server mismatch).');
    //                 }

    //                 $bookingDateTime = Carbon::parse($request->booking_date . ' ' . $bookingStartTime);
    //                 $now = Carbon::now();
    //                 $diffHours = $now->diffInHours($bookingDateTime, false);

    //                 if ($bookingType === '20HRS') {
    //                     if ($diffHours > 20 || $diffHours < 0) {
    //                         throw new \Exception('Invalid 20HRS booking window.');
    //                     }
    //                 }

    //                 if ($bookingType === 'ADVANCED BOOKING') {
    //                     if ($diffHours < 25 || $diffHours > (9 * 24)) {
    //                         throw new \Exception('Invalid advanced booking window.');
    //                     }
    //                 }

    //                 for ($time = $requestedStart->copy(); $time < $requestedEnd; $time->addHour()) {

    //                     $hourStart = $time->copy();
    //                     $hourEnd = $time->copy()->addHour();

    //                     $hourlyCount = $existingBookings->filter(function ($b) use ($hourStart, $hourEnd, $activitySpace) {
    //                         return (int) optional($b->activity)->activity_space === (int) $activitySpace
    //                             && $b->booking_start_time < $hourEnd->format('H:i:s')
    //                             && $b->booking_end_time > $hourStart->format('H:i:s');
    //                     })->count();

    //                     if (($hourlyCount + $repeatCount) > $activitySpace) {
    //                         throw new \Exception('This timeslot is already booked or has reached the maximum limit for this activity.');
    //                     }
    //                 }
    //             }

    //             $lastTransaction = ActivityBooking::lockForUpdate()->latest('id')->first();

    //             $lastNumber = $lastTransaction
    //                 ? (int) str_replace('2SAM-', '', $lastTransaction->transaction_no)
    //                 : 0;

    //             $newTransactionNo = '2SAM-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

    //             $user = auth()->user();
    //             $firstBookingId = null;

    //             $simulated = $requestedStart->copy();
    //             $hoursAllowed = 0;

    //             while ($simulated < $requestedEnd && $hoursAllowed < $maxHours) {

    //                 $slotKey = $simulated->format('H:i');

    //                 $existing = $occupiedSlots[$slotKey] ?? null;

    //                 if (
    //                     $existing['blocked'] ?? false ||
    //                     ($existing['count'] > 0 && $existing['activity_space'] != $activitySpace) ||
    //                     ($existing['count'] ?? 0) >= $activitySpace
    //                 ) {
    //                     throw new \Exception("Selected time is no longer available.");
    //                 }

    //                 $simulated->addHour();
    //                 $hoursAllowed++;
    //             }

    //             foreach ($activityIds as $activityId) {
    //                 for ($i = 0; $i < $repeatCount; $i++) {

    //                     $booking = new ActivityBooking();
    //                     $booking->activity_id = $activityId;
    //                     $booking->user_id = $user->id;
    //                     $booking->created_by = $user->id;
    //                     $booking->lobby = strtoupper($user->name);
    //                     $booking->unit = strtoupper($resident->unit_no);
    //                     $booking->resident_type = strtoupper($resident->resident_type);
    //                     $booking->name = strtoupper($user->name);
    //                     $booking->contact_number = $user->contact_number ?? null;
    //                     $booking->booking_type = strtoupper($request->booking_type);
    //                     $booking->booking_date = $request->booking_date;
    //                     $booking->booking_start_time = $bookingStartTime;
    //                     $booking->booking_end_time = $bookingEndTime;
    //                     $booking->booking_status = 1;
    //                     $booking->transaction_no = $newTransactionNo;
    //                     $booking->save();

    //                     $firstBookingId ??= $booking->id;
    //                 }
    //             }

    //             $firstBooking = ActivityBooking::with('activity')->find($firstBookingId);

    //             DB::afterCommit(function () use ($firstBooking, $user, $resident, $firstBookingId) {
    //                 Mail::to($user->email)->queue(new UserAmenityBookingConfirmation($firstBooking));
    //                 Mail::to('concierge@twoserendra.com')->queue(new ConciergeAmenityBookingConfirmation($firstBooking));

    //                 $firstBooking->user->notify(new UserAmenityBookingBellNotification($firstBooking));

    //                 event(new NewRequestSubmitted(
    //                     strtoupper($resident->unit_no),
    //                     $firstBookingId
    //                 ));
    //             });

    //             Log::info('Booking attempt', [
    //                 'user_id' => auth()->id(),
    //                 'unit' => $resident->unit_no,
    //                 'slots' => $repeatCount
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Amenity Booking submitted successfully!',
    //                 'transaction_no' => $newTransactionNo
    //             ]);

    //         });

    //     } catch (\Exception $e) {

    //         Log::error('Booking failed', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 422);
    //     }
    // }


    // Refractored booking method step by step
    public function ActivityNewBooking(Request $request)
    {
        try {
            DB::beginTransaction();

            if (!auth()->check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = auth()->user();

            $resident = ResidentDetails::where('id', $request->resident_id)
                ->where('email', $user->email)
                ->first();

            if (!$resident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized unit selection.'
                ], 403);
            }

            $unit = strtoupper($resident->unit_no);

            $selectedSlots = explode(',', $request->input('selected_slots_user'));
            $repeatCount = min(count($selectedSlots), 3);
            $activityIds = is_array($request->activity_id) ? $request->activity_id : explode(',', $request->activity_id);

            $bookingStartTime = Carbon::createFromFormat('h:i A', $request->booking_start_time)->format('H:i:s');
            $bookingEndTime = Carbon::createFromFormat('h:i A', $request->booking_end_time)->format('H:i:s');


            $selectedDate = $request->booking_date;
            $selectedMonth = Carbon::parse($selectedDate)->month;
            $selectedYear = Carbon::parse($selectedDate)->year;

            foreach ($activityIds as $activityId) {
                $activity = Activity::find($activityId);
                $maxBookings = $activity->activity_max_booking ?? 2;

                if (!$activity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid activity selected.'
                    ], 400);
                }

                $sharedActivityGroups = [
                    'almond_basketball' => [1, 2],
                    'almond_futsal' => [4, 5],
                    'almond_badminton' => [6, 7],
                    'sequoia_basketball' => [9, 10],
                    'sequoia_futsal' => [12, 13],
                ];

                $sharedActivityIds = [];

                foreach ($sharedActivityGroups as $groupActivities) {
                    if (in_array($activityId, $groupActivities)) {
                        $sharedActivityIds = $groupActivities;
                        break;
                    }
                }


                $uniqueBookingCount = ActivityBooking::where('unit', strtoupper($resident->unit_no))
                    ->whereMonth('booking_date', $selectedMonth)
                    ->whereYear('booking_date', $selectedYear)
                    ->where('booking_status', 1)
                    ->whereRaw("TRIM(LOWER(booking_type)) = 'advanced booking'")
                    ->where(function ($q) use ($activityId, $sharedActivityIds) {
                        if (!empty($sharedActivityIds)) {
                            $q->whereIn('activity_id', $sharedActivityIds);
                        } else {
                            $q->where('activity_id', $activityId);
                        }
                    })
                    ->lockForUpdate()
                    ->groupBy('transaction_no')
                    ->count('transaction_no');

                if (($uniqueBookingCount + $repeatCount) > $maxBookings) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'You have reached the maximum allowed bookings for this unit for the selected month.'
                    ], 409);
                }

                $activitySpace = $activity->activity_space;
                $amenityId = $activity->amenity_id;

                if (strtoupper($request->booking_type) === '20HRS') {

                    $exists = ActivityBooking::where('booking_date', $request->booking_date)
                        ->where('unit', $unit) // ✅ FIXED
                        ->where('activity_id', $activityId)
                        ->where('booking_type', '20HRS')
                        ->where('booking_status', 1)
                        ->lockForUpdate()
                        ->exists();

                    if ($exists) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'This unit already has a 20HRS booking for this activity on the selected date.'
                        ], 409);
                    }
                }


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


                    if (($hourlyCount + $repeatCount) > $activitySpace) {
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
            $lastNumber = $lastTransaction
                ? ((int) str_replace('2SAM-', '', $lastTransaction->transaction_no))
                : 0;
            $newTransactionNo = '2SAM-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            $user = auth()->user();
            $firstBookingId = null;
            Log::info('Selected Count', ['count' => $repeatCount]);
            foreach ($activityIds as $activityId) {
                for ($i = 0; $i < $repeatCount; $i++) {
                    $booking = new ActivityBooking();
                    $booking->activity_id = $activityId;
                    $booking->user_id = $user->id;   // add this
                    $booking->created_by = $user->id;
                    $booking->lobby = strtoupper($user->name);
                    $booking->unit = $unit;
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
                event(new NewRequestSubmitted(
                    strtoupper($resident->unit_no),
                    $firstBookingId
                ));

            return response()->json([
                'success' => true,
                'message' => 'Amenity Booking submitted successfully!',
                'transaction_no' => $newTransactionNo
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
            'almond_futsal' => [4, 5],
            'almond_badminton' => [6, 7],
            'sequoia_basketball' => [9, 10],
            'sequoia_futsal' => [12, 13],

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

    // public function fetchAvailableTimesUser(Request $request)
    // {

    //     $bookingType = $request->input('booking_type'); 
    //     $now = Carbon::now();

    //     $activityId = $request->input('activity_id');
    //     $date = $request->input('booking_date');
    //     \Log::info("Fetching available times for Activity ID: $activityId, Date: $date");

    //     $activity = Activity::find($activityId);
    //     if (!$activity) {
    //         return response()->json(['error' => 'Activity not found'], 404);
    //     }

    //     $dayOfWeek = Carbon::parse($date)->format('l');
    //     $schedule = ActivitySchedule::where('activity_id', $activityId)
    //         ->where('day', $dayOfWeek)
    //         ->first();

    //     if (!$schedule || is_null($schedule->start_time) || is_null($schedule->end_time)) {
    //         return response()->json(['error' => 'No Schedule']);
    //     }

    //     $start = Carbon::parse($date . ' ' . $schedule->start_time);
    //     $end = Carbon::parse($date . ' ' . $schedule->end_time);

    //     if ($end->lessThanOrEqualTo($start)) {
    //         $end->addDay();
    //     }

    //     $activitySpace = $activity->activity_space;
    //     $amenityId = $activity->amenity_id;

    //     $bookedSlots = ActivityBooking::where('booking_date', $date)
    //         ->where('booking_status', 1)
    //         ->whereHas('activity', function ($query) use ($amenityId) {
    //             $query->where('amenity_id', $amenityId);
    //         })
    //         ->with('activity')
    //         ->get();

    //     $occupiedSlots = [];

    //     foreach ($bookedSlots as $booking) {
    //         $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
    //         $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

    //         if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
    //             $bookingEnd->addDay();
    //         }

    //         $bookingActivitySpace = $booking->activity->activity_space;

    //         for ($time = $bookingStart->copy(); $time < $bookingEnd; $time->addHour()) {
    //             $slotKey = $time->format('Y-m-d H:i');

    //             if (!isset($occupiedSlots[$slotKey])) {
    //                 $occupiedSlots[$slotKey] = [
    //                     'count' => 0,
    //                     'activity_space' => null,
    //                 ];
    //             }
    //             $occupiedSlots[$slotKey]['count']++;
    //             $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;
    //         }
    //     }

    //     $availableTimePairs = [];
    //     $currentSlotStart = $start->copy();

    //     while ($currentSlotStart < $end) {
    //         $currentSlotEnd = $currentSlotStart->copy()->addHour();
    //         $slotKey = $currentSlotStart->format('Y-m-d H:i');
    //         $hoursFromNow = $now->diffInHours($currentSlotStart, false);

    //         if ($hoursFromNow < 1) {
    //             $currentSlotStart->addHour();
    //             continue;
    //         }

    //         if ($bookingType === '20hrs') {
    //             if ($hoursFromNow > 20) {
    //                 $currentSlotStart->addHour();
    //                 continue;
    //             }
    //         }


    //         if ($bookingType === 'Advanced Booking') {
    //             if ($hoursFromNow < 25 || $hoursFromNow > (9 * 24)) {
    //                 $currentSlotStart->addHour();
    //                 continue;
    //             }
    //         }

    //         $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];

    //         if (
    //             $bookingType === '20hrs' ||
    //             $existingBooking['count'] < $activitySpace
    //         ) {
    //             if ($existingBooking['count'] == 0 || $existingBooking['activity_space'] == $activitySpace) {
    //                 $availableTimePairs[] = [
    //                     'start' => $currentSlotStart->format('h:i A'),
    //                     'end' => $currentSlotEnd->format('h:i A'),
    //                 ];
    //             }
    //         }

    //         $currentSlotStart->addHour();
    //     }

    //     return response()->json($availableTimePairs);
    // }

    public function fetchAvailableTimesUser(Request $request)
    {
        $bookingType = $request->input('booking_type');
        $now = Carbon::now();

        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');
        \Log::info("Fetching available times for Activity ID: $activityId, Date: $date");

        $activity = Activity::select('id', 'amenity_id', 'activity_space')->find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $dayOfWeek = Carbon::parse($date)->format('l');
        $schedule = ActivitySchedule::where('activity_id', $activityId)
            ->where('day', $dayOfWeek)
            ->first();

        if (
            !$schedule ||
            is_null($schedule->start_time) ||
            is_null($schedule->end_time) ||
            $schedule->start_time === '00:00:00' ||
            $schedule->end_time === '00:00:00'
        ) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse("{$date} {$schedule->start_time}");
        $end = Carbon::parse("{$date} {$schedule->end_time}");
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;


        $bookedSlots = ActivityBooking::with('activity:id,activity_space,amenity_id')
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($q) use ($amenityId) {
                $q->where('amenity_id', $amenityId);
            })
            ->get();


        $blockedSlots = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
            $q->where('amenity_id', $amenityId);
        })->where(function ($q) use ($dayOfWeek) {
            $q->where(function ($q2) use ($dayOfWeek) {
                $q2->where('repeat_weekly', true)->where('day', $dayOfWeek);
            });
        })->get();

        $occupiedSlots = [];

        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse("{$date} {$booking->booking_start_time}");
            $bookingEnd = Carbon::parse("{$date} {$booking->booking_end_time}");
            if ($bookingEnd->lessThanOrEqualTo($bookingStart))
                $bookingEnd->addDay();

            $bookingActivitySpace = $booking->activity->activity_space;

            while ($bookingStart < $bookingEnd) {
                $slotKey = $bookingStart->format('Y-m-d H:i');
                if (!isset($occupiedSlots[$slotKey])) {
                    $occupiedSlots[$slotKey] = [
                        'count' => 0,
                        'activity_space' => null,
                        'blocked' => false,
                    ];
                }
                $occupiedSlots[$slotKey]['count']++;
                $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;
                $bookingStart->addHour();
            }
        }

        foreach ($blockedSlots as $block) {
            $blockStart = Carbon::parse("{$date} {$block->start_time}");
            $blockEnd = Carbon::parse("{$date} {$block->end_time}");
            if ($blockEnd->lessThanOrEqualTo($blockStart))
                $blockEnd->addDay();

            while ($blockStart < $blockEnd) {
                $slotKey = $blockStart->format('Y-m-d H:i');
                $occupiedSlots[$slotKey] = ['blocked' => true];
                $blockStart->addHour();
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

            if ($bookingType === '20hrs' && $hoursFromNow > 20) {
                $currentSlotStart->addHour();
                continue;
            }

            if ($bookingType === 'Advanced Booking' && ($hoursFromNow < 25 || $hoursFromNow > (9 * 24))) {
                $currentSlotStart->addHour();
                continue;
            }

            $existing = $occupiedSlots[$slotKey] ?? [
                'count' => 0,
                'activity_space' => null,
                'blocked' => false,
            ];

            if (!empty($existing['blocked'])) {
                $currentSlotStart->addHour();
                continue;
            }

            if ($existing['count'] > 0 && $existing['activity_space'] != $activitySpace) {
                break;
            }

            if ($existing['count'] >= $activitySpace) {
                break;
            }

            $availableTimePairs[] = [
                'start' => $currentSlotStart->format('h:i A'),
                'end' => $currentSlotEnd->format('h:i A'),
            ];
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

        $activity = Activity::select('id', 'amenity_id', 'activity_space')->find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        $dayOfWeek = Carbon::parse($date)->format('l');

        $schedule = ActivitySchedule::where('activity_id', $activityId)
            ->where('day', $dayOfWeek)
            ->first();

        if (
            !$schedule || !$schedule->start_time || !$schedule->end_time ||
            $schedule->start_time === '00:00:00' || $schedule->end_time === '00:00:00'
        ) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse("{$date} {$startTime}");
        $end = Carbon::parse("{$date} {$schedule->end_time}");

        if ($end->lessThanOrEqualTo(Carbon::parse("{$date} {$schedule->start_time}"))) {
            $end->addDay();
        }

        $bookedSlots = ActivityBooking::with('activity:id,activity_space,amenity_id')
            ->whereHas('activity', function ($q) use ($amenityId) {
                $q->where('amenity_id', $amenityId);
            })
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get(['activity_id', 'booking_start_time', 'booking_end_time']);

        $blockedSlots = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
            $q->where('amenity_id', $amenityId);
        })
            ->where(function ($query) use ($dayOfWeek) {
                $query->where(function ($q) use ($dayOfWeek) {
                    $q->where('repeat_weekly', true)
                        ->where('day', $dayOfWeek);
                });
            })
            ->get();


        $occupiedSlots = [];


        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse("{$date} {$booking->booking_start_time}");
            $bookingEnd = Carbon::parse("{$date} {$booking->booking_end_time}");

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            $bookingActivitySpace = $booking->activity->activity_space;

            while ($bookingStart < $bookingEnd) {
                $slotKey = $bookingStart->format('H:i');

                if (!isset($occupiedSlots[$slotKey])) {
                    $occupiedSlots[$slotKey] = [
                        'count' => 0,
                        'activity_space' => null,
                        'blocked' => false,
                    ];
                }

                $occupiedSlots[$slotKey]['count']++;
                $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;

                $bookingStart->addHour();
            }
        }


        foreach ($blockedSlots as $block) {
            $blockStart = Carbon::parse("{$date} {$block->start_time}");
            $blockEnd = Carbon::parse("{$date} {$block->end_time}");

            if ($blockEnd->lessThanOrEqualTo($blockStart)) {
                $blockEnd->addDay();
            }

            while ($blockStart < $blockEnd) {
                $slotKey = $blockStart->format('H:i');

                $occupiedSlots[$slotKey] = [
                    'count' => 0,
                    'activity_space' => null,
                    'blocked' => true,
                ];

                $blockStart->addHour();
            }
        }

        $availableEndTimes = [];
        $currentSlotStart = clone $start;

        $maxHours = ($activityId == 3) ? 1 : 2;
        $addedHours = 0;

        while ($currentSlotStart < $end && $addedHours < $maxHours) {

            $slotKey = $currentSlotStart->format('H:i');

            $existing = $occupiedSlots[$slotKey] ?? [
                'count' => 0,
                'activity_space' => null,
                'blocked' => false,
            ];

            if (
                !empty($existing['blocked']) ||
                ($existing['count'] > 0 && $existing['activity_space'] != $activitySpace) ||
                $existing['count'] >= $activitySpace
            ) {
                break;
            }


            $currentSlotEnd = (clone $currentSlotStart)->addHour();

            $availableEndTimes[] = $currentSlotEnd->format('h:i A');

            $addedHours++;
            $currentSlotStart->addHour();
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
            $endTime->addDay();
        }

        $activity = Activity::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

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
                $bookingEnd->addDay();
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
        $user = auth()->user();

        $allowedUnits = ResidentDetails::where('email', $user->email)
            ->pluck('unit_no')
            ->map(fn($u) => strtoupper($u))
            ->toArray();

        if (empty($allowedUnits)) {
            return response()->json([
                'error' => 'Unauthorized access'
            ], 403);
        }

        $booking = ActivityBooking::with('activity')
            ->where('id', $id)
            ->whereIn('unit', $allowedUnits)
            ->first();

        if (!$booking) {
            return response()->json([
                'error' => 'Unauthorized or booking not found'
            ], 403);
        }

        $slotCount = ActivityBooking::where('transaction_no', $booking->transaction_no)
            ->whereIn('unit', $allowedUnits)
            ->count();

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'transaction_no' => $booking->transaction_no,
                'booking_type' => $booking->booking_type,
                'name' => $booking->name,
                'unit' => $booking->unit,
                'resident_type' => $booking->resident_type,
                'booking_date' => $booking->booking_date,
                'booking_start_time' => $booking->booking_start_time,
                'booking_end_time' => $booking->booking_end_time,
                'booking_status' => $booking->booking_status,
                'penalty_amount' => $booking->penalty_amount,
                'penalty_waived' => $booking->penalty_waived,
                'has_penalty' => $booking->has_penalty,
                'cancelled_at' => $booking->cancelled_at,
                'slot_count' => $slotCount,
                'activity' => [
                    'activity_name' => optional($booking->activity)->activity_name
                ]
            ],
            'within_penalty' => $booking->isWithin12Hours(),
        ]);
    }

    // public function cancelAmenityBooking(ActivityBooking $booking, Request $request)
    // {
    //     try {
    //         $booking->load('activity', 'user');

    //         if (!$booking->canCancel()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Booking cannot be cancelled.'
    //             ], 400);
    //         }

    //         $withPenalty = $booking->isWithin12Hours();

    //         if (!$request->has('confirm') && $withPenalty) {
    //             return response()->json([
    //                 'success' => true,
    //                 'requires_confirmation' => true,
    //                 'message' => " Cancelling within 12 hours will incur a ₱1000 penalty."
    //             ]);
    //         }

    //         if ($withPenalty) {
    //             $booking->applyCancellationPenalty();
    //             $booking->booking_status = 3;
    //         } else {
    //             $booking->booking_status = 2;
    //         }

    //         $booking->cancelled_at = now();
    //         $booking->cancelled_by = auth()->id();
    //         $booking->save();

    //         $penaltyAmount = $booking->penalty_amount ?? 0;


    //         $booking->user?->notify(new UserAmenityBookingBellNotification($booking));
    //         if ($booking->user?->email) {
    //             Mail::to($booking->user->email)->queue(
    //                 new UserAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
    //             );
    //         }
    //         Mail::to('concierge@twoserendra.com')->queue(
    //             new ConciergeAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'withPenalty' => $withPenalty,
    //             'penaltyAmount' => $penaltyAmount,
    //             'message' => $withPenalty
    //                 ? "Booking cancelled. ₱{$penaltyAmount} penalty will be applied."
    //                 : "Booking has been cancelled successfully."
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('Cancel Amenity Booking Error', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to cancel booking.'
    //         ], 500);
    //     }
    // }


    public function cancelAmenityBooking(ActivityBooking $booking, Request $request)
    {
        try {
            $request->validate([
                'confirm' => 'nullable|boolean'
            ]);

            return DB::transaction(function () use ($booking, $request) {

                $booking = ActivityBooking::where('id', $booking->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$booking) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking not found.'
                    ], 404);
                }

                if ($booking->user_id !== auth()->id()) {
                    if ($booking->user_id !== auth()->id()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only the user who created this booking can cancel it.'
                        ], 403);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to cancel this booking.'
                    ], 403);
                }

                $booking->load('activity', 'user');

                if (!$booking->canCancel()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking cannot be cancelled.'
                    ], 400);
                }

                $withPenalty = $booking->isWithin12Hours();


                if (!$request->boolean('confirm') && $withPenalty) {
                    return response()->json([
                        'success' => true,
                        'requires_confirmation' => true,
                        'penaltyAmount' => 1000,
                        'message' => "Cancelling within 12 hours will incur a ₱1000 penalty."
                    ]);
                }

                if ($withPenalty) {
                    $booking->applyCancellationPenalty();
                    $booking->booking_status = 3;
                } else {
                    $booking->booking_status = 2;
                }

                $booking->cancelled_at = now();
                $booking->cancelled_by = auth()->id();
                $booking->save();

                $penaltyAmount = $booking->penalty_amount ?? 0;

                DB::afterCommit(function () use ($booking, $withPenalty, $penaltyAmount) {
                    $booking->user?->notify(new UserAmenityBookingBellNotification($booking));

                    if ($booking->user?->email) {
                        Mail::to($booking->user->email)->queue(
                            new UserAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
                        );
                    }

                    Mail::to('concierge@twoserendra.com')->queue(
                        new ConciergeAmenityBookingCancellation($booking, $withPenalty, $penaltyAmount)
                    );
                });

                return response()->json([
                    'success' => true,
                    'withPenalty' => $withPenalty,
                    'penaltyAmount' => $penaltyAmount,
                    'message' => $withPenalty
                        ? "Booking cancelled. ₱{$penaltyAmount} penalty will be applied."
                        : "Booking has been cancelled successfully."
                ]);
            });

        } catch (\Exception $e) {
            \Log::error('Cancel Amenity Booking Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }

    public function showAmenityBookingDetails($id)
    {
        $booking = ActivityBooking::with(['user', 'activity'])->findOrFail($id);

        // ✅ get all slots under same transaction
        $slots = ActivityBooking::where('transaction_no', $booking->transaction_no)
            ->orderBy('booking_start_time')
            ->get();

        $slotCount = $slots->count();

        $startTime = $slots->first()->booking_start_time;
        $endTime = $slots->last()->booking_end_time;

        return view('frontend.user-amenity-booking-details', compact(
            'booking',
            'slots',
            'slotCount',
            'startTime',
            'endTime'
        ));
    }

    public function fetchAllSlotsUser(Request $request)
    {
        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');

        $activity = Activity::find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        $day = Carbon::parse($date)->format('l');

        $schedule = ActivitySchedule::where('activity_id', $activityId)
            ->where('day', $day)
            ->first();

        if (
            !$schedule ||
            is_null($schedule->start_time) ||
            is_null($schedule->end_time) ||
            $schedule->start_time === '00:00:00' ||
            $schedule->end_time === '00:00:00'
        ) {
            return response()->json([
                'error' => 'No Schedule'
            ]);
        }

        $start = Carbon::parse($date . ' ' . $schedule->start_time);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $bookings = ActivityBooking::where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($query) use ($amenityId) {
                $query->where('amenity_id', $amenityId);
            })
            ->with('activity')
            ->get();

        $blockedSlots = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
            $q->where('amenity_id', $amenityId);
        })->where(function ($query) use ($day) {
            $query->where(function ($q) use ($day) {
                $q->where('repeat_weekly', true)
                    ->where('day', $day);
            });
        })->get();

        $slots = [];

        while ($start < $end) {
            $slotStart = $start->copy();
            $slotEnd = $slotStart->copy()->addHour();

            $row = [
                'time_range' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'slots' => []
            ];

            $isBlocked = $blockedSlots->contains(function ($block) use ($slotStart, $slotEnd, $date) {
                $blockStart = Carbon::parse($date . ' ' . $block->start_time);
                $blockEnd = Carbon::parse($date . ' ' . $block->end_time);

                if ($blockEnd->lessThanOrEqualTo($blockStart)) {
                    $blockEnd->addDay();
                }

                return $blockStart->lt($slotEnd) && $blockEnd->gt($slotStart);
            });

            if ($isBlocked) {
                for ($i = 1; $i <= $activitySpace; $i++) {
                    $row['slots'][] = 'Blocked';
                }

                $slots[] = $row;
                $start->addHour();
                continue;
            }


            $overlappingBookings = $bookings->filter(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                    $bookingEnd->addDay();
                }

                return $bookingStart->lt($slotEnd) && $bookingEnd->gt($slotStart);
            });


            $hasDifferentSpace = $overlappingBookings->contains(function ($booking) use ($activitySpace) {
                return $booking->activity
                    && $booking->activity->activity_space != $activitySpace;
            });

            if ($hasDifferentSpace) {


                $conflictingActivities = $overlappingBookings
                    ->map(function ($booking) {
                        return $booking->activity ? $booking->activity->activity_name : null;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                for ($i = 1; $i <= $activitySpace; $i++) {
                    $row['slots'][] = implode(', ', $conflictingActivities);
                }

            } else {

                $slotIndex = 0;

                foreach ($overlappingBookings as $booking) {
                    if ($slotIndex < $activitySpace) {
                        $row['slots'][] = $booking->activity->activity_name;
                        $slotIndex++;
                    }
                }

                while ($slotIndex < $activitySpace) {
                    $row['slots'][] = 'Available';
                    $slotIndex++;
                }
            }

            $slots[] = $row;
            $start->addHour();
        }

        return response()->json([
            'activity_space' => $activitySpace,
            'slots' => $slots
        ]);
    }
}
