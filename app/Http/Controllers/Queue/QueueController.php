<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function index()
    {
        return response()->json(Queue::with('counter')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'queue_number' => 'required|string|unique:queues',
            'counter_id' => 'required|exists:counters,id',
            'guest_name' => 'required|string',
        ]);

        $queue = Queue::create($data);
        return response()->json($queue, 201);
    }

    public function show(Queue $queue)
    {
        return response()->json($queue->load('counter'));
    }

    public function update(Request $request, Queue $queue)
    {
        $data = $request->validate([
            'status' => 'nullable|in:waiting,called,served,canceled',
        ]);

        if (isset($data['status'])) {
            switch ($data['status']) {
                case 'called':
                    $data['called_at'] = now();
                    break;
                case 'served':
                    $data['served_at'] = now();
                    break;
                case 'canceled':
                    $data['canceled_at'] = now();
                    break;
            }
        }

        $queue->update($data);
        return response()->json($queue);
    }

    public function destroy(Queue $queue)
    {
        $queue->delete();
        return response()->json(['message' => 'Queue deleted successfully']);
    }
}
