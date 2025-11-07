<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue;
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
            'filter' => [
                'counter_id' => $counterId,
                'date' => $date,
            ],
            'data' => $queues
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'queue_number' => 'required|string|unique:queues',
            'counter_id' => 'required|exists:counters,id',
            'guest_name' => 'required|string|max:255',
            'status' => 'in:waiting,called,served,canceled',
            'called_at' => 'nullable|date',
            'served_at' => 'nullable|date',
            'canceled_at' => 'nullable|date',
        ]);

        $queue = Queue::create($validated);

        return response()->json([
            'message' => 'Queue created successfully.',
            'data' => $queue
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
            'data' => $queue
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

        return response()->json([
            'message' => 'Queue updated successfully.',
            'data' => $queue
        ], 200);
    }

    public function destroy($id)
    {
        $queue = Queue::find($id);

        if (!$queue) {
            return response()->json(['message' => 'Queue not found.'], 404);
        }

        $queue->delete();

        return response()->json(['message' => 'Queue deleted successfully.'], 200);
    }
}
