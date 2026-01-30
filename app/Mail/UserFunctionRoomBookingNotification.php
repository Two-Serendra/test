<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserFunctionRoomBookingNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $mainBooking;
    public $allBookings;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(FunctionRoomBooking $mainBooking, $allBookings = [])
    {
        $this->mainBooking = $mainBooking;
        $this->allBookings = $allBookings;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $name = $this->mainBooking->user->name ?? 'Resident';

        // Collect the names of ALL function rooms booked
        $rooms = collect($this->allBookings)
            ->pluck('functionRoom.function_room_name')
            ->unique()
            ->implode(', ');

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Your Function Room Booking is Being Processed')
            ->view('emails.user-function-room-booking')
            ->with([
                'name' => $name,
                'transaction_no' => $this->mainBooking->transaction_no,
                'rooms' => $rooms,
                'date' => $this->mainBooking->function_room_booking_date,
                'start_time' => $this->mainBooking->event_start_time,
                'end_time' => $this->mainBooking->event_end_time,
            ]);
    }
}
