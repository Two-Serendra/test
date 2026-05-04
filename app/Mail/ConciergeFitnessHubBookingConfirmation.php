<?php

namespace App\Mail;

use App\Models\FitnessHubBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ConciergeFitnessHubBookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public FitnessHubBooking $booking;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(FitnessHubBooking $booking)
    {
        $this->booking = $booking;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $this->booking = FitnessHubBooking::with('fitnessHub')->find($this->booking->id);

        $name = $this->booking->name ?? 'Resident';
        $fitness_hub_name = optional($this->booking->fitnessHub)->fitness_hub_name;

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('New Fitness Hub Booking')
            ->view('emails.concierge-fitness-hub-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'fitness_hub_name' => $fitness_hub_name,
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time,
            ]);
    }
}
