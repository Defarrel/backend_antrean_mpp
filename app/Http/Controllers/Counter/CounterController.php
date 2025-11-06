<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        $counters = Counter::orderBy('id', 'desc')->get();

        return response()->json([
            'message' => 'List of counters retrieved successfully.',
            'data' => $counters
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'counter_code' => 'required|string|unique:counters',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:0',
            'schedule_start' => 'nullable|date_format:H:i:s',
            'schedule_end' => 'nullable|date_format:H:i:s',
        ]);

        $counter = Counter::create($validated);

        return response()->json([
            'message' => 'Counter created successfully.',
            'data' => $counter
        ], 201);
    }

    public function show($id)
    {
        $counter = Counter::find($id);

        if (!$counter) {
            return response()->json(['message' => 'Counter not found.'], 404);
        }

        return response()->json([
            'message' => 'Counter retrieved successfully.',
            'data' => $counter
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $counter = Counter::find($id);

        if (!$counter) {
            return response()->json(['message' => 'Counter not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'sometimes|integer|min:0',
            'schedule_start' => 'nullable|date_format:H:i:s',
            'schedule_end' => 'nullable|date_format:H:i:s',
        ]);

        $counter->update($validated);

        return response()->json([
            'message' => 'Counter updated successfully.',
            'data' => $counter
        ], 200);
    }

    public function destroy($id)
    {
        $counter = Counter::find($id);

        if (!$counter) {
            return response()->json(['message' => 'Counter not found.'], 404);
        }

        $counter->delete();

        return response()->json(['message' => 'Counter deleted successfully.'], 200);
    }
}
