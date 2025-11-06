<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\CounterDetail;
use Illuminate\Http\Request;

class CounterDetailController extends Controller
{
    public function index()
    {
        $details = CounterDetail::with('counter')->orderBy('date', 'desc')->get();

        return response()->json([
            'message' => 'Counter details retrieved successfully.',
            'data' => $details
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'counter_id' => 'required|exists:counters,id',
            'date' => 'required|date',
            'total_queues' => 'integer|min:0',
            'served' => 'integer|min:0',
            'called' => 'integer|min:0',
            'canceled' => 'integer|min:0',
            'avg_duration' => 'numeric|min:0',
        ]);

        $detail = CounterDetail::create($validated);

        return response()->json([
            'message' => 'Counter detail created successfully.',
            'data' => $detail
        ], 201);
    }

    public function show($id)
    {
        $detail = CounterDetail::with('counter')->find($id);

        if (!$detail) {
            return response()->json(['message' => 'Counter detail not found.'], 404);
        }

        return response()->json([
            'message' => 'Counter detail retrieved successfully.',
            'data' => $detail
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $detail = CounterDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Counter detail not found.'], 404);
        }

        $validated = $request->validate([
            'total_queues' => 'integer|min:0',
            'served' => 'integer|min:0',
            'called' => 'integer|min:0',
            'canceled' => 'integer|min:0',
            'avg_duration' => 'numeric|min:0',
        ]);

        $detail->update($validated);

        return response()->json([
            'message' => 'Counter detail updated successfully.',
            'data' => $detail
        ], 200);
    }

    public function destroy($id)
    {
        $detail = CounterDetail::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Counter detail not found.'], 404);
        }

        $detail->delete();

        return response()->json(['message' => 'Counter detail deleted successfully.'], 200);
    }
}
