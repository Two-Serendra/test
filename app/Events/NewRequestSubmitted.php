<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRequestSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $unitNo;
    public $requestId;

    public function __construct($unitNo, $requestId)
    {
        $this->unitNo = $unitNo;
        $this->requestId = $requestId;
    }

    public function broadcastOn(): array
    {
        return ['my-channel'];  
    }

    public function broadcastAs()
    {
        return 'my-event';
    }

    public function broadcastWith()
    {
        return [
            'unitNo' => $this->unitNo,
            'requestId' => $this->requestId,
        ];
    }
}
