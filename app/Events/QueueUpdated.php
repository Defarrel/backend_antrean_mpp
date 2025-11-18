<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueueUpdated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct($queue)
    {
        if ($queue instanceof Queue) {
            $this->payload = [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'counter_id' => $queue->counter_id,
                'status' => $queue->status,
                'created_at' => $queue->created_at,
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

    public function broadcastWith(): array
    {
        return [
            'event' => 'queue_updated',
            'queue' => $this->payload
        ];
    }
}
