<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CounterStatisticController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date');
        $cacheKey = 'stats_all_' . ($date ?: 'all');

        $stats = Cache::remember($cacheKey, 3, function () use ($date) {

            $query = Queue::select(
                'counter_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'served' THEN 1 ELSE 0 END) as served"),
                DB::raw("SUM(CASE WHEN status = 'called' THEN 1 ELSE 0 END) as called"),
                DB::raw("SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled"),
                DB::raw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done"),
                DB::raw("AVG(EXTRACT(EPOCH FROM (COALESCE(served_at, now()) - COALESCE(called_at, now())))) as avg_duration_seconds")
            )
                ->groupBy('counter_id');

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $stats = $query->get();

            $counters = Counter::whereIn('id', $stats->pluck('counter_id'))->get()->keyBy('id');

            return $stats->map(function ($stat) use ($counters) {
                $counter = $counters[$stat->counter_id] ?? null;

                return [
                    'counter_id' => $stat->counter_id,
                    'counter_name' => $counter ? $counter->name : 'Unknown Counter',
                    'total' => (int) $stat->total,
                    'served' => (int) $stat->served,
                    'called' => (int) $stat->called,
                    'canceled' => (int) $stat->canceled,
                    'done' => (int) $stat->done,
                    'avg_duration_minutes' => $stat->avg_duration_seconds
                        ? round($stat->avg_duration_seconds / 60, 2)
                        : 0
                ];
            });
        });

        return response()->json([
            'message' => 'Queue statistics retrieved successfully.',
            'data' => $stats
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $date = $request->query('date');
        $cacheKey = "stat_counter_{$id}_" . ($date ?: 'all');

        $stat = Cache::remember($cacheKey, 3, function () use ($id, $date) {

            $counter = Counter::find($id);
            if (!$counter) {
                return null;
            }

            $query = Queue::where('counter_id', $id)
                ->select(
                    'counter_id',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN status = 'served' THEN 1 ELSE 0 END) as served"),
                    DB::raw("SUM(CASE WHEN status = 'called' THEN 1 ELSE 0 END) as called"),
                    DB::raw("SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled"),
                    DB::raw("SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done"),
                    DB::raw("AVG(EXTRACT(EPOCH FROM (served_at - called_at))) as avg_duration_seconds")
                )
                ->groupBy('counter_id');

            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $stat = $query->first();

            if (!$stat) {
                return [
                    'counter_id' => $counter->id,
                    'counter_name' => $counter->name,
                    'total' => 0,
                    'served' => 0,
                    'called' => 0,
                    'canceled' => 0,
                    'done' => 0,
                    'avg_duration_minutes' => 0
                ];
            }

            return [
                'counter_id' => $counter->id,
                'counter_name' => $counter->name,
                'total' => (int) $stat->total,
                'served' => (int) $stat->served,
                'called' => (int) $stat->called,
                'canceled' => (int) $stat->canceled,
                'done' => (int) $stat->done,
                'avg_duration_minutes' => $stat->avg_duration_seconds
                    ? round($stat->avg_duration_seconds / 60, 2)
                    : 0
            ];
        });

        if (!$stat) {
            return response()->json(['message' => 'Counter not found.'], 404);
        }

        return response()->json([
            'message' => 'Counter statistics retrieved successfully.',
            'data' => $stat
        ], 200);
    }
}
