<?php

namespace App\Http\Controllers\FrontendControllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use App\Models\AmenityBooking;
use App\Models\Amenity;
use App\Models\ActivitySchedule;
use Carbon\Carbon;
use App\Events\NewRequestSubmitted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function ActivityBooking(Request $request)
    {
        $activity_bookings = AmenityBooking::with('activity')->paginate(5);
        $activities = Activity::all();
        $amenities = Amenity::all();
        return view('frontend.amenities-booking', compact('activity_bookings', 'activities', 'amenities'));
    }

    public function SlotFront(Request $request)
    {
        $activity_bookings = AmenityBooking::with('activity')->paginate(5);
        $activities = Activity::all();
        $amenities = Amenity::all();
        return view('frontend.slot-front', compact('activity_bookings', 'activities', 'amenities'));
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

        if (!$schedule) {
            return response()->json(['error' => 'No schedule for this activity on selected day'], 404);
        }

        $start = Carbon::parse($date . ' ' . $schedule->start_time);
        $end = Carbon::parse($date . ' ' . $schedule->end_time);

        // Handle overnight schedule
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $bookings = AmenityBooking::where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($query) use ($amenityId) {
                $query->where('amenity_id', $amenityId);
            })
            ->with('activity')
            ->get();

        $slots = [];

        while ($start < $end) {
            $slotStart = $start->copy();
            $slotEnd = $slotStart->copy()->addHour();

            $row = [
                'time_range' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'slots' => []
            ];

            $overlappingBookings = $bookings->filter(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                // Handle overnight bookings
                if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                    $bookingEnd->addDay();
                }

                return $bookingStart->lt($slotEnd) && $bookingEnd->gt($slotStart);
            });

            $sameActivityCount = $overlappingBookings->filter(function ($booking) use ($activity) {
                return $booking->activity->id === $activity->id;
            })->count();

            $hasConflictingActivity = $overlappingBookings->contains(function ($booking) use ($activity) {
                return $booking->activity->id !== $activity->id;
            });

            for ($i = 1; $i <= $activitySpace; $i++) {
                if ($hasConflictingActivity) {
                    $row['slots'][] = 'Unavailable';
                } elseif ($sameActivityCount >= $i) {
                    $row['slots'][] = 'Booked';
                } else {
                    $row['slots'][] = 'Available';
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


    public function NewBooking(Request $request)
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

                $existingBookings = AmenityBooking::where('booking_date', $request->booking_date)
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

            $lastTransaction = AmenityBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction ? ((int) str_replace('2s-', '', $lastTransaction->transaction_no)) : 0;
            $newTransactionNo = '2s-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

            $firstBookingId = null;
            Log::info('Selected Count', ['count' => $repeatCount]);
            foreach ($activityIds as $activityId) {
                for ($i = 0; $i < $repeatCount; $i++) {
                    $newBooking = new AmenityBooking();
                    $newBooking->activity_id = $activityId;
                    $newBooking->lobby = auth()->user()->name;
                    $newBooking->unit = strtoupper($request->unit);
                    $newBooking->resident_type = strtoupper($request->selectResidentType);
                    $newBooking->name = strtoupper($request->name);
                    $newBooking->contact_number = $request->contact_number;
                    $newBooking->booking_type = strtoupper($request->booking_type);
                    $newBooking->booking_date = $request->booking_date;
                    $newBooking->booking_start_time = $bookingStartTime;
                    $newBooking->booking_end_time = $bookingEndTime;
                    $newBooking->booking_status = 1;
                    $newBooking->transaction_no = $newTransactionNo;
                    $newBooking->save();

                    if ($firstBookingId === null)
                        $firstBookingId = $newBooking->id;
                }
            }

            DB::commit();

            // event after commit
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

    // WORKING CODE display all the time slots without filtering the past times

    public function fetchAvailableTimesUser(Request $request)
    {
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

        $bookedSlots = AmenityBooking::where('booking_date', $date)
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

            // 🟢 Removed the time filtering (below current time) check
            $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];

            if ($existingBooking['count'] < $activitySpace) {
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

        // Fetch bookings under the same amenity on the selected date
        $bookedSlots = AmenityBooking::where('booking_date', $date)
            ->where('booking_status', 1)
            ->whereHas('activity', function ($query) use ($amenityId) {
                $query->where('amenity_id', $amenityId);
            })
            ->with('activity')
            ->get();

        $occupiedSlots = [];

        // Count how many times each hour is booked
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

        // Generate available end times from selected start time up to scheduled end
        $availableEndTimes = [];
        $currentSlotStart = $start->copy();

        while ($currentSlotStart < $end) {
            $currentSlotEnd = $currentSlotStart->copy()->addHour();
            $slotKey = $currentSlotStart->format('Y-m-d H:i');

            $existingBooking = $occupiedSlots[$slotKey] ?? ['count' => 0, 'activity_space' => null];

            // Check if slot still has space
            if ($existingBooking['count'] < $activitySpace) {
                if ($existingBooking['count'] == 0 || $existingBooking['activity_space'] == $activitySpace) {
                    $availableEndTimes[] = $currentSlotEnd->format('h:i A');
                    $currentSlotStart->addHour();
                    continue;
                }
            }

            // Stop when a slot is fully booked
            break;
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
        $overlappingBookings = Booking::whereHas('activity', function ($query) use ($amenityId) {
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




    public function checkUnitBooking(Request $request)
    {
        $unit = $request->input('unit');
        $activity_id = (int) $request->input('activity_id');
        $selectedDate = $request->input('dateField');

        // Validate that all required fields are provided
        if (!$unit || !$activity_id) {
            Log::warning('Invalid request: Missing unit or activity_id');
            return response()->json([
                'success' => false,
                'message' => 'Unit and Activity ID are required.',
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

        $query = Booking::where('unit', $unit)
            ->whereMonth('booking_date', $selectedMonth) // Use selected month
            ->whereYear('booking_date', $selectedYear) // Use selected year
            ->where('booking_status', 1)
            ->whereRaw("TRIM(LOWER(booking_type)) = 'advanced booking'");

        if (!empty($sharedActivityIds)) {
            $query->whereIn('activity_id', $sharedActivityIds);
        } else {
            $query->where('activity_id', $activity_id);
        }

        // Count unique transaction_no values (each transaction is counted once)
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


}
