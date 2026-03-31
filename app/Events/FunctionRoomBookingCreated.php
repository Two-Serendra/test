<?php

namespace App\Events;

use App\Models\FunctionRoomBooking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FunctionRoomBookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction_no;
    public $unit_no;
    public $function_room;

    /** 
     * Create a new event instance.
     */
    public function __construct(FunctionRoomBooking $booking)
    {
        $this->transaction_no = $booking->transaction_no;
        $this->unit_no = $booking->unit_no;
        $this->function_room = $booking->functionRoom->function_room_name ?? 'N/A'; 
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('function-room-bookings');
    }

    public function broadcastAs()
    {
        return 'FunctionRoomBookingCreated';
    }
}
