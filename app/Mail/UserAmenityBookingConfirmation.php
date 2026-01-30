<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use App\Models\ActivityBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserAmenityBookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ActivityBooking $booking;

    public function __construct(ActivityBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $name = $this->booking->name ?? 'Resident';


        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Your Amenity Booking has been Confirmed')
            ->view('emails.user-amenity-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'booking_type' => $this->booking->booking_type,
                'activity_name' => $this->booking->activity->activity_name,
                'unit_no' => $this->booking->unit,
                'booking_date' => $this->booking->booking_date,
                'booking_start_time' => $this->booking->booking_start_time,
                'booking_end_time' => $this->booking->booking_end_time,
            ]);
    }
}
