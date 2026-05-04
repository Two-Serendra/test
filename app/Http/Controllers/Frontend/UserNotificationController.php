<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\UserAmenityBookingBellNotification;
use App\Notifications\UserFunctionRoomBookingBellNotification;
use App\Notifications\UserGreaseTrapBookingBellNotification;
use App\Notifications\UserPestControlBookingBellNotification;
use App\Notifications\UserFitnessHubBookingBellNotification;

class UserNotificationController extends Controller
{
    // public function index()
    // {
    //     return view('frontend.booking-list');
    // }

    // public function show($id)
    // {
    //     $notification = auth()->user()->notifications()->findOrFail($id);
    //     $notification->markAsRead();

    //     // Redirect to related page (e.g., booking details)
    //     if (isset($notification->data['booking_id'])) {
    //         return redirect()->route('booking.show', $notification->data['booking_id']);
    //     }

    //     return redirect()->route('notifications.index');
    // }

    public function show($id)
    {

        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $bookingId = $notification->data['booking_id'] ?? null;

        if (!$bookingId) {
            return redirect()->route('home');
        }

        return match ($notification->type) {

            UserAmenityBookingBellNotification::class
            => redirect()->route('show.amenity.booking.details', $bookingId),

            UserFunctionRoomBookingBellNotification::class
            => redirect()->route('show.functionroom.booking.details', $bookingId),

            UserGreaseTrapBookingBellNotification::class
            => redirect()->route('show.grease.trap.booking.details', $bookingId),

            UserPestControlBookingBellNotification::class
            => redirect()->route('show.pest.control.booking.details', $bookingId),

            UserFitnessHubBookingBellNotification::class
            => redirect()->route('show.fitness.hub.booking.details', $bookingId),




            //    UserOtherBookingBellNotification::class
            //     => redirect()->route('show.other.booking.details', $bookingId),

            default => redirect()->route('home'),
        };
    }


    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read');
    }

}


