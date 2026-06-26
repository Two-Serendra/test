<?php

namespace App\Mail;

use App\Models\TestGreaseTrapBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestUserGreaseTrapBookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public TestGreaseTrapBooking $booking;

    /**
     * Accept the main booking.
     */
    public function __construct(TestGreaseTrapBooking $booking)
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
            ->view('emails.user-grease-trap-booking-cancellation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,
                'has_penalty' => $hasPenalty,
                'penalty_amount' => $penaltyAmount, // pass the amount
                'cancelled_within_24hrs' => $this->booking->cancelled_within_24hrs, // pass the 24-hour flag  
            ]);
    }
}