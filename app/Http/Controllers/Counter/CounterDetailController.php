<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\CounterDetail;
use Illuminate\Http\Request;

class CounterDetailController extends Controller
{
    public function index()
    {
        return response()->json(CounterDetail::with('counter')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'counter_id' => 'required|exists:counters,id',
            'date' => 'required|date',
            'waiting_count' => 'nullable|integer',
            'called_count' => 'nullable|integer',
            'served_count' => 'nullable|integer',
            'canceled_count' => 'nullable|integer',
        ]);

        $detail = CounterDetail::create($data);
        return response()->json($detail, 201);
    }

    public function show(CounterDetail $counterDetail)
    {
        return response()->json($counterDetail->load('counter'));
    }

    public function update(Request $request, CounterDetail $counterDetail)
    {
        $data = $request->validate([
            'waiting_count' => 'nullable|integer',
            'called_count' => 'nullable|integer',
            'served_count' => 'nullable|integer',
            'canceled_count' => 'nullable|integer',
        ]);

        $counterDetail->update($data);
        return response()->json($counterDetail);
    }

    public function destroy(CounterDetail $counterDetail)
    {
        $counterDetail->delete();
        return response()->json(['message' => 'Counter detail deleted successfully']);
    }
}
