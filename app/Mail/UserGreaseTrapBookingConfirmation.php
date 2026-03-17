<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use App\Models\GreaseTrapBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserGreaseTrapBookingConfirmation extends Mailable implements ShouldQueue
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
        $name = $this->booking->user->name ?? 'Resident';
        $fee = 448;


        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('Grease Trap Booking has been Confirmed')
            ->view('emails.user-grease-trap-booking-confirmation')
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
