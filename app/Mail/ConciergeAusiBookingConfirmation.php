<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AusiBooking;

class ConciergeAusiBookingConfirmation extends Mailable implements ShouldQueue
{
    public AusiBooking $booking;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(AusiBooking $booking)
    {
        $this->booking = $booking;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $name = $this->booking->user->name ?? 'Resident';
        $fee = 350; 

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('New AUSI Booking Received')
            ->view('emails.concierge-ausi-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,
            ]);
    }
}
