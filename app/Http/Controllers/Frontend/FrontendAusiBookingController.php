<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AusiBooking;
use App\Models\ResidentDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserAusiBookingConfirmation;
use App\Mail\ConciergeAusiBookingConfirmation;
use App\Events\AusiBookingCreated;
use App\Notifications\UserAusiBookingBellNotification;
use App\Mail\UserAusiBookingCancellation;
use App\Mail\ConciergeAusiBookingCancellation;
class FrontendAusiBookingController extends Controller
{
    public function ausiBookingUser()
    {
        $residences = auth()->check()
            ? DB::table('resident_details')
                ->where('email', auth()->user()->email)
                ->select('id', 'unit_no', 'resident_type')
                ->get()
            : collect();
        return view('frontend.ausi-booking', compact('residences'));
    }    

    public function getBookedSlotsAusi(Request $request)
    {

        $resident = ResidentDetails::findOrFail($request->resident_id);

        $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);

        $lowrise = ['A', 'B', 'C', 'D', 'E'];
        $highrise = ['F', 'G', 'H', 'I'];

        $userGroup = in_array($areaLetter, $lowrise) ? 'lowrise' : 'highrise';

        $bookings = AusiBooking::whereDate('booking_date', $request->date)
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

    // public function storeAusiBooking(Request $request)
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

    //             \Log::info('AUSI BOOKING REQUEST', [
    //                 'auth' => auth()->check(),
    //                 'email' => auth()->user()->email ?? null,
    //                 'superapp_email' => $request->superapp_email,
    //             ]);

    //             $isSuperapp = $request->filled('superapp_email');

    //             $email = $isSuperapp
    //                 ? $request->superapp_email
    //                 : optional(auth()->user())->email;

    //             $user = $isSuperapp
    //                 ? (object) [
    //                     'email' => $email,
    //                     'name' => $request->superapp_user_name ?? 'Superapp User'
    //                 ]
    //                 : auth()->user() ?? (object) [
    //                     'email' => $email,
    //                     'name' => 'Guest User'
    //                 ];

    //             $userId = $isSuperapp ? null : optional(auth()->user())->id;

    //             $resident = ResidentDetails::where('id', $request->resident_id_ausi)
    //                 ->where('email', $email)
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$resident) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => 'Unauthorized unit selection.'
    //                 ], 403);
    //             }

    //             $bookingDate = Carbon::parse($request->booking_date)->toDateString();

    //             $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);
    //             $towerGroup = $towerGroups[$areaLetter] ?? null;

    //             if (!$towerGroup) {
    //                 DB::rollBack();
    //                 return response()->json(['message' => 'Unknown area for your unit.'], 422);
    //             }
    //             $towerAreas = $towerGroup == 'lowrise' ? ['A', 'B', 'C', 'D', 'E'] : ['F', 'G', 'H', 'I'];
    //             $existingBookings = AusiBooking::whereDate('booking_date', $bookingDate)
    //                 ->whereIn('unit_area', $towerAreas)
    //                 ->where('booking_status', 1)
    //                 ->lockForUpdate()
    //                 ->get();


    //             $slotTaken = $existingBookings->contains('booking_time_slot', $request->booking_time_slot);
    //             if ($slotTaken) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'message' => 'Slot already taken just now.',
    //                     'type' => 'slot_taken'
    //                 ], 409);
    //             }

    //             $existingUnitBooking = AusiBooking::where('unit_no', strtoupper($resident->unit_no))
    //                 ->where('booking_status', 1)
    //                 ->whereYear('booking_date', Carbon::parse($bookingDate)->year)
    //                 ->lockForUpdate()
    //                 ->exists();

    //             $forceOverride = $request->boolean('force_override');

    //             if ($existingUnitBooking && !$forceOverride) {
    //                 DB::rollBack();

    //                 return response()->json([
    //                     'message' => 'This unit already has a booking for this year. Do you want to proceed anyway?',
    //                     'type' => 'unit_already_booked'
    //                 ], 409);
    //             }

    //             $booking = AusiBooking::create([
    //                 'user_id' => $userId,
    //                 'created_by' => $userId,
    //                 'transaction_no' => '',
    //                 'unit_no' => strtoupper($resident->unit_no),
    //                 'resident_type' => $resident->resident_type,
    //                 'name' => strtoupper($user->name),
    //                 'booking_date' => $bookingDate,
    //                 'booking_time_slot' => $request->booking_time_slot,
    //                 'unit_area' => $areaLetter,

    //             ]);

    //             $booking->transaction_no = '2AUSI-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    //             $booking->save();

    //             DB::commit();

    //             $booking->load('user');
    //             $recipientEmail = $email;
    //             DB::afterCommit(function () use ($booking, $recipientEmail) {

    //                 if ($recipientEmail) {
    //                     Mail::to($recipientEmail)
    //                         ->queue(new UserAusiBookingConfirmation($booking));
    //                 }

    //                 Mail::to('concierge@twoserendra.com')
    //                     ->queue(new ConciergeAusiBookingConfirmation($booking));

    //                 event(new AusiBookingCreated($booking));

    //                 if ($booking->user) {
    //                     $booking->user->notify(new UserAusiBookingBellNotification($booking));
    //                 }
    //             });

    //             return response()->json(['message' => 'Ausi booking submitted successfully.']);

    //         } catch (\Illuminate\Database\QueryException $e) {
    //             DB::rollBack();

    //             if (in_array($e->errorInfo[1], [1213, 1205])) {
    //                 $attempt++;
    //                 usleep(100000);
    //                 continue;
    //             }
    //             Log::error('Ausi Booking Error', ['error' => $e->getMessage()]);
    //             return response()->json(['message' => 'Something went wrong while saving the booking.'], 500);
    //         } catch (\Throwable $e) {
    //             DB::rollBack();
    //             Log::error('Ausi Booking Fatal Error', ['error' => $e->getMessage()]);
    //             return response()->json(['message' => 'Something went wrong.'], 500);
    //         }
    //     }

    //     return response()->json(['message' => 'Could not complete booking. Please try again.'], 500);
    // }

    public function storeAusiBooking(Request $request)
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

                $user = auth()->user();

                if (!$user) {
                    return response()->json([
                        'message' => 'Unauthenticated.'
                    ], 401);
                }

                $email = $user->email;
                $userId = $user->id;

                Log::info('AUSI BOOKING REQUEST', [
                    'auth' => true,
                    'user_id' => $userId,
                    'email' => $email,
                ]);

                $resident = ResidentDetails::where('id', $request->resident_id_ausi)
                    ->where('email', $email)
                    ->lockForUpdate()
                    ->first();

                if (!$resident) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Unauthorized unit selection.'
                    ], 403);
                }

                $bookingDate = Carbon::parse($request->booking_date)->toDateString();

                $areaLetter = preg_replace('/[^A-Z]/', '', $resident->unit_no);
                $towerGroup = $towerGroups[$areaLetter] ?? null;

                if (!$towerGroup) {
                    DB::rollBack();
                    return response()->json(['message' => 'Unknown area for your unit.'], 422);
                }

                $towerAreas = $towerGroup == 'lowrise'
                    ? ['A', 'B', 'C', 'D', 'E']
                    : ['F', 'G', 'H', 'I'];

                $existingBookings = AusiBooking::whereDate('booking_date', $bookingDate)
                    ->whereIn('unit_area', $towerAreas)
                    ->where('booking_status', 1)
                    ->lockForUpdate()
                    ->get();

                $slotTaken = $existingBookings->contains('booking_time_slot', $request->booking_time_slot);

                if ($slotTaken) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Slot already taken just now.',
                        'type' => 'slot_taken'
                    ], 409);
                }

                $existingUnitBooking = AusiBooking::where('unit_no', strtoupper($resident->unit_no))
                    ->where('booking_status', 1)
                    ->whereYear('booking_date', $bookingDate)
                    ->lockForUpdate()
                    ->exists();

                $forceOverride = $request->boolean('force_override');

                if ($existingUnitBooking && !$forceOverride) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'This unit already has a booking for this year. Do you want to proceed anyway?',
                        'type' => 'unit_already_booked'
                    ], 409);
                }

                $booking = AusiBooking::create([
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'transaction_no' => '',
                    'unit_no' => strtoupper($resident->unit_no),
                    'resident_type' => $resident->resident_type,
                    'name' => strtoupper($user->name),
                    'booking_date' => $bookingDate,
                    'booking_time_slot' => $request->booking_time_slot,
                    'unit_area' => $areaLetter,
                ]);

                $booking->transaction_no = '2AUSI-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
                $booking->save();

                DB::commit();

                $booking->load('user');

                DB::afterCommit(function () use ($booking, $email) {

                    Mail::to($email)
                        ->queue(new UserAusiBookingConfirmation($booking));

                    Mail::to('concierge@twoserendra.com')
                        ->queue(new ConciergeAusiBookingConfirmation($booking));

                    event(new AusiBookingCreated($booking));

                    if ($booking->user) {
                        $booking->user->notify(new UserAusiBookingBellNotification($booking));
                    }
                });

                return response()->json([
                    'message' => 'Ausi booking submitted successfully.'
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                if (in_array($e->errorInfo[1], [1213, 1205])) {
                    $attempt++;
                    usleep(100000);
                    continue;
                }

                Log::error('Ausi Booking Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong while saving the booking.'], 500);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Ausi Booking Fatal Error', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Something went wrong.'], 500);
            }
        }

        return response()->json(['message' => 'Could not complete booking. Please try again.'], 500);
    }

    public function CancelAusiBooking(AusiBooking $booking)
    {
        try {
            return DB::transaction(function () use ($booking) {
                $user = auth()->user();

                $units = ResidentDetails::where('email', $user->email)
                    ->pluck('unit_no');

                $booking = AusiBooking::where('id', $booking->id)
                    ->whereIn('unit_no', $units)
                    ->lockForUpdate()
                    ->firstOrFail();

                $booking->load('user');

                if ($booking->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This booking was created by another resident in your unit. Only the creator can cancel it.'
                    ], 403);
                }

                if ($booking->display_status === 'Cancelled') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking is already cancelled.'
                    ], 400);
                }

                if ($booking->display_status === 'Completed') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot cancel a completed booking.'
                    ], 400);
                }



                $booking->booking_status = 2;
                $booking->cancelled_at = now();
                $booking->cancelled_by = auth()->id();
                $booking->save();

                DB::afterCommit(function () use ($booking) {

                    if ($booking->user) {
                        $booking->user->notify(
                            new UserAusiBookingBellNotification($booking)
                        );
                    }

                    if ($booking->user?->email) {
                        Mail::to($booking->user->email)
                            ->queue(new UserAusiBookingCancellation($booking));
                    }

                    Mail::to('concierge@twoserendra.com')
                        ->queue(new ConciergeAusiBookingCancellation($booking));
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Booking cancelled successfully.'
                ]);
            });


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function showAusiBookingDetails($id)
    {
        $booking = AusiBooking::with('user')->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('frontend.user-ausi-booking-details', compact('booking'));
    }

}

