<?php

namespace App\Mail;

use App\Models\FitnessHubBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConciergeFitnessHubBookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public FitnessHubBooking $booking;
    public bool $withPenalty;
    public int $penaltyAmount;

    public function __construct(FitnessHubBooking $booking, bool $withPenalty = false, int $penaltyAmount = 0)
    {
        $this->booking = $booking;
        $this->withPenalty = $withPenalty;
        $this->penaltyAmount = $penaltyAmount;
    }

    public function build()
    {
        $this->booking = FitnessHubBooking::with('fitnessHub')->find($this->booking->id);

        $name = $this->booking->name ?? optional($this->booking->user)->name ?? 'Resident';
        $fitness_hub_name = optional($this->booking->fitnessHub)->fitness_hub_name ?? 'N/A';

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Fitness Hub Booking Cancelled')
            ->view('emails.concierge-fitness-hub-booking-cancellation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'fitness_hub_name' => $fitness_hub_name,
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time, 
                'withPenalty' => $this->withPenalty,
                'penaltyAmount' => $this->penaltyAmount,
            ]);
    }
}
