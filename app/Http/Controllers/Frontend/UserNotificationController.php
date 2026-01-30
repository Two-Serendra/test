<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index()
    {
        return view('notifications.index');
    }

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

        if (isset($notification->data['booking_id'])) {
            $route = $notification->data['type'] === 'amenity'
                ? route('show.amenity.booking.details', $notification->data['booking_id'])
                : route('show.functionroom.booking.details', $notification->data['booking_id']);

            return redirect($route);
        }

        return redirect()->route('notifications.index');
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


