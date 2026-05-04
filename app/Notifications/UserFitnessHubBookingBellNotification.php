<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;
class UserFitnessHubBookingBellNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast']; // Database + Pusher broadcast
    }

    public function toArray($notifiable)
    {
        $statusMessage = match ($this->booking->booking_status) {
            1 => "Your FitnessHub booking has been confirmed.",
            2 => "Your FitnessHub booking has been cancelled.",
            3 => "Your booking for FitnessHub has been cancelled. ₱1000 penalty will be applied.",
            default => "Booking status updated.",
        };

        return [
            'notification_id' => $this->id, // 👈 VERY IMPORTANT
            'message' => $statusMessage,
            'booking_id' => $this->booking->id,
            'status' => $this->booking->booking_status,
            'type' => 'fitness_hub',
        ]; 
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->booking->user_id);
    }

}
