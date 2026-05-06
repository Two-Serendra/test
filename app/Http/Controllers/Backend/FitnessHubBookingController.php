<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\FitnessHubDateBlocking;
use App\Models\FitnessHubScheduleBlocking;
use Illuminate\Http\Request;
use App\Models\FitnessHubBooking;
use App\Models\FitnessHub;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class FitnessHubBookingController extends Controller
{
  public function AdminFitnessHubBooking()
  {
    $FitnessHubBookings = FitnessHubBooking::with(['fitnessHub', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
      ->whereDate('booking_date', '>=', Carbon::today())
      ->latest()
      ->orderBy('id', 'desc')
      ->paginate(10);
    $FitnessHubs = FitnessHub::all();
    return view('backend.fitness-hubs.admin-fitness-hub-booking', compact('FitnessHubBookings', 'FitnessHubs'));
  }

  public function searchBookingFitnessHub(Request $request)
  {
    $searchTerm = $request->input('searchBookingFitnessHub');

    $FitnessHubs = FitnessHub::all();

    $FitnessHubBookings = FitnessHubBooking::with('fitnessHub')
      ->where(function ($query) use ($searchTerm) {
        $query->where('transaction_no', 'LIKE', "%{$searchTerm}%")
          ->orWhere('unit', 'LIKE', "%{$searchTerm}%")
          ->orWhere('resident_type', 'LIKE', "%{$searchTerm}%")
          ->orWhere('name', 'LIKE', "%{$searchTerm}%")
          ->orWhere('contact_number', 'LIKE', "%{$searchTerm}%");
      })
      ->paginate(10)
      ->appends(['searchBookingFitnessHub' => $searchTerm]); // ✅ correct

    return view('backend.fitness-hubs.admin-fitness-hub-booking', compact(
      'FitnessHubBookings',
      'FitnessHubs',
      'searchTerm'
    ));
  }


  public function adminFetchAvailableTimesFitnessHub(Request $request)
  {
    $fitnessHubId = $request->input('fitness_hub_id');
    $date = $request->input('booking_date');

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


    $dayOfWeek = Carbon::parse($date)->format('l'); // Monday, Tuesday, etc.

    $blockedSlots = FitnessHubScheduleBlocking::where('fitness_hub_id', $fitnessHubId)
      ->where('day', $dayOfWeek)
      ->get();

    foreach ($blockedSlots as $block) {

      $blockStart = Carbon::parse("$date {$block->start_time}");
      $blockEnd = Carbon::parse("$date {$block->end_time}");

      if ($blockEnd->lessThanOrEqualTo($blockStart)) {
        $blockEnd->addDay();
      }

      while ($blockStart < $blockEnd) {
        $occupiedSlots[$blockStart->format('H:i')] = true;
        $blockStart->addHour();
      }
    }


    $availableTimePairs = [];
    $now = Carbon::now();

    $currentSlot = clone $start;

    while ($currentSlot < $end) {

      if ($date === $now->toDateString() && $currentSlot <= $now) {
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


  public function adminFetchAvailableEndTimesFitnessHub(Request $request)
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

    $dayOfWeek = Carbon::parse($date)->format('l');

    $blockedSlots = FitnessHubScheduleBlocking::where('fitness_hub_id', $fitnessHubId)
      ->where('day', $dayOfWeek)
      ->get();

    foreach ($blockedSlots as $block) {

      $blockStart = Carbon::parse("$date {$block->start_time}");
      $blockEnd = Carbon::parse("$date {$block->end_time}");

      if ($blockEnd->lessThanOrEqualTo($blockStart)) {
        $blockEnd->addDay();
      }

      while ($blockStart < $blockEnd) {
        $slotKey = $blockStart->format('H:i');

        // 🚨 Force it to be FULLY occupied
        $occupiedSlots[$slotKey] = $maxBooking;

        $blockStart->addHour();
      }
    }

    $availableEndTimes = [];
    $current = clone $start;

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


  public function adminCheckUnitBookingFitnessHub(Request $request)
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

    // 🎯 Get week range (Monday → Sunday)
    $startOfWeek = Carbon::parse($selectedDate)->startOfWeek();
    $endOfWeek = Carbon::parse($selectedDate)->endOfWeek();

    \Log::info('Weekly Range', [
      'start' => $startOfWeek,
      'end' => $endOfWeek
    ]);

    // ✅ Get bookings for this unit within the week
    $bookings = FitnessHubBooking::where('unit', $unit)
      ->where('fitness_hub_id', $fitnessHubId)
      ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
      ->where('booking_status', 1)
      ->get(['booking_start_time', 'booking_end_time']);

    $totalHours = 0;

    foreach ($bookings as $booking) {
      $start = Carbon::parse($booking->booking_start_time);
      $end = Carbon::parse($booking->booking_end_time);

      if ($end->lessThanOrEqualTo($start)) {
        $end->addDay();
      }

      $hours = $start->diffInHours($end);
      $totalHours += $hours;
    }


    $maxHours = 2;

    return response()->json([
      'success' => true,
      'count' => $totalHours,
      'maxBookings' => $maxHours,
    ]);
  }

  public function AdminNewBookingFitnessHub(Request $request)
  {
    try {
      return DB::transaction(function () use ($request) {

        $fitnessHubId = $request->fitness_hub_id;
        $date = $request->booking_date;
        $unit = strtoupper(trim($request->unit));

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

        $startOfWeek = Carbon::parse($date)->startOfWeek();
        $endOfWeek = Carbon::parse($date)->endOfWeek();

        $weeklyBookings = FitnessHubBooking::where('unit', $unit)
          ->where('fitness_hub_id', $fitnessHubId)
          ->whereBetween('booking_date', [$startOfWeek, $endOfWeek])
          ->where('booking_status', 1)
          ->lockForUpdate()
          ->get();

        $totalMinutes = 0;

        foreach ($weeklyBookings as $booking) {
          $bStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
          $bEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

          if ($bEnd->lessThanOrEqualTo($bStart)) {
            $bEnd->addDay();
          }

          $totalMinutes += $bStart->diffInMinutes($bEnd);
        }

        $newMinutes = $start->diffInMinutes($end);

        $maxMinutes = 120;
        $remainingMinutes = $maxMinutes - $totalMinutes;

        if ($remainingMinutes <= 0) {
          return response()->json([
            'success' => false,
            'message' => 'The unit already reached the 2-hour weekly booking limit.'
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
            'message' => "The unit can only book up to {$formattedTime} more for this week."
          ], 409);
        }

        $booking = FitnessHubBooking::create([
          'fitness_hub_id' => $fitnessHubId,
          'booking_date' => $date,
          'booking_start_time' => $start->format('H:i:s'),
          'booking_end_time' => $end->format('H:i:s'),
          'unit' => $unit,
          'name' => strtoupper($request->name),
          'resident_type' => strtoupper($request->selectResidentType),
          'contact_number' => $request->contact_number,
          'booking_type' => strtoupper($request->booking_type),
          'booking_status' => 1,
          'created_by' => auth()->id(),
        ]);

        $booking->transaction_no = '2SFH-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);
        $booking->save();

        return response()->json([
          'message' => 'Booking successfully created!'
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

  public function adminFetchInfoFitnessHubBooking($id)
  {
    $booking = FitnessHubBooking::with(['fitnessHub'])->find($id);
    if (!$booking) {
      return response()->json(['message' => 'Data not found'], 404);
    }

    $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('h:i A');
    $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format(format: 'h:i A');

    return response()->json([
      'booking' => [
        'id' => $booking->id,
        'transaction_no' => $booking->transaction_no,
        'unit' => $booking->unit,
        'name' => $booking->name,
        'contact' => $booking->contact_number,
        'booking_type' => $booking->booking_type,
        'resident_type' => $booking->resident_type,
        'booking_date' => $booking->booking_date,
        'booking_start_time' => $booking->booking_start_time,
        'booking_end_time' => $booking->booking_end_time,
        'booking_status' => $booking->booking_status,
        'penalty_amount' => $booking->penalty_amount,
        'penalty_waived' => $booking->penalty_waived,
        'has_penalty' => $booking->penalty_amount > 0,
        'cancelled_at' => $booking->cancelled_at,
        'fitness_hub' => [
          'name' => optional($booking->fitnessHub)->fitness_hub_name
        ],
      ]
    ]);
  }

  public function cancelFitnessHubBooking(FitnessHubBooking $booking, Request $request)
  {
    try {

      $booking->load('fitnessHub');

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
      $bookings = FitnessHubBooking::where('transaction_no', $transactionNo)->get();

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

  public function getUpdatedFitnessHubBookingsTable()
  {
    $currentDate = Carbon::today();

    $bookings = FitnessHubBooking::with(['fitnessHub', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
      ->whereDate('booking_date', '>=', $currentDate)
      ->latest()
      ->orderBy('id', 'desc')
      ->paginate(10);

    $bookings->getCollection()->transform(function ($booking) {
      return [
        'id' => $booking->id,
        'transaction_no' => $booking->transaction_no,
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
        'penalty_applied_at' => $booking->penalty_applied_at
          ? Carbon::parse($booking->penalty_applied_at)->format('Y-m-d H:i:s')
          : null,
        'penalty_waived_at' => $booking->penalty_waived_at
          ? Carbon::parse($booking->penalty_waived_at)->format('Y-m-d H:i:s')
          : null,
        'created_at' => Carbon::parse($booking->created_at)->format('Y-m-d H:i:s'),
        'updated_at' => Carbon::parse($booking->updated_at)->format('Y-m-d H:i:s'),
        'penalty_applied_by' => $booking->penaltyAppliedBy?->name ?? null,

        'fitness_hub' => [
          'fitness_hub_name' => optional($booking->fitnessHub)->fitness_hub_name
        ],
      ];
    });

    return response()->json([
      'data' => $bookings->items(),
      'links' => (string) $bookings->links('vendor.pagination.bootstrap-5')
    ]);
  }

  public function markAsNoShowFitnessHubBooking(FitnessHubBooking $booking)
  {
    try {

      if ($booking->penalty_amount > 0) {
        return response()->json([
          'success' => false,
          'message' => 'Penalty already applied.'
        ]);
      }

      $transactionNo = $booking->transaction_no;

      $bookings = FitnessHubBooking::where('transaction_no', $transactionNo)->get();

      foreach ($bookings as $b) {

        $b->penalty_amount = 1000;
        $b->booking_status = 4;
        $b->has_penalty = true;
        $b->penalty_applied_at = now();
        $b->penalty_applied_by = auth()->id();
        $b->cancelled_at = now();
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

  public function managePenaltyFitnessHub(FitnessHubBooking $booking, Request $request)
  {
    try {

      $booking->load('fitnessHub');

      $action = $request->input('action');

      $transactionNo = $booking->transaction_no;
      $bookings = FitnessHubBooking::where('transaction_no', $transactionNo)->get();

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

  public function fetchAllSlotsAdminFitnessHub(Request $request)
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

    while ($start < $end) {
      $slotStart = $start->copy();
      $slotEnd = $slotStart->copy()->addHour();

      $isOccupied = $bookings->contains(function ($booking) use ($slotStart, $slotEnd) {
        $bookingStart = Carbon::parse($booking->booking_date . ' ' . $booking->booking_start_time);
        $bookingEnd = Carbon::parse($booking->booking_date . ' ' . $booking->booking_end_time);

        if ($bookingEnd->lessThanOrEqualTo($bookingStart)) {
          $bookingEnd->addDay();
        }

        return $bookingStart->lt($slotEnd) && $bookingEnd->gt($slotStart);
      });

      $slots[] = [
        'time_range' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
        'slots' => [
          $isOccupied ? 'Occupied' : 'Available'
        ]
      ];

      $start->addHour();
    }

    return response()->json([
      'activity_space' => 1,
      'slots' => $slots
    ]);
  }

  public function fetchFitnessHubBlockedDates(Request $request)
  {
    $amenityId = $request->input('amenity_id');
    $blockedDatesQuery = FitnessHubDateBlocking::where('blocking_status', 1);

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


  public function historyFintessHub(Request $request)
  {
    $currentDate = Carbon::now()->toDateString();
    $fitnessHubBookings = FitnessHubBooking::with(['fitnessHub', 'user', 'cancelledBy', 'waivedBy', 'penaltyAppliedBy'])
      ->whereDate('booking_date', '<', $currentDate)
      ->orderBy('booking_date', 'desc')
      ->paginate(10);

    foreach ($fitnessHubBookings as $booking) {
      $booking->booking_start_time = Carbon::parse($booking->booking_start_time)->format('H:i a');
      $booking->booking_end_time = Carbon::parse($booking->booking_end_time)->format('H:i a');
    }

    return view('backend.fitness-hubs.admin-fitness-hub-booking-records', compact('fitnessHubBookings'));
  }


  public function AdminFitnessHubBookingCalendar()
  {
    $schedules = FitnessHubBooking::with('fitnessHub')
      ->where('booking_status', 1)
      ->get()
      ->map(function ($schedule) {
        $bookingDate = Carbon::parse($schedule->booking_date);
        $startTime = Carbon::parse($schedule->booking_start_time)->format('g a');
        $endTime = Carbon::parse($schedule->booking_end_time)->format('g a');
        $startDateTime = $bookingDate->format('Y-m-d') . 'T' . $schedule->booking_start_time;
        $endDateTime = $bookingDate->format('Y-m-d') . 'T' . $schedule->booking_end_time;

        $fitnessHubName = $schedule->fitnessHub ? $schedule->fitnessHub->fitness_hub_name : 'Unknown Fitness Hub';

        return [
          'id' => $schedule->id,
          'title' => $schedule->unit . ' (' . $startTime . ' - ' . $endTime . ') ' . $fitnessHubName,
          'start' => $startDateTime,
          'end' => $endDateTime,
          'fitness_hub_id' => $schedule->fitness_hub_id
        ];
      });

    return view('backend.fitness-hubs.admin-fitness-hub-booking-calendar', ['events' => $schedules]);

  }

  public function fetchFitnessHubCalendarInfo($id)
  {
    $schedule = FitnessHubBooking::with('fitnessHub')->find($id);

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
      'fitness_hub_name' => strtoupper($schedule->fitnessHub->fitness_hub_name),

    ]);
  }
}
