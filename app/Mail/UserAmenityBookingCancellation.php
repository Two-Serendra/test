<?php

namespace App\Mail;

use App\Models\ActivityBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserAmenityBookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ActivityBooking $booking;
    public bool $withPenalty;
    public int $penaltyAmount;

    public function __construct(ActivityBooking $booking, bool $withPenalty = false, int $penaltyAmount = 0)
    {
        $this->booking = $booking;
        $this->withPenalty = $withPenalty;
        $this->penaltyAmount = $penaltyAmount;
    }

    public function build()
    {
        $this->booking = ActivityBooking::with('activity', 'user')->find($this->booking->id);

        $name = $this->booking->name ?? optional($this->booking->user)->name ?? 'Resident';
        $activity_name = optional($this->booking->activity)->activity_name ?? 'N/A';

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Your Amenity Booking has been Cancelled')
            ->view('emails.user-amenity-booking-cancellation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'activity_name' => $activity_name,
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time,
                'withPenalty' => $this->withPenalty,
                'penaltyAmount' => $this->penaltyAmount,
            ]);
    }
}
