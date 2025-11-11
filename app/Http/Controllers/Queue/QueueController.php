<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue as QueueModel;
use App\Models\QueueLog as QueueLogModel;
use App\Events\QueueUpdated;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $counterId = $request->query('counter_id');
        $date = $request->query('date');

        $query = QueueModel::with('counter')->orderBy('id', 'desc');

        if ($counterId) {
            $query->where('counter_id', $counterId);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $queues = $query->get();

        return response()->json([
            'message' => 'Queues retrieved successfully.',
            'filters' => [
                'counter_id' => $counterId,
                'date' => $date,
            ],
            'data' => $queues,
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
            'guest_name' => 'required|string|max:255',
        ]);

        $counter = \App\Models\Counter::findOrFail($validated['counter_id']);
        $code = strtoupper($counter->counter_code);

        $lastQueue = \App\Models\Queue::where('counter_id', $counter->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastQueue) {
            $parts = explode('-', $lastQueue->queue_number);
            $lastNumber = is_numeric(end($parts)) ? (int) end($parts) : 0;
            $nextNumber = $lastNumber + 1;
        }

        $queueNumber = sprintf('%s-%03d', $code, $nextNumber);

        $queue = \App\Models\Queue::create([
            'queue_number' => $queueNumber,
            'counter_id' => $counter->id,
            'guest_name' => $validated['guest_name'],
            'status' => 'waiting',
        ]);

        return response()->json([
            'message' => 'Queue created successfully.',
            'data' => $queue,
        ], 201);
    }

    public function call($id)
    {
        return $this->updateQueueStatus($id, 'called', 'called_at');
    }

    public function serve($id)
    {
        return $this->updateQueueStatus($id, 'served', 'served_at');
    }

    public function done($id)
    {
        $response = $this->updateQueueStatus($id, 'done', 'done_at');

        $queue = QueueModel::find($id);
        if ($queue) {
            $this->callNext($queue->counter_id);
        }

        return $response;
    }

    public function cancel($id)
    {
        return $this->updateQueueStatus($id, 'canceled', 'canceled_at');
    }

    public function destroy($id)
    {
        $queue = QueueModel::find($id);
        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $queue->delete();
        event(new QueueUpdated((object)['deleted_id' => $id]));

        return response()->json(['message' => 'Queue deleted successfully.'], 200);
    }

    private function logQueueStatus(QueueModel $queue, string $status)
    {
        QueueLogModel::create([
            'queue_id' => $queue->id,
            'counter_id' => $queue->counter_id,
            'status' => $status,
            'status_time' => now(),
        ]);
    }

    private function updateQueueStatus($id, $status, $timestampColumn)
    {
        $queue = QueueModel::find($id);
        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $fillableTimestamps = ['called_at', 'served_at', 'done_at', 'canceled_at'];
        if (!in_array($timestampColumn, $fillableTimestamps)) {
            return response()->json(['message' => 'Invalid timestamp column.'], 400);
        }

        $queue->update([
            'status' => $status,
            $timestampColumn => now(),
        ]);

        $this->logQueueStatus($queue, $status);
        event(new QueueUpdated($queue));

        return response()->json([
            'message' => "Queue status updated to {$status}.",
            'data' => $queue,
        ], 200);
    }

    private function callNext($counterId)
    {
        $nextQueue = QueueModel::where('counter_id', $counterId)
            ->where('status', 'waiting')
            ->orderBy('id')
            ->first();

        if ($nextQueue) {
            $nextQueue->update([
                'status' => 'called',
                'called_at' => now(),
            ]);

            $this->logQueueStatus($nextQueue, 'called');
            event(new QueueUpdated($nextQueue));
        }
    }
}
