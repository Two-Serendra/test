<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\FunctionRoomBooking;

class FunctionRoomNewBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $roleId;

    public function __construct(FunctionRoomBooking $booking, $roleId)
    {
        $this->booking = $booking;
        $this->roleId = $roleId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('role.' . $this->roleId);
    }

    public function broadcastWith()
    {
        return [
            'transaction_no' => $this->booking->transaction_no,
            'unit_no' => $this->booking->unit_no,
            'booking_date' => $this->booking->booking_date,
            'booking_time_Slot' => $this->booking->booking_time_Slot,
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'transaction_no' => $this->booking->transaction_no,
            'unit_no' => $this->booking->unit_no,
            'booking_date' => $this->booking->booking_date,
            'booking_time_Slot' => $this->booking->booking_time_Slot,
        ];
    }

    public function broadcastAs()
    {
        return 'new-booking';
    }
}
