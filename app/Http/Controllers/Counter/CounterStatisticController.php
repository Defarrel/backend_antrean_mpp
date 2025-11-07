<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CounterStatisticController extends Controller
{
    /**
     * Ambil statistik antrean per loket
     */
    public function index(Request $request)
    {
        $date = $request->query('date'); 

        $query = Queue::select(
                'counter_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'served' THEN 1 ELSE 0 END) as served"),
                DB::raw("SUM(CASE WHEN status = 'called' THEN 1 ELSE 0 END) as called"),
                DB::raw("SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled"),
                DB::raw("AVG(EXTRACT(EPOCH FROM (served_at - called_at))) as avg_duration_seconds")
            )
            ->groupBy('counter_id');

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $stats = $query->get()->map(function ($stat) {
            $counter = Counter::find($stat->counter_id);
            return [
                'counter_id' => $stat->counter_id,
                'counter_name' => $counter ? $counter->name : 'Unknown Counter',
                'total' => (int) $stat->total,
                'served' => (int) $stat->served,
                'called' => (int) $stat->called,
                'canceled' => (int) $stat->canceled,
                'avg_duration_minutes' => $stat->avg_duration_seconds
                    ? round($stat->avg_duration_seconds / 60, 2)
                    : 0
            ];
        });

        return response()->json([
            'message' => 'Queue statistics retrieved successfully.',
            'data' => $stats
        ], 200);
    }
}
