<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public array $payload;

    /**
     * Create a new event instance.
     */
    public function __construct($queue)
    {
        if ($queue instanceof Queue) {
            $this->payload = [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'status' => $queue->status,
                'counter_id' => $queue->counter_id,
                'updated_at' => $queue->updated_at,
            ];
        } elseif (is_object($queue) && isset($queue->deleted_id)) {
            $this->payload = [
                'deleted_id' => $queue->deleted_id,
            ];
        } else {
            $this->payload = (array) $queue;
        }
    }

    public function broadcastOn()
    {
        return new Channel('queues');
    }

    public function broadcastAs()
    {
        return 'QueueUpdated';
    }
}
