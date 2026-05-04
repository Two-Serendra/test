<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use App\Models\GreaseTrapBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConciergeGreaseTrapBookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public GreaseTrapBooking $booking;

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
        $name = $this->booking->name ?? 'Resident';

        $hasPenalty = $this->booking->has_penalty; // true/false
        $penaltyAmount = $this->booking->penalty_amount ?? 0; // actual penalty amount


        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Grease Trap Booking has been Cancelled')
            ->view('emails.concierge-grease-trap-booking-cancellation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,
                'has_penalty' => $hasPenalty,
                'penalty_amount' => $penaltyAmount,

            ]);
    }
}
