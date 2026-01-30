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
    public $allBookings;

    public function __construct(FunctionRoomBooking $booking, $allBookings = [])
    {
        $this->booking = $booking;
        $this->allBookings = $allBookings;
    }
    public function build()
    {
        $name = $this->booking->user->name ?? 'Resident';
        $rooms = collect($this->allBookings)
            ->pluck('functionRoom.function_room_name')
            ->unique()
            ->implode(', ');

        return $this->subject('Your Function Room Booking is Confirmed')
            ->view('emails.user-function-room-booking-confirmed')
            ->with([
                // 'name' => $this->booking->user->name,
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'function_rooms' => $rooms,
                'date' => $this->booking->function_room_booking_date,
                'start_time' => $this->booking->event_start_time,
                'end_time' => $this->booking->event_end_time,
            ]);
    }
}
