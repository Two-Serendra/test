<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use App\Models\FunctionRoomBooking;
use Illuminate\Queue\SerializesModels;

class FunctionRoomBookingConfirmedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(FunctionRoomBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Your Function Room Booking is Confirmed')
            ->view('emails.user-function-room-booking-confirmed')
            ->with([
                'name' => $this->booking->user->name,
                'transaction_no' => $this->booking->transaction_no,
                'function_room' => $this->booking->functionRoom->function_room_name ?? 'Function Room',
                'date' => $this->booking->function_room_booking_date,
                'start_time' => $this->booking->event_start_time,
                'end_time' => $this->booking->event_end_time,
            ]);
    }
}
