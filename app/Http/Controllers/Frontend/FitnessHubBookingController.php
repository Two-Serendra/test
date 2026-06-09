<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FitnessHub;
use App\Models\FitnessHubBooking;
use App\Models\ResidentDetails;
use App\Models\FitnessHubScheduleBlocking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Notifications\UserFitnessHubBookingBellNotification;
use App\Mail\UserFitnessHubBookingConfirmation;
use App\Mail\ConciergeFitnessHubBookingConfirmation;
use App\Events\FitnessHubBookingCreated;

use App\Mail\UserFitnessHubBookingCancellation;
use App\Mail\ConciergeFitnessHubBookingCancellation;

class FitnessHubBookingController extends Controller
{
    public function checkUnitBookingFitnessHub(Request $request)
    {
        $unit = $request->input('unit');
        $fitnessHubId = $request->input('fitness_hub_id');
        $selectedDate = $request->input('dateField');

        if (!$unit || !$fitnessHubId || !$selectedDate) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields.',
            ]);
        }
        $startOfWeek = Carbon::parse($selectedDate)
            ->startOfWeek(Carbon::MONDAY)
            ->startOfDay();

        $endOfWeek = Carbon::parse($selectedDate)
            ->endOfWeek(Carbon::SUNDAY)
            ->endOfDay();


        \Log::info('Weekly Range', [
            'start' => $startOfWeek,
            'end' => $endOfWeek
        ]);

        $bookings = FitnessHubBooking::where('unit', $unit)
            ->where('fitness_hub_id', $fitnessHubId)
            ->whereDate('booking_date', '>=', $startOfWeek)
            ->whereDate('booking_date', '<=', $endOfWeek)
            ->where('booking_status', 1)
            ->get(['booking_start_time', 'booking_end_time']);

        $totalHours = 0;

        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
            $end = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);
            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            $hours = $start->diffInMinutes($end) / 60;
            $totalHours += $hours;
        }

        $maxHours = 2;

        return response()->json([
            'success' => true,
            'count' => $totalHours,
            'maxBookings' => $maxHours,
        ]);
    }


    public function fetchAvailableTimesFitnessHub(Request $request)
    {
        $fitnessHubId = $request->input('fitness_hub_id');
        $date = $request->input('booking_date');
        $bookingType = $request->input('booking_type');

        \Log::info("Fetching available times for Fitness Hub ID: $fitnessHubId, Date: $date");

        $fitnessHub = FitnessHub::find($fitnessHubId);

        if (!$fitnessHub) {
            return response()->json(['error' => 'Fitness Hub not found'], 404);
        }

        if (
            !$fitnessHub->fitness_hub_start_time ||
            !$fitnessHub->fitness_hub_end_time ||
            $fitnessHub->fitness_hub_start_time === '00:00:00' ||
            $fitnessHub->fitness_hub_end_time === '00:00:00'
        ) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse("$date {$fitnessHub->fitness_hub_start_time}");
        $end = Carbon::parse("$date {$fitnessHub->fitness_hub_end_time}");

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }

        $bookedSlots = FitnessHubBooking::where('fitness_hub_id', $fitnessHubId)
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get(['booking_start_time', 'booking_end_time']);

        $occupiedSlots = [];

        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse("$date {$booking->booking_start_time}");
            $bookingEnd = Carbon::parse("$date {$booking->booking_end_time}");

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            while ($bookingStart < $bookingEnd) {
                $occupiedSlots[$bookingStart->format('H:i')] = true;
                $bookingStart->addHour();
            }
        }

        $availableTimePairs = [];
        $now = Carbon::now();

        $currentSlot = clone $start;

        while ($currentSlot < $end) {

            $hoursFromNow = $now->diffInHours($currentSlot, false);
            if ($hoursFromNow < 1) {
                $currentSlot->addHour();
                continue;
            }
            if ($bookingType === 'Advanced Booking' && ($hoursFromNow < 25 || $hoursFromNow > (30 * 24))) {
                $currentSlot->addHour();
                continue;
            }

            $slotKey = $currentSlot->format('H:i');

            if (!isset($occupiedSlots[$slotKey])) {
                $availableTimePairs[] = [
                    'start' => $currentSlot->format('h:i A'),
                    'end' => (clone $currentSlot)->addHour()->format('h:i A'),
                ];
            }

            $currentSlot->addHour();
        }

        return response()->json($availableTimePairs);
    }


    public function fetchAvailableEndTimesFitnessHub(Request $request)
    {
        $fitnessHubId = $request->input('fitness_hub_id');
        $date = $request->input('booking_date');
        $startTime = $request->input('start_time');

        \Log::info("Fetching end times for Fitness Hub ID: $fitnessHubId, Date: $date, Start: $startTime");

        $fitnessHub = FitnessHub::find($fitnessHubId);

        if (!$fitnessHub) {
            return response()->json(['error' => 'Fitness Hub not found'], 404);
        }

        $start = Carbon::parse("$date $startTime");
        $end = Carbon::parse("$date {$fitnessHub->fitness_hub_end_time}");

        if ($end->lessThanOrEqualTo(Carbon::parse("$date {$fitnessHub->fitness_hub_start_time}"))) {
            $end->addDay();
        }

        $maxBooking = $fitnessHub->fitness_hub_max_booking;

        $bookedSlots = FitnessHubBooking::where('fitness_hub_id', $fitnessHubId)
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get(['booking_start_time', 'booking_end_time']);

        $occupiedSlots = [];

        foreach ($bookedSlots as $booking) {
            $bookingStart = Carbon::parse("$date {$booking->booking_start_time}");
            $bookingEnd = Carbon::parse("$date {$booking->booking_end_time}");

            if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                $bookingEnd->addDay();
            }

            while ($bookingStart < $bookingEnd) {
                $slotKey = $bookingStart->format('H:i');
                $occupiedSlots[$slotKey] = ($occupiedSlots[$slotKey] ?? 0) + 1;
                $bookingStart->addHour();
            }
        }

        $availableEndTimes = [];
        $current = clone $start;

        // optional: limit booking duration (ex: 2 hrs max)
        $maxHours = 2;
        $addedHours = 0;

        while ($current < $end && $addedHours < $maxHours) {

            $slotKey = $current->format('H:i');

            if (($occupiedSlots[$slotKey] ?? 0) >= $maxBooking) {
                break;
            }

            $next = (clone $current)->addHour();
            $availableEndTimes[] = $next->format('h:i A');

            $current->addHour();
            $addedHours++;
        }

        return response()->json([
            'availableEndTimes' => $availableEndTimes
        ]);
    }



    public function UserNewBookingFitnessHub(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {

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


                $fitnessHubId = $request->fitness_hub_id;
                $date = $request->booking_date;
                $unit = strtoupper($resident->unit_no);

                $start = Carbon::createFromFormat('Y-m-d h:i A', "$date {$request->booking_start_time}");
                $end = Carbon::createFromFormat('Y-m-d h:i A', "$date {$request->booking_end_time}");

                if ($end->lessThanOrEqualTo($start)) {
                    $end->addDay();
                }

                if ($end->diffInMinutes($start) <= 0) {
                    return response()->json([
                        'message' => 'Invalid time range.'
                    ], 422);
                }
                $fitnessHub = FitnessHub::where('id', $fitnessHubId)
                    ->lockForUpdate()
                    ->first();

                if (!$fitnessHub) {
                    return response()->json([
                        'message' => 'Fitness Hub not found.'
                    ], 404);
                }

                $existingBookings = FitnessHubBooking::where('fitness_hub_id', $fitnessHubId)
                    ->where('booking_date', $date)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                foreach ($existingBookings as $booking) {

                    $bStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                    $bEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                    if ($bEnd->lessThanOrEqualTo($bStart)) {
                        $bEnd->addDay();
                    }

                    if ($start < $bEnd && $end > $bStart) {
                        return response()->json([
                            'message' => 'This time slot was just booked by another user. Please choose a different time.'
                        ], 409);
                    }
                }

                $startOfWeek = Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->startOfDay();
                $endOfWeek = Carbon::parse($date)->endOfWeek(Carbon::SUNDAY)->endOfDay();

                $weeklyBookings = FitnessHubBooking::where('unit', $unit)
                    ->where('fitness_hub_id', $fitnessHubId)
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
                    ->lockForUpdate()
                    ->get();

                $totalMinutes = $weeklyBookings->sum(function ($booking) {
                    $start = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                    $end = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                    if ($end->lessThanOrEqualTo($start)) {
                        $end->addDay();
                    }

                    return $start->diffInMinutes($end);
                });

                $newMinutes = $start->diffInMinutes($end);

                $maxMinutes = 120;
                $remainingMinutes = $maxMinutes - $totalMinutes;

                if ($remainingMinutes <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already reached the 2-hour weekly booking limit.'
                    ], 409);
                }

                if ($newMinutes > $remainingMinutes) {

                    $remainingHours = floor($remainingMinutes / 60);
                    $remainingMins = $remainingMinutes % 60;

                    $formattedTime = '';

                    if ($remainingHours > 0) {
                        $formattedTime .= $remainingHours . ' hour' . ($remainingHours > 1 ? 's' : '');
                    }

                    if ($remainingMins > 0) {
                        if ($formattedTime)
                            $formattedTime .= ' and ';
                        $formattedTime .= $remainingMins . ' minute' . ($remainingMins > 1 ? 's' : '');
                    }

                    return response()->json([
                        'success' => false,
                        'message' => "You can only book up to {$formattedTime} more for this week."
                    ], 409);
                }

                $last = FitnessHubBooking::lockForUpdate()
                    ->select('transaction_no')
                    ->where('transaction_no', 'like', '2SFH-%')
                    ->orderByDesc('id')
                    ->first();

                $lastNumber = $last
                    ? (int) str_replace('2SFH-', '', $last->transaction_no)
                    : 0;

                $startOfMonth = Carbon::parse($date)->startOfMonth();
                $endOfMonth = Carbon::parse($date)->endOfMonth();

                $cancellationCount = FitnessHubBooking::where('unit', $unit)
                    ->whereBetween('booking_date', [$startOfMonth, $endOfMonth])
                    ->where('booking_status', 2)
                    ->lockForUpdate()->count();

                if ($cancellationCount >= 2) {
                    return response()->json([
                        'message' => 'Your unit has reached the maximum of 2 cancellations this month. Booking is temporarily restricted.'
                    ], 403);
                }


                $latestWeeklyMinutes = FitnessHubBooking::where('unit', $unit)
                    ->where('fitness_hub_id', $fitnessHubId)
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
                    ->lockForUpdate()
                    ->get()
                    ->sum(function ($booking) {
                        $start = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                        $end = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                        if ($end->lessThanOrEqualTo($start)) {
                            $end->addDay();
                        }

                        return $start->diffInMinutes($end);
                    });

                if (($latestWeeklyMinutes + $newMinutes) > 120) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking limit reached due to another recent booking. Please try again.'
                    ], 409);
                }

                $booking = FitnessHubBooking::create([
                    'fitness_hub_id' => $fitnessHubId,
                    'booking_date' => $date,
                    'user_id' => $user->id,
                    'booking_start_time' => $start->format('H:i:s'),
                    'booking_end_time' => $end->format('H:i:s'),
                    'unit' => $unit,
                    'name' => strtoupper($user->name),
                    'resident_type' => strtoupper($resident->resident_type),
                    'contact_number' => $request->contact_number,
                    'booking_type' => strtoupper($request->booking_type),
                    'booking_status' => 1,
                    'created_by' => $user->id,
                ]);

                $booking->transaction_no = '2SFH-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);
                $booking->save();
                Mail::to($user->email)
                    ->queue(new UserFitnessHubBookingConfirmation($booking));

                Mail::to('concierge@twoserendra.com')
                    ->queue(new ConciergeFitnessHubBookingConfirmation($booking));

                $user->notify(new UserFitnessHubBookingBellNotification($booking));

                event(new FitnessHubBookingCreated($booking));

                return response()->json([
                    'success' => true,
                    'message' => 'FitnessHub Booking submitted successfully!',
                    'transaction_no' => $booking->transaction_no
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Fitness Hub Booking Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function getFitnessHubBookingDetails($id)
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

        $booking = FitnessHubBooking::with('fitnessHub')
            ->where('id', $id)
            ->whereIn('unit', $allowedUnits)
            ->first();

        if (!$booking) {
            return response()->json([
                'error' => 'Unauthorized or booking not found'
            ], 403);
        }

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
                'fitness_hub' => [
                    'fitness_hub_name' => optional($booking->fitnessHub)->fitness_hub_name
                ]
            ],
            'within_penalty' => $booking->isWithin12Hours(),
        ]);
    }

    public function cancelFitnessHubBooking(FitnessHubBooking $booking, Request $request)
    {
        try {
            $request->validate([
                'confirm' => 'nullable|boolean'
            ]);

            return DB::transaction(function () use ($booking, $request) {
                $user = auth()->user();

                $units = ResidentDetails::where('email', $user->email)
                    ->pluck('unit_no');

                $booking = FitnessHubBooking::where('id', $booking->id)
                    ->whereIn('unit', $units)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($booking->booking_status !== 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only active bookings can be cancelled.'
                    ], 400);
                }

                if ($booking->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This booking was created by another resident in your unit. Only the creator can cancel it.'
                    ], 403);
                }

                $booking->load('fitnessHub', 'user');
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
                    $booking->user?->notify(new UserFitnessHubBookingBellNotification($booking));

                    if ($booking->user?->email) {
                        Mail::to($booking->user->email)->queue(
                            new UserFitnessHubBookingCancellation($booking, $withPenalty, $penaltyAmount)
                        );
                    }

                    Mail::to('concierge@twoserendra.com')->queue(
                        new ConciergeFitnessHubBookingCancellation($booking, $withPenalty, $penaltyAmount)
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
            \Log::error('Cancel Fitness Hub Booking Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }

    public function showFitnessHubBookingDetails($id)
    {
        $booking = FitnessHubBooking::with('user', 'fitnessHub')->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        return view('frontend.user-fitness-hub-booking-details', compact('booking'));
    }

    public function fetchAllSlotsUserFitnessHub(Request $request)
    {
        $fitnessHubId = $request->input('fitness_hub_id');
        $date = $request->input('booking_date');

        $fitnessHub = FitnessHub::find($fitnessHubId);

        if (!$fitnessHub) {
            return response()->json(['error' => 'Fitness Hub not found'], 404);
        }

        if (!$fitnessHub->fitness_hub_start_time || !$fitnessHub->fitness_hub_end_time) {
            return response()->json(['error' => 'No Schedule']);
        }

        $start = Carbon::parse($date . ' ' . $fitnessHub->fitness_hub_start_time);
        $end = Carbon::parse($date . ' ' . $fitnessHub->fitness_hub_end_time);

        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $bookings = FitnessHubBooking::where('fitness_hub_id', $fitnessHubId)
            ->where('booking_date', $date)
            ->where('booking_status', 1)
            ->get();



        $slots = [];

        $dayOfWeek = Carbon::parse($date)->format('l');

        $blockedSlots = FitnessHubScheduleBlocking::where('fitness_hub_id', $fitnessHubId)
            ->where('day', $dayOfWeek)
            ->get();


        while ($start < $end) {
            $slotStart = $start->copy();
            $slotEnd = $slotStart->copy()->addHour();

            $status = 'Available';
            $label = null;

            // 🔴 BOOKING CHECK (highest priority)
            $matchedBooking = $bookings->first(function ($booking) use ($slotStart, $slotEnd) {

                $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
                $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

                if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
                    $bookingEnd->addDay();
                }

                return $bookingStart->lt($slotEnd) && $bookingEnd->gt($slotStart);
            });

            // ⚫ BLOCK CHECK
            $isBlocked = $blockedSlots->contains(function ($block) use ($slotStart, $slotEnd, $date) {

                $blockStart = Carbon::parse("$date {$block->start_time}");
                $blockEnd = Carbon::parse("$date {$block->end_time}");

                if ($blockEnd->lessThanOrEqualTo($blockStart)) {
                    $blockEnd->addDay();
                }

                return $blockStart->lt($slotEnd) && $blockEnd->gt($slotStart);
            });

            // 🎯 DECIDE STATUS
            if ($matchedBooking) {

                $status = 'Booked';
                $label = $matchedBooking->fitnessHub->fitness_hub_name ?? 'Booked';

            } elseif ($isBlocked) {

                $status = 'Blocked';
                $label = 'Blocked';

            } else {

                $status = 'Available';
                $label = 'Available';
            }

            $slots[] = [
                'time_range' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                'status' => $status,
                'label' => $label
            ];

            $start->addHour();
        }

        return response()->json([
            'activity_space' => 1,
            'slots' => $slots
        ]);
    }
}