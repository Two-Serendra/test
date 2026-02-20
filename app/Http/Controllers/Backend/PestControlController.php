<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PestControlBooking;
use App\Models\ResidentDetails;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PestControlController extends Controller
{
    public function AdminBookingPestControl()
    {
        $pestControlBookings = PestControlBooking::orderBy('created_at', 'DESC')->paginate(10);
        return view('backend.pest-control.pest-control-booking', compact('pestControlBookings'));

    }

    public function searchPestControlBooking(Request $request)
    {
        $searchBooking = $request->input('searchPestControlBooking');
        $currentDate = Carbon::today();

        $pestControlBookings = PestControlBooking::with('user')
            ->where('booking_date', '>=', $currentDate)
            ->when($searchBooking, function ($query, $searchBooking) {
                $query->where(function ($q) use ($searchBooking) {
                    $q->where('unit_no', 'LIKE', "%{$searchBooking}%")
                        ->orWhereHas('user', function ($userQuery) use ($searchBooking) {
                            $userQuery->where('name', 'LIKE', "%{$searchBooking}%");
                        });
                });
            })
            ->orderBy('booking_date', 'desc')
            ->paginate(10);

        $pestControlBookings->appends(['searchPestControlBooking' => $searchBooking]);

        return view('backend.pest-control.pest-control-booking', compact('pestControlBookings'));
    }



    public function getBookedSlotsAdminPestControl(Request $request)
    {
        $unit = strtoupper($request->unit);
        $areaLetter = preg_replace('/[^A-Z]/', '', $unit);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = PestControlBooking::whereDate('booking_date', $request->date)
            ->where('booking_status', 1)
            ->get(['booking_time_slot', 'unit_area']);

        $slotStatus = [];

        foreach ($bookings as $b) {
            $slot = $b->booking_time_slot;
            $area = $b->unit_area;

            if (!isset($slotStatus[$slot])) {
                $slotStatus[$slot] = ['lowrise' => false, 'highrise' => false];
            }

            if (in_array($area, $lowrise))
                $slotStatus[$slot]['lowrise'] = true;
            if (in_array($area, $highrise))
                $slotStatus[$slot]['highrise'] = true;
        }

        $blockedForUser = [];

        foreach ($slotStatus as $slot => $status) {
            if ($status[$userGroup]) {
                $blockedForUser[] = $slot;
            }
        }

        return response()->json([
            'booked_slots' => $blockedForUser
        ]);
    }


    public function AdminStorePestControlBooking(Request $request)
    {
        $maxRetries = 3;
        $attempt = 0;

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

        while ($attempt < $maxRetries) {
            try {
                DB::beginTransaction();

                $request->validate([
                    'name' => 'required|string|max:255',
                    'unit' => 'required|string|max:10',
                    'selectResidentType' => 'required|in:Owner,Tenant',
                    'booking_date' => 'required|date',
                    'booking_time_slot' => 'required|string',
                ]);

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();
                $unit = strtoupper($request->unit);
                $areaLetter = preg_replace('/[^A-Z]/', '', $unit);
                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();
                    return response()->json(['message' => 'Invalid unit area letter.'], 422);
                }

                $towerAreas = $towerGroup == 'lowrise'
                    ? ['A', 'B', 'C', 'D', 'E']
                    : ['F', 'G', 'H', 'I'];

                $existingBookings = PestControlBooking::whereDate('booking_date', $bookingDate)
                    ->whereIn('unit_area', $towerAreas)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                if ($existingBookings->contains('booking_time_slot', $request->booking_time_slot)) {
                    DB::rollBack();
                    return response()->json(['message' => 'Slot already taken. Please refresh slots.'], 409);
                }

                $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
                $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();

                $unitBookingsThisMonth = PestControlBooking::where('unit_no', $unit)
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$monthStart, $monthEnd])
                    ->lockForUpdate()
                    ->count();

                $freeBookingLimit = 1;
                $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;

                if ($chargedType == 2 && !$request->force_payment) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "You already used your free pest control booking for this month. This booking will require payment. Continue?",
                        'requires_payment' => true,
                        'remaining_free_bookings' => max($freeBookingLimit - $unitBookingsThisMonth, 0)
                    ], 409);
                }

                $booking = PestControlBooking::create([
                    'user_id' => auth()->id(),
                    'transaction_no' => '',
                    'unit_no' => $unit,
                    'resident_name' => $request->name,
                    'resident_type' => $request->selectResidentType,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                    'charged_type' => $chargedType,
                    'remarks' => $request->remarks,
                    'is_admin_created' => 1,
                ]);

                $booking->transaction_no = '2SPC-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                return response()->json([
                    'message' => 'Admin pest control booking created successfully.',
                    'charged_type' => $chargedType,
                    'free_used' => $unitBookingsThisMonth,
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000);
                    continue;
                }

                Log::error('Admin Pest Control Booking Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Database error.'], 500);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Admin Pest Control Booking Fatal', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong.'], 500);
            }
        }

        return response()->json(['message' => 'Could not complete booking. Please retry.'], 500);
    }


    public function getUpdatedPestControlTable()
    {
        $bookings = PestControlBooking::with('user')
            ->orderByDesc('created_at') // newest first
            ->paginate(10); // 10 per page

        return response()->json($bookings);
    }

    public function AdminStoreEmergencyPestControl(Request $request)
    {
        try {
            DB::beginTransaction();

            $bookingDate = Carbon::parse($request->booking_date)->toDateString();
            $unit = strtoupper(trim($request->unit));
            $areaLetter = preg_replace('/[^A-Z]/', '', $unit);
            $residentType = strtoupper($request->selectResidentType);


            $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
            $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();
            $unitBookingsThisMonth = PestControlBooking::where('unit_no', $unit)
                ->where('booking_status', 1)
                ->whereBetween('booking_date', [$monthStart, $monthEnd])
                ->count();

            $freeBookingLimit = 1;
            $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;


            if ($chargedType == 2 && !$request->force_payment) {
                DB::rollBack();
                return response()->json([
                    'message' => "You have already used your free pest control booking for this month. This emergency booking will require payment. Continue?",
                    'requires_payment' => true,
                    'remaining_free_bookings' => max($freeBookingLimit - $unitBookingsThisMonth, 0)
                ], 409);
            }


            $booking = PestControlBooking::create([
                'user_id' => auth()->id(),
                'transaction_no' => '',
                'unit_no' => $unit,
                'resident_name' => $request->name,
                'resident_type' => $residentType,
                'booking_date' => $bookingDate,
                'booking_time_slot' => $request->booking_time_slot,
                'charged_type' => $chargedType,
                'remarks' => $request->remarks,
                'emergency' => 1,
                'is_admin_created' => 1,
                'booking_status' => 1,
                'unit_area' => $areaLetter,
            ]);

            $booking->transaction_no = '2SEG-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
            $booking->save();

            DB::commit();

            return response()->json([
                'message' => 'Emergency pest control booking created successfully.',
                'charged_type' => $chargedType,
                'free_used' => $unitBookingsThisMonth,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin Emergency Pest Control Booking Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Something went wrong.'], 500);
        }
    }


    public function fetchPestControlBooking($id)
    {
        $pestControlBooking = PestControlBooking::with('user')->find($id);

        if (!$pestControlBooking) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'name' => $pestControlBooking->user->name ?? 'N/A',
            'unit_no' => $pestControlBooking->unit_no,
            'resident_type' => $pestControlBooking->resident_type,
            'remarks' => $pestControlBooking->remarks,
            'transaction_no' => $pestControlBooking->transaction_no,
            'booking_date' => Carbon::parse($pestControlBooking->booking_date)->format('F d, Y'),
            'booking_time_slot' => $pestControlBooking->booking_time_slot,
            'srf_no' => $pestControlBooking->srf_no,
            'charged_type' => $pestControlBooking->charged_type, // <-- add this
        ]);
    }

    public function AdminUpdatePestControlBooking(Request $request)
    {

        try {
            $booking = PestControlBooking::findOrFail($request->id);

            $booking->srf_no = $request->srf_no;
            $booking->remarks = $request->remarks;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Pest Control Booking updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function CancelPestControlBookingAdmin(PestControlBooking $booking)
    {
        try {
            $booking->load('user');

            $booking->booking_status = 2;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Booking has been cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.'
            ], 500);
        }
    }

    public function AdminBookingPestControlCalendar()
    {
        $schedules = PestControlBooking::with('user')
            ->where('booking_status', 1)
            ->get()
            ->map(function ($schedule) {

                $timeSlot = str_replace([' NN', ' MN'], [' PM', ' AM'], $schedule->booking_time_slot);
                [$start, $end] = explode('-', $timeSlot);

                $startTime = Carbon::parse(trim($start))->format('g:i A');
                $endTime = Carbon::parse(trim($end))->format('g:i A');

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->unit_no . ' (' . $startTime . ' - ' . $endTime . ')',
                    'start' => $schedule->booking_date . ' ' . Carbon::parse(trim($start))->format('H:i:s'),
                    'end' => $schedule->booking_date . ' ' . Carbon::parse(trim($end))->format('H:i:s'),
                    'allDay' => false,
                ];
            });

        return view('backend.pest-control.pest-control-booking-calendar', [
            'events' => $schedules
        ]);
    }

    public function fetchPestControlCalendarSchedule($id)
    {
        $schedule = PestControlBooking::with('user')->find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'id' => $schedule->id,
            'unit_no' => $schedule->unit_no,
            'name' => $schedule->user ? $schedule->user->name : 'N/A',
            'resident_type' => $schedule->resident_type,
            'booking_date' => date('F d, Y', strtotime($schedule->booking_date)),
            'booking_time_slot' => $schedule->booking_time_slot,
            'transaction_no' => $schedule->transaction_no,
            'srf_no' => $schedule->srf_no ?? 'N/A',
            'charged_type' => $schedule->charged_type,
        ]);
    }
}
