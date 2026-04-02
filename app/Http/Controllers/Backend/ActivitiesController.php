<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Amenity;
use App\Models\ActivityDateBlocking;
use App\Models\ActivitySchedule;
use App\Models\ActivityBlocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ActivityBooking;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ActivitiesController extends Controller
{
    public function activities(Request $request)
    {
        $activities = Activity::with('amenity')->paginate(10);
        foreach ($activities as $activity) {
            $activity->start_time = Carbon::parse($activity->start_time)->format('h:i A');
            $activity->end_time = Carbon::parse($activity->end_time)->format('h:i A');
        }
        $amenities = Amenity::all();
        return view('backend.activities.admin-activities', compact('activities', 'amenities'));
    }


    public function addActivities(Request $request)
    {
        Log::info('Form submission received', $request->all());

        // Validate request
        $request->validate([
            'amenity_id' => 'required',
            'activity_name' => 'required',
            'activity_description' => 'required',
            'activity_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'space' => 'required',
            'timeOption' => 'required',
        ]);

        // Handle image upload
        $filename = null;
        if ($request->hasFile('activity_image')) {
            try {
                $file = $request->file('activity_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path(path: 'assets/images/activities'), $filename);
                Log::info('File uploaded successfully', ['filename' => $filename]);
            } catch (\Exception $e) {
                Log::error('File upload error', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'File upload failed.');
            }
        }

        try {
            // Save new activity
            $newActivity = new Activity();
            $newActivity->amenity_id = $request->input('amenity_id');
            $newActivity->activity_name = strtoupper($request->input('activity_name'));
            $newActivity->activity_description = strtoupper($request->input('activity_description'));
            $newActivity->activity_remarks = strtoupper($request->input('activity_remarks'));
            $newActivity->activity_max_booking = $request->input('activity_max_booking');
            $newActivity->activity_space = $request->input('space');
            $newActivity->activity_image = $filename;
            $newActivity->save();
            Log::info('New activity saved successfully', ['activity_id' => $newActivity->id]);

            // Define day mapping
            $dayMapping = [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ];

            // Check timeOption and save schedules
            if ($request->input('timeOption') === 'manual') {
                if (isset($request->times) && is_array($request->times)) {
                    Log::info('Manual scheduling mode detected', ['times' => $request->times]);

                    foreach ($dayMapping as $dayKey => $dayFullName) {
                        if (isset($request->times[$dayKey])) {
                            Log::info('Inserting schedule', [
                                'activity_id' => $newActivity->id,
                                'day' => $dayFullName,
                                'start_time' => $request->times[$dayKey]['start'] ?? 'N/A',
                                'end_time' => $request->times[$dayKey]['end'] ?? 'N/A',
                            ]);

                            DB::table('activity_schedules')->insert([
                                'activity_id' => $newActivity->id,
                                'day' => $dayFullName,
                                'start_time' => $request->times[$dayKey]['start'],
                                'end_time' => $request->times[$dayKey]['end'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                } else {
                    Log::warning('Manual schedule data is missing or invalid', ['times' => $request->times ?? 'null']);
                }
            } else {
                Log::info('Automatic scheduling mode detected', [
                    'start_time' => $request->input('start_time'),
                    'end_time' => $request->input('end_time'),
                ]);

                foreach ($dayMapping as $dayAbbreviation => $dayFullName) {
                    Log::info('Inserting schedule for automatic mode', [
                        'activity_id' => $newActivity->id,
                        'day' => $dayFullName,
                        'start_time' => $request->input('start_time'),
                        'end_time' => $request->input('end_time'),
                    ]);

                    DB::table('activity_schedules')->insert([
                        'activity_id' => $newActivity->id,
                        'day' => $dayFullName,
                        'start_time' => $request->input('start_time'),
                        'end_time' => $request->input('end_time'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Log::info('Activity and schedule successfully saved', ['activity_id' => $newActivity->id]);

            return redirect()->back()->with('success', 'Activity added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving activity', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to save activity.');
        }
    }


    public function getUpdatedActivitiesTable()
    {
        $activities = Activity::with('amenity')->paginate(perPage: 10);
        $activitiesData = $activities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'amenity_name' => strtoupper($activity->amenity->amenity_name ?? 'N/A'),
                'activity_name' => strtoupper($activity->activity_name ?? 'N/A'),
                'activity_description' => strtoupper($activity->activity_description ?? 'N/A'),
                'activity_remarks' => strtoupper($activity->activity_remarks ?? 'N/A'),
                'activity_space' => strtoupper($activity->activity_space ?? 'N/A'),
                'activity_max_booking' => strtoupper($activity->activity_max_booking ?? 'N/A'),
                'start_time' => strtoupper($activity->start_time ?? 'N/A'),
                'end_time' => strtoupper($activity->end_time ?? 'N/A'),
                'activity_image' => $activity->activity_image,
                'activity_status' => $activity->activity_status,
            ];
        });

        return response()->json([
            'data' => $activitiesData,
            'links' => (string) $activities->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchInfoActivity($id)
    {
        // dd($id);
        $act = Activity::with('schedules')->find($id);

        if (!$act) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $schedules = [];
        foreach ($act->schedules as $schedule) {
            $schedules[$schedule->day] = [
                'start' => Carbon::parse($schedule->start_time)->format('H:i'),
                'end' => Carbon::parse($schedule->end_time)->format('H:i'),
            ];
        }
        return response()->json([
            'id' => $act->id,
            'amenity_id' => $act->amenity_id,
            'activity_name' => $act->activity_name,
            'activity_description' => $act->activity_description,
            'activity_max_booking' => $act->activity_max_booking,
            'activity_space' => $act->activity_space,
            'activity_image' => $act->activity_image,
            'schedules' => $schedules,
        ]);
    }

    public function updateActivities(Request $request)
    {
        try {
            Log::info('Updating activity:', $request->all());

            $activity = Activity::find($request->input('act_id'));
            if (!$activity) {
                return response()->json(['status' => false, 'message' => "Activity not found"]);
            }
            $filename = $activity->activity_image;
            if ($request->hasFile('activity_image')) {
                try {
                    $file = $request->file('activity_image');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('assets/images/activities'), $filename);
                } catch (\Exception $e) {
                    Log::error('File upload error:', ['error' => $e->getMessage()]);
                    return response()->json(['status' => false, 'message' => 'File upload failed.']);
                }
            } elseif ($request->has('activity_image_file_name')) {
                $filename = $request->input('activity_image_file_name');
            }
            if (!$filename) {
                return response()->json(['status' => false, 'message' => "No image provided"]);
            }
            $activity->amenity_id = $request->input('hidden_amenity_id_activity');
            $activity->activity_name = strtoupper($request->input('activity_name'));
            $activity->activity_description = strtoupper($request->input('activity_description'));
            $activity->activity_max_booking = $request->input('edit_activity_max_booking');
            $activity->activity_space = $request->input('edit_activity_space');
            $activity->activity_image = $filename;
            $activity->save();

            Log::info('Activity updated successfully.');
            $times = $request->input('times', []);

            foreach ($times as $dayFullName => $time) {
                ActivitySchedule::updateOrCreate(
                    ['activity_id' => $activity->id, 'day' => $dayFullName],
                    [
                        'start_time' => Carbon::parse($time['start'])->format('H:i:s'),
                        'end_time' => Carbon::parse($time['end'])->format('H:i:s'),
                        'updated_at' => now(),
                    ]
                );
            }

            Log::info('Activity schedule updated successfully.');
            return response()->json(['status' => true, 'message' => "Updated Successfully"]);
        } catch (\Exception $e) {
            Log::error('Error updating activity:', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => "Update Failed"]);
        }
    }


    public function fetchActivityAddRemarks($id)
    {
        $info = Activity::find($id);
        if (!$info) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($info);
    }


    public function deactivateActivities(Request $request)
    {
        $activityId = $request->input('activity_id');
        $activityRemarks = $request->input('activity_remarks');
        $statusId = $request->input('activity_status');

        try {
            $activityRemarks = strtoupper($activityRemarks);
            $activity = Activity::findOrFail($activityId);
            $activity->activity_remarks = $activityRemarks;
            $activity->activity_status = $statusId;
            $activity->save();

            return response()->json(['status' => true, 'message' => 'Activity Updated Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Activity Update Failed.']);
        }
    }

    public function activateActivities(Request $request)
    {
        $activityId = $request->input('activity_id');
        $statusId = $request->input('activity_status');
        try {
            $activity = Activity::findOrFail($activityId);
            $activity->activity_status = $statusId;
            if ($statusId == 1) {
                $activity->activity_remarks = null;
            }
            $activity->update();

            return response()->json(['status' => true, 'message' => 'Status Updated Successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Status UpdatedFailed.']);
        }
    }
    public function deleteActivities(Request $request)
    {
        $activityId = $request->input('activity_id');
        try {
            $activity = Activity::findOrFail($activityId);

            $activity->delete();
            return response()->json(['status' => true, 'message' => 'Activity deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Activity deletion failed.']);
        }
    }

    public function searchActivity(Request $request)
    {
        $searchActivity = $request->input('searchActivity');
        $activities = Activity::when($searchActivity, function ($query, $searchActivity) {
            return $query->where('activity_name', 'LIKE', "{$searchActivity}%");
        })->paginate(10);

        $activities->appends(['searchActivity' => $searchActivity]);
        $amenities = Activity::all();
        return view('backend.activities', compact('amenities', 'activities', 'searchActivity'));
    }


    public function fetchDateBlockingActivities(Request $request)
    {
        $dateBlockings = ActivityDateBlocking::with(['amenity', 'activity'])->paginate(10);

        $activities = Activity::all();
        $amenities = Amenity::all();

        return view('backend.activities.admin-activity-date-blocking', compact('dateBlockings', 'activities', 'amenities'));

    }
    public function fetchActivityBlocking(Request $request)
    {
        $activities = Activity::where('amenity_id', $request->amenity_id)
            ->select('id', 'activity_name')
            ->get();
        return response()->json($activities);
    }

    public function searchBlockDates(Request $request)
    {
        $ActivityDateBlocking = new ActivityDateBlocking();
        $ActivityDateBlocking->date_blocking_start = Carbon::parse($request->input('date_blocking_start'))->format('Y-m-d');
        $ActivityDateBlocking->date_blocking_end = Carbon::parse($request->input('date_blocking_end'))->format('Y-m-d');
        $ActivityDateBlocking->save();

        return response()->json(['message' => 'Date blocked successfully.'], 200);
    }

    public function newDateBlockingActivities(Request $request)
    {
        try {
            $newBlocking = new ActivityDateBlocking();
            $newBlocking->amenity_id = $request->input('amenity_id_blocking');
            $newBlocking->blocking_remarks = strtoupper($request->input('blocking_remarks'));
            // $newBlocking->activity_id = $request->input('activity_id_blocking');
            $newBlocking->date_blocking_start = $request->input('date_blocking_start');
            $newBlocking->date_blocking_end = $request->input('date_blocking_end');
            $newBlocking->save();

            // Return a JSON response instead of redirecting
            return response()->json(['status' => 'success', 'message' => 'Blocked Successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Blocking Failed'], 500);
        }
    }

    public function getUpdatedBlocking()
    {
        $dateBlockings = Amenity::paginate(5);
        return response()->json([
            'data' => $dateBlockings->items(),
            'links' => (string) $dateBlockings->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function deleteBlockedDate(Request $request)
    {
        try {
            $block = ActivityDateBlocking::find($request->block_id);
            if (!$block) {
                return response()->json(['status' => false, 'message' => 'Blocked date not found'], 404);
            }

            $block->delete();
            return response()->json(['status' => true, 'message' => 'Blocked date deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Error deleting blocked date:', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Failed to delete blocked date']);
        }
    }

    public function getUpdatedBlockingTable()
    {
        $dateBlockings = ActivityDateBlocking::with(['amenity', 'activity'])->latest()
            ->paginate(10);
        return response()->json([
            'data' => $dateBlockings->items(),
            'links' => (string) $dateBlockings->links('vendor.pagination.bootstrap-5') // Pagination links
        ]);

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


    public function AdminBookingActivities(Request $request)
    {
        $currentDate = Carbon::today();
        $bookings = ActivityBooking::with(['activity', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
            ->whereDate('booking_date', '>=', $currentDate)
            ->latest()
            ->paginate(10);
        foreach ($bookings as $booking) {
            $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
            $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format('h:i A');
        }

        $activities = Activity::with('amenity')
            ->where('activity_status', 1)
            ->get();
        $amenities = Amenity::all();

        return view('backend.activities.admin-activity-booking', compact('bookings', 'activities', 'amenities'));
    }


    public function searchBooking(Request $request)
    {
        $searchBooking = $request->input('searchBooking');
        $currentDate = Carbon::today();

        $bookings = ActivityBooking::with('activity')
            ->where('booking_date', '>=', $currentDate)
            ->when($searchBooking, function ($query, $searchBooking) {
                return $query->where(function ($q) use ($searchBooking) {
                    $q->where('unit', 'LIKE', "{$searchBooking}%")
                        ->orWhere('name', 'LIKE', "%{$searchBooking}%");
                });
            })
            ->orderBy('booking_date', 'desc')
            ->paginate(10);
        foreach ($bookings as $booking) {
            $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
            $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format('h:i A');
        }

        $bookings->appends(['searchBooking' => $searchBooking]);
        $amenities = Amenity::all();
        $activities = Activity::all();

        return view('backend.activities.admin-activity-booking', compact('bookings', 'amenities', 'activities', 'searchBooking'));
    }


    public function AdminNewBookingActivities(Request $request)
    {
        try {
            DB::beginTransaction();

            $selectedCount = (int) $request->input('selected_slots', 1);
            $activityIds = is_array($request->activity_id) ? $request->activity_id : explode(',', $request->activity_id);

            $bookingStartTime = Carbon::createFromFormat('h:i A', $request->booking_start_time)->format('H:i:s');
            $bookingEndTime = Carbon::createFromFormat('h:i A', $request->booking_end_time)->format('H:i:s');

            foreach ($activityIds as $activityId) {
                $activity = Activity::find($activityId);

                if (!$activity) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Invalid activity selected.'], 400);
                }

                $bookingType = strtoupper($request->booking_type);

                if (in_array($bookingType, ['24HRS', 'WALK-IN'])) {

                    $existingBooking = ActivityBooking::where('booking_date', $request->booking_date)
                        ->where('unit', strtoupper($request->unit))
                        ->where('activity_id', $activityId)
                        ->whereIn('booking_type', ['24HRS', 'WALK-IN'])
                        ->where('booking_status', 1)
                        ->lockForUpdate()
                        ->exists();
                    if ($existingBooking) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "This unit already has a {$bookingType} booking for this activity on the selected date."
                        ], 409);
                    }
                }


                $activitySpace = $activity->activity_space;
                $amenityId = $activity->amenity_id;
                $existingBookings = ActivityBooking::where('booking_date', $request->booking_date)
                    ->where('booking_status', 1)
                    ->whereHas('activity', function ($query) use ($amenityId) {
                        $query->where('amenity_id', $amenityId);
                    })
                    ->where('booking_start_time', '<', $bookingEndTime)
                    ->where('booking_end_time', '>', $bookingStartTime)
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

                    $hourlyCount = $existingBookings->filter(function ($b) use ($hourStart, $hourEnd) {
                        return $b->booking_start_time < $hourEnd->format('H:i:s') &&
                            $b->booking_end_time > $hourStart->format('H:i:s');
                    })->count();

                    if (($hourlyCount + $selectedCount) > $activitySpace) {
                        $isConflict = true;
                        break;
                    }
                }

                if ($isConflict) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'This timeslot is already booked or has reached the maximum limit for this activity space.'
                    ], 409);
                }
            }


            $lastTransaction = ActivityBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction
                ? ((int) str_replace('2SAM-', '', $lastTransaction->transaction_no))
                : 0;
            $newTransactionNo = '2SAM-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            $user = auth()->user();
            foreach ($activityIds as $activityId) {
                for ($i = 0; $i < $selectedCount; $i++) {
                    $newBooking = new ActivityBooking();
                    $newBooking->activity_id = $activityId;
                    $newBooking->lobby = auth()->user()->name; // Admin name
                    $newBooking->unit = strtoupper($request->unit);
                    $newBooking->resident_type = strtoupper($request->selectResidentType);
                    $newBooking->name = strtoupper($request->name);
                    $newBooking->user_id = $user->id;   // add this
                    $newBooking->created_by = $user->id;
                    $newBooking->contact_number = $request->contact_number;
                    $newBooking->booking_type = strtoupper($request->booking_type);
                    $newBooking->booking_date = $request->booking_date;
                    $newBooking->booking_start_time = $bookingStartTime;
                    $newBooking->booking_end_time = $bookingEndTime;
                    $newBooking->booking_status = 1;
                    $newBooking->transaction_no = $newTransactionNo;
                    $newBooking->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Booking submitted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin booking failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function fetchAllSlotsAdmin(Request $request)
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

    // public function fetchAvailableTimes(Request $request)
    // {
    //     $activityId = $request->input('activity_id');
    //     $date = $request->input('booking_date');
    //     \Log::info("Fetching available times for Activity ID: $activityId, Date: $date");

    //     $activity = Activity::select('id', 'amenity_id', 'activity_space')->find($activityId);

    //     if (!$activity) {
    //         return response()->json(['error' => 'Activity not found'], 404);
    //     }

    //     $dayOfWeek = Carbon::parse($date)->format('l');
    //     $schedule = ActivitySchedule::where('activity_id', $activityId)
    //         ->where('day', $dayOfWeek)
    //         ->first();

    //     $blockedSlots = ActivityBlocking::where('activity_id', $activityId)
    //         ->where(function ($query) use ($dayOfWeek, $date) {

    //             $query->where(function ($q) use ($dayOfWeek) {
    //                 $q->where('repeat_weekly', true)
    //                     ->where('day', $dayOfWeek);
    //             });


    //         })
    //         ->get();

    //     if (
    //         !$schedule ||
    //         is_null($schedule->start_time) ||
    //         is_null($schedule->end_time) ||
    //         $schedule->start_time === '00:00:00' ||
    //         $schedule->end_time === '00:00:00'
    //     ) {
    //         return response()->json(['error' => 'No Schedule']);
    //     }

    //     $start = Carbon::parse("{$date} {$schedule->start_time}");
    //     $end = Carbon::parse("{$date} {$schedule->end_time}");


    //     if ($end->lessThanOrEqualTo($start)) {
    //         $end->addDay();
    //     }

    //     $activitySpace = $activity->activity_space;
    //     $amenityId = $activity->amenity_id;


    //     $bookedSlots = ActivityBooking::with('activity:id,activity_space')
    //         ->whereHas('activity', function ($query) use ($amenityId) {
    //             $query->where('amenity_id', $amenityId);
    //         })
    //         ->where('booking_date', $date)
    //         ->where('booking_status', 1)
    //         ->get(['activity_id', 'booking_start_time', 'booking_end_time']);


    //     $occupiedSlots = [];
    //     foreach ($bookedSlots as $booking) {
    //         $bookingStart = Carbon::parse("{$date} {$booking->booking_start_time}");
    //         $bookingEnd = Carbon::parse("{$date} {$booking->booking_end_time}");

    //         if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
    //             $bookingEnd->addDay();
    //         }

    //         $bookingActivitySpace = $booking->activity->activity_space;

    //         while ($bookingStart < $bookingEnd) {
    //             $slotKey = $bookingStart->format('H:i');

    //             if (!isset($occupiedSlots[$slotKey])) {
    //                 $occupiedSlots[$slotKey] = [
    //                     'count' => 0,
    //                     'activity_space' => null,
    //                     'blocked' => false
    //                 ];
    //             }

    //             $occupiedSlots[$slotKey]['count']++;
    //             $occupiedSlots[$slotKey]['activity_space'] = $bookingActivitySpace;

    //             $bookingStart->addHour();
    //         }
    //     }

    //     foreach ($blockedSlots as $block) {
    //         $blockStart = Carbon::parse("{$date} {$block->start_time}");
    //         $blockEnd = Carbon::parse("{$date} {$block->end_time}");

    //         if ($blockEnd->lessThanOrEqualTo($blockStart)) {
    //             $blockEnd->addDay();
    //         }

    //         while ($blockStart < $blockEnd) {
    //             $slotKey = $blockStart->format('H:i');

    //             $occupiedSlots[$slotKey] = [
    //                 'blocked' => true,
    //             ];

    //             $blockStart->addHour();
    //         }
    //     }
    //     $availableTimePairs = [];
    //     $currentSlotStart = clone $start;

    //     while ($currentSlotStart < $end) {
    //         $currentSlotEnd = (clone $currentSlotStart)->addHour();
    //         $slotKey = $currentSlotStart->format('H:i');

    //         $existingBooking = $occupiedSlots[$slotKey] ?? [
    //             'count' => 0,
    //             'activity_space' => null,
    //             'blocked' => false
    //         ];

    //         if (!empty($existingBooking['blocked'])) {
    //             $currentSlotStart->addHour();
    //             continue;
    //         }

    //         if (
    //             $existingBooking['count'] < $activitySpace &&
    //             ($existingBooking['count'] == 0 || $existingBooking['activity_space'] == $activitySpace)
    //         ) {
    //             $availableTimePairs[] = [
    //                 'start' => $currentSlotStart->format('h:i A'),
    //                 'end' => $currentSlotEnd->format('h:i A'),
    //             ];
    //         }

    //         $currentSlotStart->addHour();
    //     }

    //     return response()->json($availableTimePairs);
    // }


    public function fetchAvailableTimes(Request $request)
    {
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
            ->whereHas('activity', function ($q) use ($amenityId) {
                $q->where('amenity_id', $amenityId);
            })
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get(['activity_id', 'booking_start_time', 'booking_end_time']);

        $blockedSlots = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
            $q->where('amenity_id', $amenityId);
        })->where(function ($query) use ($dayOfWeek) {
            $query->where(function ($q) use ($dayOfWeek) {
                $q->where('repeat_weekly', true)
                    ->where('day', $dayOfWeek);
            });
        })->get();


        $occupiedSlots = [];


        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse("{$date} {$booking->booking_start_time}");
            $bookingEnd = Carbon::parse("{$date} {$booking->booking_end_time}");
            if ($bookingEnd->lessThanOrEqualTo($bookingStart))
                $bookingEnd->addDay();

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
                $occupiedSlots[$slotKey]['activity_space'] = $booking->activity->activity_space;
                $bookingStart->addHour();
            }
        }

        foreach ($blockedSlots as $block) {
            $blockStart = Carbon::parse("{$date} {$block->start_time}");
            $blockEnd = Carbon::parse("{$date} {$block->end_time}");
            if ($blockEnd->lessThanOrEqualTo($blockStart))
                $blockEnd->addDay();

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


        $availableTimePairs = [];
        $now = Carbon::now();

        $currentSlotStart = clone $start;
        while ($currentSlotStart < $end) {
            if ($date == $now->toDateString() && $currentSlotStart->lessThanOrEqualTo($now)) {
                $currentSlotStart->addHour();
                continue;
            }

            $currentSlotEnd = (clone $currentSlotStart)->addHour();
            $slotKey = $currentSlotStart->format('H:i');

            $existing = $occupiedSlots[$slotKey] ?? [
                'count' => 0,
                'activity_space' => null,
                'blocked' => false,
            ];

            if (!empty($existing['blocked'])) {
                $currentSlotStart->addHour();
                continue;
            }

            if (
                $existing['count'] < $activitySpace &&
                ($existing['count'] == 0 || $existing['activity_space'] == $activitySpace)
            ) {
                $availableTimePairs[] = [
                    'start' => $currentSlotStart->format('h:i A'),
                    'end' => $currentSlotEnd->format('h:i A'),
                ];
            }

            $currentSlotStart->addHour();
        }

        return response()->json($availableTimePairs);
    }

    public function fetchEndTimes(Request $request)
    {
        $activityId = $request->input('activity_id');
        $date = $request->input('booking_date');
        $startTime = $request->input('start_time');

        \Log::info("Fetching end times for Activity ID: $activityId, Date: $date, Start Time: $startTime");

        $activity = Activity::select('id', 'amenity_id', 'activity_space')->find($activityId);
        if (!$activity) {
            return response()->json(['error' => 'Activity not found'], 404);
        }

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

        $activitySpace = $activity->activity_space;
        $amenityId = $activity->amenity_id;

        $bookedSlots = ActivityBooking::with('activity:id,activity_space,amenity_id')
            ->whereHas('activity', function ($q) use ($amenityId) {
                $q->where('amenity_id', $amenityId);
            })
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get(['activity_id', 'booking_start_time', 'booking_end_time']);

        $blockedSlots = ActivityBlocking::whereHas('activity', function ($q) use ($amenityId) {
            $q->where('amenity_id', $amenityId);
        })->where(function ($query) use ($dayOfWeek) {
            $query->where(function ($q) use ($dayOfWeek) {
                $q->where('repeat_weekly', true)->where('day', $dayOfWeek);
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
            if ($blockEnd->lessThanOrEqualTo($blockStart))
                $blockEnd->addDay();

            while ($blockStart < $blockEnd) {
                $slotKey = $blockStart->format('H:i');
                if (!isset($occupiedSlots[$slotKey])) {
                    $occupiedSlots[$slotKey] = [
                        'count' => 0,
                        'activity_space' => null,
                        'blocked' => true,
                    ];
                } else {
                    $occupiedSlots[$slotKey]['blocked'] = true;
                }
                $blockStart->addHour();
            }
        }


        $availableEndTimes = [];
        $currentSlotStart = clone $start;
        $maxHours = 2;
        $addedHours = 0;

        while ($currentSlotStart < $end && $addedHours < $maxHours) {
            $currentSlotEnd = (clone $currentSlotStart)->addHour();
            $slotKey = $currentSlotStart->format('H:i');

            $existingBooking = $occupiedSlots[$slotKey] ?? [
                'count' => 0,
                'activity_space' => null,
                'blocked' => false,
            ];

            if (!empty($existingBooking['blocked'])) {
                break;
            }

            if ($existingBooking['count'] >= $activitySpace) {
                break;
            }

            if (
                $existingBooking['count'] > 0 &&
                $existingBooking['activity_space'] != $activitySpace
            ) {
                break;
            }

            $availableEndTimes[] = $currentSlotEnd->format('h:i A');
            $addedHours++;
            $currentSlotStart->addHour();
        }

        return response()->json([
            'availableEndTimes' => $availableEndTimes,
        ]);
    }

    public function fetchAvailableSlots(Request $request)
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
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get();

        $timeSlots = [];

        foreach ($overlappingBookings as $booking) {
            $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            for ($time = $bookingStart->copy(); $time < $bookingEnd; $time->addMinute()) {
                $key = $time->format('Y-m-d H:i'); // full datetime key to avoid overlap
                $timeSlots[$key] = ($timeSlots[$key] ?? 0) + 1;
            }
        }

        $maxConcurrent = 0;
        for ($time = $startTime->copy(); $time < $endTime; $time->addMinute()) {
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

        // Get max bookings dynamically from activity_max_booking column
        $maxBookings = $activity->activity_max_booking ?? 2; // Default to 2 if not set
        Log::info('Max Bookings Retrieved:', ['maxBookings' => $maxBookings]);

        // Extract month and year from the selected date
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

    // public function getUpdatedBookingTable()
    // {
    //     $currentDate = Carbon::today();
    //     $bookings = ActivityBooking::with('activity')
    //         ->whereDate('booking_date', '>=', $currentDate)
    //         ->latest()
    //         ->paginate(10);

    //     $bookings->getCollection()->transform(function ($booking) {
    //         return [
    //             'id' => $booking->id,
    //             'lobby' => $booking->lobby,
    //             'transaction_no' => $booking->transaction_no,
    //             'activity' => $booking->activity,
    //             'unit' => $booking->unit,
    //             'resident_type' => $booking->resident_type,
    //             'name' => $booking->name,
    //             'contact_number' => $booking->contact_number,
    //             'booking_type' => $booking->booking_type,
    //             'booking_status' => $booking->booking_status,
    //             'booking_date' => $booking->booking_date,
    //             'booking_start_time' => Carbon::parse($booking->booking_start_time)->format('h:i A'),
    //             'booking_end_time' => Carbon::parse($booking->booking_end_time)->format('h:i A'),
    //             'created_at' => Carbon::parse($booking->created_at)->format('Y-m-d H:i:s'),
    //             'updated_at' => Carbon::parse($booking->updated_at)->format('Y-m-d H:i:s'),
    //         ];
    //     });

    //     return response()->json([
    //         'data' => $bookings->items(),
    //         'links' => (string) $bookings->links('vendor.pagination.bootstrap-5')
    //     ]);
    // }

    public function getUpdatedBookingTable()
    {
        $currentDate = Carbon::today();

        $bookings = ActivityBooking::with(['activity', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
            ->whereDate('booking_date', '>=', $currentDate)
            ->latest()
            ->paginate(10);

        $bookings->getCollection()->transform(function ($booking) {
            return [
                'id' => $booking->id,
                'transaction_no' => $booking->transaction_no,
                'activity' => $booking->activity,
                'user' => $booking->user,
                'created_by' => $booking->user?->name ?? 'Admin',
                'cancelled_by' => $booking->cancelledBy?->name ?? null, // snake_case
                'cancelled_at' => $booking->cancelled_at ? Carbon::parse($booking->cancelled_at)->format('Y-m-d H:i:s') : null,
                'waived_by' => $booking->waivedBy?->name ?? null,       // snake_case
                'unit' => $booking->unit,
                'resident_type' => $booking->resident_type,
                'name' => $booking->name,
                'contact_number' => $booking->contact_number,
                'booking_type' => $booking->booking_type,
                'booking_status' => $booking->booking_status,
                'booking_date' => $booking->booking_date,
                'penalty_amount' => $booking->penalty_amount ?? 0,
                'penalty_waived' => $booking->penalty_waived,
                'booking_start_time' => Carbon::parse($booking->booking_start_time)->format('h:i A'),
                'booking_end_time' => Carbon::parse($booking->booking_end_time)->format('h:i A'),
                'penalty_applied_at' => Carbon::parse($booking->penalty_applied_at)->format('Y-m-d H:i:s'),
                'penalty_waived_at' => Carbon::parse($booking->penalty_waived_at)->format('Y-m-d H:i:s'),
                'created_at' => Carbon::parse($booking->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($booking->updated_at)->format('Y-m-d H:i:s'),
                'penalty_applied_by' => $booking->penaltyAppliedBy?->name ?? null,
            ];
        });

        return response()->json([
            'data' => $bookings->items(),
            'links' => (string) $bookings->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchInfoBooking($id)
    {
        $booking = ActivityBooking::find($id);
        if (!$booking) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
        $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format(format: 'h:i A');

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'transaction_no' => $booking->transaction_no,
                'activity_name' => $booking->activity->activity_name,
                'unit' => $booking->unit,
                'name' => $booking->name,
                'contact' => $booking->contact_number,
                'booking_type' => $booking->booking_type,
                'resident_type' => $booking->resident_type,
                'booking_date' => $booking->booking_date,
                'booking_start_time' => $booking->booking_start_time,
                'booking_end_time' => $booking->booking_end_time,
                'booking_status' => $booking->booking_status,

                // ✅ ADD THESE
                'penalty_amount' => $booking->penalty_amount,
                'penalty_waived' => $booking->penalty_waived,
                'has_penalty' => $booking->penalty_amount > 0,
                'cancelled_at' => $booking->cancelled_at,
            ]
        ]);
    }
    public function cancelBooking(ActivityBooking $booking, Request $request)
    {
        try {

            $booking->load('activity');

            if (!$booking->canCancel()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking cannot be cancelled.'
                ], 400);
            }

            $withPenalty = $booking->isWithin12Hours();
            $waivePenalty = $request->input('waive_penalty', false); // 👈 NEW

            if (!$request->has('confirm') && $withPenalty && !$waivePenalty) {
                return response()->json([
                    'success' => true,
                    'requires_confirmation' => true,
                    'message' => "Cancelling within 12 hours will incur a ₱1000 penalty."
                ]);
            }

            $transactionNo = $booking->transaction_no;
            $bookings = ActivityBooking::where('transaction_no', $transactionNo)->get();

            foreach ($bookings as $b) {

                if ($withPenalty && !$waivePenalty) {

                    $b->applyCancellationPenalty(); // sets penalty_amount
                    $b->booking_status = 3;
                    $b->penalty_waived = false;
                    $b->waived_by = null;

                } elseif ($withPenalty && $waivePenalty) {

                    $b->applyCancellationPenalty(); // 👈 STILL APPLY (IMPORTANT)

                    $b->booking_status = 2; // still cancelled
                    $b->penalty_waived = true;
                    $b->waived_by = auth()->id(); // 👈 TRACK WHO WAIVED

                } else {

                    $b->booking_status = 2;
                    $b->penalty_waived = false;
                    $b->waived_by = null;
                }

                $b->cancelled_at = now();
                $b->cancelled_by = auth()->id();
                $b->cancelled_within_12hrs = $withPenalty ? 1 : 0;

                $b->save();
            }

            return response()->json([
                'success' => true,
                'withPenalty' => $withPenalty && !$waivePenalty,
                'waived' => $waivePenalty,
                'message' => ($withPenalty && !$waivePenalty)
                    ? "Booking cancelled. Penalty applied."
                    : ($waivePenalty
                        ? "Booking cancelled. Penalty waived."
                        : "Booking cancelled successfully.")
            ]);

        } catch (\Exception $e) {

            \Log::error('Admin Cancel Booking Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }


    public function History(Request $request)
    {
        $currentDate = Carbon::now()->toDateString();
        $activity_bookings = ActivityBooking::with(['activity', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
            ->whereDate('booking_date', '<', $currentDate)
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        foreach ($activity_bookings as $booking) {
            $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('H:i a');
            $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format('H:i a');
        }

        $activities = Activity::all();
        $amenities = Amenity::all();

        return view('backend.activities.admin-activity-booking-records', compact('activity_bookings', 'activities', 'amenities'));
    }

    public function searchHistory(Request $request)
    {
        $searchHistory = $request->input('searchHistory');
        $currentDate = now();

        $activity_bookings = ActivityBooking::with(['activity', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
            ->where('booking_date', '<', $currentDate)
            ->when($searchHistory, function ($query, $searchHistory) {
                $query->where(function ($q) use ($searchHistory) {
                    $q->where('unit', 'LIKE', "{$searchHistory}%")
                        ->orWhere('name', 'LIKE', "%{$searchHistory}%");
                });
            })
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        foreach ($activity_bookings as $activity_booking) {
            $activity_booking->booking_start_time = Carbon::parse($activity_booking->booking_start_time)->format('h:i A');
            $activity_booking->booking_end_time = Carbon::parse($activity_booking->booking_end_time)->format('h:i A');
        }

        $activity_bookings->appends(['searchHistory' => $searchHistory]);
        $amenities = Amenity::all();
        $activities = Activity::all();

        return view('backend.activities.admin-activity-booking-records', compact('activity_bookings', 'amenities', 'activities', 'searchHistory'));
    }

    public function downloadHistory(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        Log::info("Download history request received", [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);

        $formattedFromDate = Carbon::parse($fromDate)->format('m-d-Y');
        $formattedToDate = Carbon::parse($toDate)->format('m-d-Y');

        $data = DB::table('activity_bookings')
            ->join('activities', 'activity_bookings.activity_id', '=', 'activities.id')
            ->select(
                'lobby',
                'transaction_no',
                'activities.activity_name as activity',
                'unit',
                'resident_type',
                'name',
                'contact_number',
                'booking_type',
                'booking_status',
                'booking_date',
                'booking_start_time',
                'booking_end_time',
                'activity_bookings.created_at as booking_created_at',  // Specify table name
                'activity_bookings.updated_at as booking_updated_at'   // Specify table name
            )
            ->whereBetween('booking_date', [$fromDate, $toDate])
            ->get();

        // Log::info("Data fetched for download", [
        //     'total_records' => count($data),
        //     'sample_data' => $data->take(5)->toArray()
        // ]);

        $fileName = "2S_Booking_Reports_{$formattedFromDate}_to_{$formattedToDate}.csv";

        $response = new StreamedResponse(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Lobby', 'Transaction No.', 'Activity', 'Unit', 'Resident Type', 'Name', 'Contact', 'Booking Type', 'Status', 'Booking Date', 'Start Time', 'End Time', 'Created At', 'Updated At']);

            foreach ($data as $row) {
                $booking_date = Carbon::parse($row->booking_date)->format('F j, Y');
                $start_time = Carbon::parse($row->booking_start_time)->format('h:i A');
                $end_time = Carbon::parse($row->booking_end_time)->format('h:i A');
                $status = $row->booking_status == 1 ? 'Completed' : 'Cancelled';

                fputcsv($handle, [
                    $row->lobby,
                    $row->transaction_no,
                    $row->activity,
                    $row->unit,
                    $row->resident_type,
                    $row->name,
                    "\t" . $row->contact_number,
                    $row->booking_type,
                    $status,
                    $booking_date,
                    $start_time,
                    $end_time,
                    $row->booking_created_at,  // Keep original format from database
                    $row->booking_updated_at
                ]);

                ob_flush();
                flush();
            }

            fclose($handle);
        });

        Log::info("CSV file generation completed", [
            'filename' => $fileName
        ]);

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    public function markNoShow(ActivityBooking $booking)
    {
        try {

            if ($booking->penalty_amount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penalty already applied.'
                ]);
            }

            $transactionNo = $booking->transaction_no;

            $bookings = ActivityBooking::where('transaction_no', $transactionNo)->get();

            foreach ($bookings as $b) {

                $b->penalty_amount = 1000;
                $b->booking_status = 4; // mark as NO SHOW
                $b->cancelled_at = now(); // optional for tracking
                $b->cancelled_by = auth()->id();
                $b->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking marked as no-show. ₱1000 penalty applied.'
            ]);

        } catch (\Exception $e) {

            \Log::error('No Show Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark no show.'
            ], 500);
        }
    }

    public function fetchScheduleBlocking(Request $request)
    {
        $activity_blockings = ActivityBlocking::with('activity')->paginate(10);

        $activities = Activity::all();

        return view('backend.activities.admin-activities-schedule-blocking', compact('activity_blockings', 'activities'));

    }

    public function newScheduleBlocking(Request $request)
    {
        $request->validate([
            'activity_id' => 'required',
            'days' => 'required|array',
            'blocking_start_time' => 'required',
            'blocking_end_time' => 'required'
        ]);

        $created = 0;

        foreach ($request->days as $day) {

            $exists = ActivityBlocking::where('activity_id', $request->activity_id)
                ->where('day', $day)
                ->where('start_time', $request->blocking_start_time)
                ->where('end_time', $request->blocking_end_time)
                ->exists();

            if (!$exists) {

                ActivityBlocking::create([
                    'activity_id' => $request->activity_id,
                    'day' => $day,
                    'start_time' => $request->blocking_start_time,
                    'end_time' => $request->blocking_end_time,
                    'remarks' => strtoupper($request->remarks),
                    'repeat_weekly' => $request->repeat_weekly
                ]);

                $created++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$created blocking schedule(s) created."
        ]);
    }
    public function getUpdatedActivityScheduleBlockingTable()
    {
        $dateBlockings = ActivityBlocking::with('activity')->latest()
            ->paginate(10);
        return response()->json([
            'data' => $dateBlockings->items(),
            'links' => (string) $dateBlockings->links('vendor.pagination.bootstrap-5') // Pagination links
        ]);

    }


    public function importAmenityBookings(Request $request)
    {
        Log::info('Import booking route hit');

        if (!$request->hasFile('file')) {
            Log::error('No file uploaded');
            return back()->with('error', 'No file uploaded');
        }

        $file = $request->file('file');
        Log::info('File received', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        DB::beginTransaction();

        try {
            // Load CSV properly
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            if (count($csvData) <= 1) {
                Log::warning('CSV file is empty or only has headers');
                return back()->with('error', 'CSV file is empty');
            }

            $header = array_shift($csvData);

            $lastTransaction = ActivityBooking::lockForUpdate()->latest('id')->first();
            $lastNumber = $lastTransaction
                ? ((int) str_replace('2SAM-', '', $lastTransaction->transaction_no))
                : 0;

            foreach ($csvData as $index => $row) {

                if (!isset($row[0]) || empty(trim($row[0])))
                    continue;

                Log::info('Processing CSV row', ['index' => $index, 'row' => $row]);


                $lastNumber++;
                $transactionNo = '2SAM-' . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
                $name = isset($row[5]) ? iconv('ISO-8859-1', 'UTF-8', $row[5]) : '';

                // Parse booking date safely
                try {
                    $bookingDate = Carbon::parse(trim($row[9]))->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::error('Booking date parse error', [
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                    continue; // skip row if date is invalid
                }
                try {
                    $bookingStartTime = Carbon::createFromFormat('g:i A', trim($row[10]))->format('H:i:s');
                    $bookingEndTime = Carbon::createFromFormat('g:i A', trim($row[11]))->format('H:i:s');
                } catch (\Exception $e) {
                    Log::error('Booking time parse error', ['row' => $row, 'error' => $e->getMessage()]);
                    continue;
                }

                try {
                    ActivityBooking::create([
                        'transaction_no' => $transactionNo,
                        'lobby' => trim($row[0]),
                        'activity_id' => trim($row[2]),
                        'unit' => trim($row[3]),
                        'resident_type' => trim($row[4]),
                        'name' => $name,
                        'contact_number' => trim($row[6]),
                        'booking_type' => trim($row[7]),
                        'booking_status' => trim($row[8]),
                        'booking_date' => $bookingDate,
                        'booking_start_time' => $bookingStartTime,
                        'booking_end_time' => $bookingEndTime,
                        'cancelled_by' => null,
                        'cancelled_at' => null,
                        'penalty' => null,
                        'created_by' => null,
                        'created_at' => isset($row[12]) ? trim($row[12]) : now(),
                        'updated_at' => isset($row[13]) ? trim($row[13]) : now(),
                    ]);

                    Log::info('Booking created', ['transaction_no' => $transactionNo]);

                } catch (\Exception $e) {
                    Log::error('Row insert failed', [
                        'index' => $index,
                        'row' => $row,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();
            Log::info('CSV import completed successfully');
            return back()->with('success', 'Bookings imported successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV import failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }



    public function AdminActivityBookingCalendar()
    {
        $schedules = ActivityBooking::with('activity')
            ->where('booking_status', 1)
            ->get()
            ->map(function ($schedule) {
                $bookingDate = Carbon::parse($schedule->booking_date);
                $startTime = Carbon::parse($schedule->booking_start_time)->format('g a');
                $endTime = Carbon::parse($schedule->booking_end_time)->format('g a');
                $startDateTime = $bookingDate->format('Y-m-d') . 'T' . $schedule->booking_start_time;
                $endDateTime = $bookingDate->format('Y-m-d') . 'T' . $schedule->booking_end_time;

                $activityName = $schedule->activity ? $schedule->activity->activity_name : 'Unknown Activity';

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->unit . ' (' . $startTime . ' - ' . $endTime . ') ' . $activityName,
                    'start' => $startDateTime,
                    'end' => $endDateTime,
                    'activity_id' => $schedule->activity_id
                ];
            });

        return view('backend.activities.admin-activity-booking-calendar', ['events' => $schedules]);

    }

    public function fetchActivityCalendarInfo($id)
    {
        $schedule = ActivityBooking::with('activity')->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'id' => $schedule->id,
            'unit' => $schedule->unit,
            'name' => $schedule->name,
            'contact_number' => $schedule->contact_number,
            'booking_date' => date('F d, Y', strtotime($schedule->booking_date)),
            'booking_start_time' => date('g:i A', strtotime($schedule->booking_start_time)),
            'booking_end_time' => date('g:i A', strtotime($schedule->booking_end_time)),
            'activity_name' => strtoupper($schedule->activity->activity_name),

        ]);
    }


    public function managePenalty(ActivityBooking $booking, Request $request)
    {
        try {

            $booking->load('activity');

            $action = $request->input('action');

            $transactionNo = $booking->transaction_no;
            $bookings = ActivityBooking::where('transaction_no', $transactionNo)->get();

            foreach ($bookings as $b) {

                if ($action === 'apply') {

                    $b->applyManualPenalty();
                    $b->penalty_waived = false;

                } elseif ($action === 'waive') {

                    $b->waivePenalty();

                }

                $b->save();
            }

            return response()->json([
                'success' => true,
                'message' => $action === 'apply'
                    ? 'Penalty applied successfully.'
                    : 'Penalty waived successfully.'
            ]);

        } catch (\Exception $e) {

            \Log::error('Manage Penalty Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update penalty.'
            ], 500);
        }
    }


    public function fetchInfoBookingReport($id)
    {
        $booking = ActivityBooking::find($id);
        if (!$booking) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
        $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format(format: 'h:i A');

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'transaction_no' => $booking->transaction_no,
                'activity_name' => $booking->activity->activity_name,
                'unit' => $booking->unit,
                'name' => $booking->name,
                'contact' => $booking->contact_number,
                'booking_type' => $booking->booking_type,
                'resident_type' => $booking->resident_type,
                'booking_date' => $booking->booking_date,
                'booking_start_time' => $booking->booking_start_time,
                'booking_end_time' => $booking->booking_end_time,
                'booking_status' => $booking->booking_status,

                // ✅ ADD THESE
                'penalty_amount' => $booking->penalty_amount,
                'penalty_waived' => $booking->penalty_waived,
                'has_penalty' => $booking->penalty_amount > 0,
                'cancelled_at' => $booking->cancelled_at,
            ]
        ]);
    }
}

