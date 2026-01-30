<?php

namespace App\Mail;

use App\Models\FunctionRoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class FinanceFunctionRoomBookingNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $mainBooking;
    public $allBookings;

    public function __construct(FunctionRoomBooking $mainBooking, $allBookings = [])
    {
        $this->mainBooking = $mainBooking;
        $this->allBookings = $allBookings;
    }

    public function build()
    {
        $rooms = collect($this->allBookings)
            ->pluck('functionRoom.function_room_name')
            ->unique()
            ->implode(', ');

        $totalAmount = collect($this->allBookings)
            ->sum('total_amount');

        return $this->from('lowriseadmin@twoserendra.com', 'Two Serendra')
            ->subject('New Function Room Booking (Finance Copy)')
            ->view('emails.finance-function-room-booking')
            ->with([
                'booking' => $this->mainBooking,
                'rooms' => $rooms,
                'totalAmount' => $totalAmount,
            ]);
    }
}
