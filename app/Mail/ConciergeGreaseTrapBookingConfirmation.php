<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GreaseTrapBooking;

class ConciergeGreaseTrapBookingConfirmation extends Mailable implements ShouldQueue
{
    public GreaseTrapBooking  $booking;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(GreaseTrapBooking $booking)
    {
        $this->booking = $booking;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $name = $this->booking->user->name ?? 'Resident';
        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('New Grease Trap Booking')
            ->view('emails.concierge-grease-trap-booking-cofirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,

            ]);
    }
}
