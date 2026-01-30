<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class FinanceFunctionRoomBookingCancellation extends Mailable implements ShouldQueue
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

        return $this->subject('Function Room Booking Cancellation - ' . $this->booking->transaction_no)
            ->view('emails.finance-function-room-booking-cancellation')
            ->with([
                'booking' => $this->booking,
                'name' => $name,
            ]);
    }
}
