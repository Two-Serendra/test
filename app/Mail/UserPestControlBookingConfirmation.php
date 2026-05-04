<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use App\Models\PestControlBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserPestControlBookingConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public PestControlBooking $booking;

    /**
     * Accept main booking + all linked bookings.
     */
    public function __construct(PestControlBooking $booking)
    {
        $this->booking = $booking;

    }

    /**
     * Build the message.
     */
    public function build()
    {
        $name = $this->booking->name ?? 'Resident';
        $fee = 350;

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Pest Control Booking has been Confirmed')
            ->view('emails.user-pest-control-booking-confirmation')
            ->with([
                'name' => $name,
                'transaction_no' => $this->booking->transaction_no,
                'unit_no' => $this->booking->unit_no,
                'booking_date' => $this->booking->booking_date,
                'booking_time_slot' => $this->booking->booking_time_slot,
                'charged_type' => $this->booking->charged_type,
                'fee' => $fee,

            ]);
    }
}
