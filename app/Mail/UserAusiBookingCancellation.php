<?php

namespace App\Mail;
use App\Models\AusiBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserAusiBookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
        $name = $this->booking->name ?? 'Resident';

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Ausi Booking has been Cancelled')
            ->view('emails.user-ausi-booking-cancellation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,
            ]);
    }
}
