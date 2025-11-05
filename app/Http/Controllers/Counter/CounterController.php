<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        return response()->json(Counter::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'quota' => 'nullable|integer',
            'schedule_start' => 'nullable|date_format:H:i',
            'schedule_end' => 'nullable|date_format:H:i',
        ]);

        $counter = Counter::create($data);
        return response()->json($counter, 201);
    }

    public function show(Counter $counter)
    {
        return response()->json($counter->load('details'));
    }

    public function update(Request $request, Counter $counter)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'nullable|string',
            'quota' => 'nullable|integer',
            'schedule_start' => 'nullable|date_format:H:i',
            'schedule_end' => 'nullable|date_format:H:i',
        ]);

        $counter->update($data);
        return response()->json($counter);
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();
        return response()->json(['message' => 'Counter deleted successfully']);
    }
}
