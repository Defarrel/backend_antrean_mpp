<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; 
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue;

    /**
     * Create a new event instance.
     */
    public function __construct($queue)
    {
        // Data antrean yang baru dibuat/diupdate dikirim ke socket
        $this->queue = $queue;
    }

    public function broadcastOn(): array
    {
        // Pastikan nama ini sama dengan yang didengarkan di frontend (useWebSocket)
        return [
            new Channel('queue-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'QueueUpdated';
    }
}