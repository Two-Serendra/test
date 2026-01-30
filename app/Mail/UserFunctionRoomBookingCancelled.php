<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class UserFunctionRoomBookingCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(FunctionRoomBooking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $name = $this->booking->user->name ?? 'Resident';

        return $this->subject('Your Function Room Booking Has Been Cancelled')
            ->view('emails.user-function-room-booking-cancellation')
            ->with([
                // 'name' => $this->booking->user->name,
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'function_room' => $this->booking->functionRoom->function_room_name ?? 'Function Room',
                'date' => $this->booking->function_room_booking_date,
                'start_time' => $this->booking->event_start_time,
                'end_time' => $this->booking->event_end_time,
            ]);
    }
}
