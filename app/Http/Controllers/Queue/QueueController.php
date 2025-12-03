<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue as QueueModel;
use App\Models\QueueLog as QueueLogModel;
use App\Models\Counter;
use App\Events\QueueUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
{
    private function clearCache()
    {
        Cache::forget('queues_all');
        Cache::forget('waiting_all');

        foreach (Counter::pluck('id') as $id) {
            Cache::forget("queues_all_{$id}");
            Cache::forget("waiting_{$id}");
        }
    }

    private function ensureAuthorizedForCounter($counterId)
    {
        $user = auth()->user();

        if ($user && $user->role && $user->role->name === 'customer_service') {
            if ($user->counter_id != $counterId) {
                abort(403, 'You are not allowed to access this counter.');
            }
        }
    }

    public function index(Request $request)
    {
        $counterId = $request->query('counter_id');
        $date = $request->query('date', now()->toDateString());

        $user = auth()->user();
        if ($user && $user->role && $user->role->name === 'customer_service') {
            $counterId = $user->counter_id;
        }

        $key = "queues_all_" . ($counterId ?: 'all');

        $queues = Cache::remember($key, 1, function () use ($counterId, $date) {
            $query = QueueModel::with('counter')->orderBy('id', 'desc');

            if ($counterId) {
                $query->where('counter_id', $counterId);
            }

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            return $query->get();
        });

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
        ]);

        $this->ensureAuthorizedForCounter($validated['counter_id']);

        $counter = Counter::findOrFail($validated['counter_id']);
        $code = strtoupper($counter->counter_code);

        $lastQueue = QueueModel::where('counter_id', $counter->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastQueue) {
            $parts = explode('-', $lastQueue->queue_number);
            $lastNumber = is_numeric(end($parts)) ? (int) end($parts) : 0;
            $nextNumber = $lastNumber + 1;
        }

        $today = now()->format('Ymd');
        $queueNumber = sprintf('%s-%s-%03d', $code, $today, $nextNumber);

        $queue = QueueModel::create([
            'queue_number' => $queueNumber,
            'counter_id' => $counter->id,
            'status' => 'waiting',
        ]);

        $this->logQueueStatus($queue, 'waiting');

        $this->clearCache();
        event(new QueueUpdated($queue));

        return response()->json([
            'message' => 'Queue created successfully.',
            'data' => $queue,
        ], 201);
    }

    public function call($id)
    {
        $queue = QueueModel::find($id);
        $this->ensureAuthorizedForCounter($queue->counter_id);

        $response = $this->updateQueueStatus($id, 'called', 'called_at');
        $this->clearCache();

        return $response;
    }

    public function serve($id)
    {
        $queue = QueueModel::find($id);
        $this->ensureAuthorizedForCounter($queue->counter_id);

        $response = $this->updateQueueStatus($id, 'served', 'served_at');
        $this->clearCache();

        return $response;
    }

    public function done($id)
    {
        $queue = QueueModel::find($id);
        $this->ensureAuthorizedForCounter($queue->counter_id);

        $response = $this->updateQueueStatus($id, 'done', 'done_at');

        $this->clearCache();

        return $response;
    }

    public function cancel($id)
    {
        $queue = QueueModel::find($id);
        $this->ensureAuthorizedForCounter($queue->counter_id);

        $response = $this->updateQueueStatus($id, 'canceled', 'canceled_at');
        $this->clearCache();

        return $response;
    }

    public function destroy($id)
    {
        $queue = QueueModel::find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $this->ensureAuthorizedForCounter($queue->counter_id);

        $queue->delete();

        event(new QueueUpdated((object) ['deleted_id' => $id]));
        $this->clearCache();

        return response()->json(['message' => 'Queue deleted successfully.'], 200);
    }

    private function logQueueStatus(QueueModel $queue, string $status)
    {
        $statusTime = $status === 'waiting'
            ? $queue->created_at
            : now();

        QueueLogModel::create([
            'queue_id' => $queue->id,
            'counter_id' => $queue->counter_id,
            'status' => $status,
            'status_time' => $statusTime,
        ]);
    }

    private function updateQueueStatus($id, $status, $timestampColumn)
    {
        $queue = QueueModel::find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $this->ensureAuthorizedForCounter($queue->counter_id);

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

    public function callNext($counterId)
    {
        $this->ensureAuthorizedForCounter($counterId);

        $nextQueue = QueueModel::where('counter_id', $counterId)
            ->where('status', 'waiting')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('id')
            ->first();

        if (!$nextQueue) {
            return response()->json(['message' => 'No waiting queue found.'], 404);
        }

        $nextQueue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        $this->logQueueStatus($nextQueue, 'called');
        event(new QueueUpdated($nextQueue));

        return response()->json([
            'message' => 'Next queue called successfully.',
            'data' => $nextQueue,
        ], 200);
    }

    public function waitingList(Request $request)
    {
        $counterId = $request->query('counter_id');
        $date = now()->toDateString();

        $user = auth()->user();
        if ($user && $user->role && $user->role->name === 'customer_service') {
            $counterId = $user->counter_id;
        }

        $key = "waiting_" . ($counterId ?: 'all');

        $waiting = Cache::remember($key, 1, function () use ($counterId, $date) {
            $query = QueueModel::with('counter')
                ->where('status', 'waiting')
                ->whereDate('created_at', $date)
                ->orderBy('id');

            if ($counterId) {
                $query->where('counter_id', $counterId);
            }

            return $query->get();
        });

        return response()->json([
            'message' => 'Waiting queues retrieved successfully.',
            'filters' => [
                'counter_id' => $counterId,
            ],
            'data' => $waiting,
        ], 200);
    }
}
