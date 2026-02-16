<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\PestControlBooking;
use App\Models\ResidentDetails;
use App\Mail\UserPestControlBookingConfirmation;
use App\Mail\ConciergePestControlBookingConfirmation;
use App\Mail\UserPestControlBookingCancellation;
use App\Mail\ConciergePestControlBookingCancellation;
use App\Events\PestControlBookingCreated;
use App\Notifications\UserPestControlBookingBellNotification;

class PestControlController extends Controller
{
    public function pestControl()
    {
        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();
        return view('frontend.pest-control-booking', compact('residences'));
    }

    // public function storePestControlBooking(Request $request)
    // {
    //     $maxRetries = 3;
    //     $attempt = 0;

    //     $towerGroups = [
    //         'A' => 'lowrise',
    //         'B' => 'lowrise',
    //         'C' => 'lowrise',
    //         'D' => 'lowrise',
    //         'E' => 'lowrise',
    //         'F' => 'highrise',
    //         'G' => 'highrise',
    //         'H' => 'highrise',
    //         'I' => 'highrise',
    //     ];

    //     while ($attempt < $maxRetries) {
    //         try {
    //             DB::beginTransaction();

    //             $resident = ResidentDetails::find($request->resident_id_pest_control);
    //             if (!$resident) {
    //                 DB::rollBack();
    //                 return response()->json(['message' => 'Invalid residence selected.'], 422);
    //             }

    //             $bookingDate = Carbon::parse($request->booking_date)->toDateString();


    //             $lastId = (PestControlBooking::max('id') ?? 0) + 1;
    //             $transactionNo = '2SPC-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);


    //             $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);
    //             $towerGroup = $towerGroups[$areaLetter] ?? null;

    //             if (!$towerGroup) {
    //                 DB::rollBack();
    //                 return response()->json(['message' => 'Unknown area for your unit.'], 422);
    //             }


    //             $slotQuery = PestControlBooking::whereDate('booking_date', $bookingDate)
    //                 ->where('booking_time_slot', $request->booking_time_slot)
    //                 ->where('booking_status', 1)
    //                 ->whereIn(
    //                     'unit_area',
    //                     $towerGroup == 'lowrise'
    //                     ? ['A', 'B', 'C', 'D', 'E']
    //                     : ['F', 'G', 'H', 'I']
    //                 )
    //                 ->lockForUpdate()
    //                 ->get();

    //             if ($slotQuery->count() > 0) {
    //                 DB::rollBack();
    //                 return response()->json(['message' => 'Slot already taken just now'], 409);
    //             }


    //             $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
    //             $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();

    //             $unitBookingsThisMonth = PestControlBooking::where('unit_no', $resident->unit_no)
    //                 ->where('booking_status', 1)
    //                 ->whereBetween('booking_date', [$monthStart, $monthEnd])
    //                 ->count();

    //             $freeBookingLimit = 1;

    //             $remainingFreeBookings = max($freeBookingLimit - $unitBookingsThisMonth, 0);
    //             $chargedType = $unitBookingsThisMonth < $freeBookingLimit ? 1 : 2;

    //             if ($chargedType == 2 && !$request->force_payment) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => "You already used your free pest control booking for this month. This booking will require payment. Continue?",
    //                     'requires_payment' => true,
    //                     'remaining_free_bookings' => $remainingFreeBookings
    //                 ], 409);
    //             }



    //             $booking = PestControlBooking::create([
    //                 'user_id' => auth()->id(),
    //                 'transaction_no' => $transactionNo,
    //                 'unit_no' => $resident->unit_no,
    //                 'resident_type' => $resident->resident_type,
    //                 'booking_date' => $bookingDate,
    //                 'booking_time_slot' => $request->booking_time_slot,
    //                 'unit_area' => $areaLetter,
    //                 'charged_type' => $chargedType,
    //             ]);

    //             $booking->transaction_no = '2SPC-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    //             $booking->save();

    //             $booking->load('user');
    //             if ($booking->user?->email) {
    //                 Mail::to($booking->user->email)
    //                     ->queue(new UserPestControlBookingConfirmation($booking));
    //             }

    //             Mail::to('concierge@twoserendra.com')
    //                 ->queue(new ConciergePestControlBookingConfirmation($booking));

    //             event(new PestControlBookingCreated($booking));
    //             $booking->user?->notify(new UserPestControlBookingBellNotification($booking));

    //             DB::commit();

    //             return response()->json(['message' => 'Pest control booking submitted successfully.']);

    //         } catch (\Illuminate\Database\QueryException $e) {
    //             DB::rollBack();
    //             if (in_array($e->errorInfo[1], [1213, 1205])) {
    //                 $attempt++;
    //                 usleep(100000);
    //                 continue;
    //             }
    //             Log::error('Pest Control Booking Error', ['error' => $e->getMessage()]);
    //             return response()->json(['message' => 'Something went wrong while saving the booking.'], 500);
    //         } catch (\Throwable $e) {
    //             DB::rollBack();
    //             Log::error('Pest Control Booking Fatal Error', ['error' => $e->getMessage()]);
    //             return response()->json(['message' => 'Something went wrong.'], 500);
    //         }
    //     }

    //     return response()->json(['message' => 'Could not complete booking. Please try again.'], 500);
    // }


    public function storePestControlBooking(Request $request)
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

                // Lock resident row to safely check monthly free bookings
                $resident = ResidentDetails::where('id', $request->resident_id_pest_control)
                    ->lockForUpdate()
                    ->first();

                if (!$resident) {
                    DB::rollBack();
                    return response()->json(['message' => 'Invalid residence selected.'], 422);
                }

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();

                $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);
                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();
                    return response()->json(['message' => 'Unknown area for your unit.'], 422);
                }

                // Lock all bookings for the same date + tower group to avoid race
                $towerAreas = $towerGroup == 'lowrise' ? ['A', 'B', 'C', 'D', 'E'] : ['F', 'G', 'H', 'I'];
                $existingBookings = PestControlBooking::whereDate('booking_date', $bookingDate)
                    ->whereIn('unit_area', $towerAreas)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                // Check if requested slot is already taken
                $slotTaken = $existingBookings->contains('booking_time_slot', $request->booking_time_slot);
                if ($slotTaken) {
                    DB::rollBack();
                    return response()->json(['message' => 'Slot already taken just now'], 409);
                }

                // Check free booking quota safely (resident row is locked)
                $monthStart = Carbon::parse($bookingDate)->startOfMonth()->toDateString();
                $monthEnd = Carbon::parse($bookingDate)->endOfMonth()->toDateString();

                $unitBookingsThisMonth = PestControlBooking::where('unit_no', $resident->unit_no)
                    ->where('booking_status', 1)
                    ->whereBetween('booking_date', [$monthStart, $monthEnd])
                    ->lockForUpdate() // lock all month bookings for this unit
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

                // Create booking
                $booking = PestControlBooking::create([
                    'user_id' => auth()->id(),
                    'transaction_no' => '', // will set after insert
                    'unit_no' => $resident->unit_no,
                    'resident_type' => $resident->resident_type,
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                    'charged_type' => $chargedType,
                ]);

                // Set transaction number based on actual ID
                $booking->transaction_no = '2SPC-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                // Dispatch emails / notifications AFTER commit
                $booking->load('user');
                DB::afterCommit(function () use ($booking) {
                    if ($booking->user?->email) {
                        Mail::to($booking->user->email)
                            ->queue(new UserPestControlBookingConfirmation($booking));
                    }

                    Mail::to('concierge@twoserendra.com')
                        ->queue(new ConciergePestControlBookingConfirmation($booking));

                    event(new PestControlBookingCreated($booking));
                    $booking->user?->notify(new UserPestControlBookingBellNotification($booking));
                });

                return response()->json(['message' => 'Pest control booking submitted successfully.']);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                // deadlock / lock wait retry
                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000);
                    continue;
                }
                Log::error('Pest Control Booking Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong while saving the booking.'], 500);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Pest Control Booking Fatal Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong.'], 500);
            }
        }

        return response()->json(['message' => 'Could not complete booking. Please try again.'], 500);
    }


    public function getBookedSlotsPestControl(Request $request)
    {


        $resident = ResidentDetails::findOrFail($request->resident_id);
        $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);

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
            // If same tower already booked → block for user
            if ($status[$userGroup]) {
                $blockedForUser[] = $slot;
            }
        }

        return response()->json([
            'blocked_for_user' => $blockedForUser,
            'raw_status' => $slotStatus
        ]);
    }

    public function showPestControlBookingDetails($id)
    {
        $booking = PestControlBooking::with('user')->findOrFail($id);
        return view('frontend.user-pest-control-booking-details', compact('booking'));
    }

}
