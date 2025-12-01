<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CounterController extends Controller
{
    public function index()
    {

        $counters = Cache::remember('counters_list', 1, function () {
            return Counter::orderBy('id', 'desc')->get();
        });

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

        Cache::forget('counters_list');

        return response()->json([
            'message' => 'Counter created successfully.',
            'data' => $counter
        ], 201);
    }

    public function show($id)
    {
        $cacheKey = "counter_detail_{$id}";

        $counter = Cache::remember($cacheKey, 1, function () use ($id) {
            return Counter::find($id);
        });

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

        Cache::forget('counters_list');
        Cache::forget("counter_detail_{$id}");

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

        Cache::forget('counters_list');
        Cache::forget("counter_detail_{$id}");

        return response()->json(['message' => 'Counter deleted successfully.'], 200);
    }

    public function trashed()
    {
        $counters = Cache::remember('counters_trashed', 1, function () {
            return Counter::onlyTrashed()->orderBy('id', 'desc')->get();
        });

        return response()->json([
            'message' => 'List of soft-deleted counters retrieved successfully.',
            'data' => $counters
        ], 200);
    }

    public function restore($id)
    {
        $counter = Counter::onlyTrashed()->where('id', $id)->first();

        if (!$counter) {
            return response()->json(['message' => 'Counter not found or not deleted.'], 404);
        }

        $counter->restore();

        Cache::forget('counters_list');
        Cache::forget("counter_detail_{$id}");
        Cache::forget('counters_trashed');

        return response()->json([
            'message' => 'Counter restored successfully.',
            'data' => $counter
        ], 200);
    }

    public function forceDelete($id)
    {
        $counter = Counter::onlyTrashed()->where('id', $id)->first();

        if (!$counter) {
            return response()->json(['message' => 'Counter not found or not deleted.'], 404);
        }

        $counter->forceDelete();

        Cache::forget('counters_list');
        Cache::forget("counter_detail_{$id}");
        Cache::forget('counters_trashed');

        return response()->json(['message' => 'Counter permanently deleted successfully.'], 200);
    }
}
