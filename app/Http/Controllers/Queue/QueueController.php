<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Events\QueueUpdated;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $counterId = $request->query('counter_id');
        $date = $request->query('date');

        $query = Queue::with('counter')->orderBy('id', 'desc');

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
        $code = strtoupper($counter->counter_code); // Contoh: B-0001

        $lastQueue = \App\Models\Queue::where('counter_id', $counter->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;

        if ($lastQueue) {
            $parts = explode('-', $lastQueue->queue_number);
            $lastNum = (int) end($parts);
            $nextNumber = $lastNum + 1;
        }

        $queueNumber = sprintf('%s-%03d', $code, $nextNumber);

        $queue = \App\Models\Queue::create([
            'queue_number' => $queueNumber,
            'counter_id' => $counter->id,
            'guest_name' => $validated['guest_name'],
            'status' => 'waiting',
        ]);

        event(new \App\Events\QueueUpdated($queue));

        return response()->json([
            'message' => 'Queue created successfully.',
            'data' => $queue,
        ], 201);
    }



    public function show($id)
    {
        $queue = Queue::with('counter')->find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        return response()->json([
            'message' => 'Queue retrieved successfully.',
            'data' => $queue,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $queue = Queue::find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $validated = $request->validate([
            'status' => 'in:waiting,called,served,canceled',
            'called_at' => 'nullable|date',
            'served_at' => 'nullable|date',
            'canceled_at' => 'nullable|date',
        ]);

        $queue->update($validated);

        event(new QueueUpdated($queue));

        return response()->json([
            'message' => 'Queue updated successfully.',
            'data' => $queue,
        ], 200);
    }

    public function destroy($id)
    {
        $queue = Queue::find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $queue->delete();

        event(new QueueUpdated(['deleted_id' => $id]));

        return response()->json(['message' => 'Queue deleted successfully.'], 200);
    }
    
    public function callNext(Request $request)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        $queue = Queue::where('counter_id', $validated['counter_id'])
            ->where('status', 'waiting')
            ->orderBy('id')
            ->first();

        if (!$queue) {
            return response()->json(['message' => 'No waiting queue found.'], 404);
        }

        $queue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        event(new \App\Events\QueueUpdated($queue));

        return response()->json([
            'message' => 'Queue called successfully.',
            'data' => $queue,
        ], 200);
    }

}
