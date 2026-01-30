<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ActivityBooking;

class ConciergeAmenityBookingConfirmation extends Mailable implements ShouldQueue
{
    public ActivityBooking $booking;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(ActivityBooking $booking)
    {
        $this->booking = $booking;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $this->booking = ActivityBooking::with('activity')->find($this->booking->id);

        $name = $this->booking->name ?? 'Resident';
        $activity_name = optional($this->booking->activity)->activity_name;

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('New Amenity Booking')
            ->view('emails.concierge-amenity-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'activity_name' => $activity_name,
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time,
            ]);
    }
}
