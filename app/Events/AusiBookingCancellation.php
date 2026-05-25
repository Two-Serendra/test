<?php

namespace App\Events;

use App\Models\AusiBooking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AusiBookingCancellation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction_no;
    public $unit_no;


    /**
     * Create a new event instance.
     */
    public function __construct(AusiBooking $booking)
    {
        $this->transaction_no = $booking->transaction_no;
        $this->unit_no = $booking->unit_no;

    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('ausi-bookings');
    }

    public function broadcastAs()
    {
        return 'AusiBookingCancellation';
    }
}
