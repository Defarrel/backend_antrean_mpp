<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;

    /**
     * Create a new event instance.
     */
    public function __construct($queue)
    {
        $this->queue = $queue;
    }

    /**
     * Tentukan channel broadcast.
     */
    public function broadcastOn()
    {
        return new Channel('queue-updates');
    }

    /**
     * Tentukan event name yang dikirim ke frontend.
     */
    public function broadcastAs()
    {
        return 'QueueUpdated';
    }
}
