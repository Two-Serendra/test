<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
class UserAusiBookingBellNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        $statusMessage = match ($this->booking->booking_status) {
            1 => "Your ausi booking has been confirmed.",
            2 => "Your ausi booking has been cancelled.",
        };

        return [
            'notification_id' => $this->id, 
            'message' => $statusMessage,
            'booking_id' => $this->booking->id,
            'status' => $this->booking->booking_status,
        ];
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->booking->user_id);
    }

}
