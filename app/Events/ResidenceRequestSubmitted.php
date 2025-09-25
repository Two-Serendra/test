<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResidenceRequestSubmitted implements ShouldBroadcast

{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ResidenceRequest;

    /**
     * Create a new event instance.
     */
    public function __construct($ResidenceRequest)
    {
        $this->ResidenceRequest = $ResidenceRequest;
    }

    public function broadcastOn()
    {
        return new Channel('residence-requests');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->ResidenceRequest->id,
            'name' => $this->ResidenceRequest->residence_type,
            'email' => $this->ResidenceRequest->section,
            'unit_no' => $this->ResidenceRequest->unit_no,

            'created_at' => $this->ResidenceRequest->created_at->toDateTimeString(),
        ];
    }

     public function broadcastAs()
    {
        return 'ResidenceRequestSubmitted';
    }
}
