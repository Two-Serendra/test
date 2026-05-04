<?php

namespace App\Mail;


use App\Models\FitnessHubBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserFitnessHubBookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public FitnessHubBooking $booking;

    public function __construct(FitnessHubBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $name = $this->booking->name ?? 'Resident';


        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Fitness Hub Booking has been Confirmed')
            ->view('emails.user-fitness-hub-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'fitness_hub_name' => $this->booking->fitness_hub_name ?? 'Fitness Hub',
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time,
            ]);
    }
}
