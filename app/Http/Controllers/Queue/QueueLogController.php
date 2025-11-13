<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\QueueLog;
use App\Models\Counter;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QueueLogController extends Controller
{
    public function indexByCounter($counterId, Request $request)
    {
        $counter = Counter::find($counterId);
        if (!$counter) {
            return response()->json([
                'message' => 'Counter not found.',
            ], 404);
        }

        $logs = QueueLog::with('queue')
            ->where('counter_id', $counterId)
            ->orderBy('queue_id')
            ->orderBy('status_time', 'asc')
            ->get();

        $processed = [];
        $previousTimeByQueue = [];

        foreach ($logs as $log) {
            $queueId = $log->queue_id;
            $currentTime = Carbon::parse($log->status_time);
            $duration = '-';

            if (isset($previousTimeByQueue[$queueId])) {
                $previousTime = Carbon::parse($previousTimeByQueue[$queueId]);
                $diffInSeconds = $previousTime->diffInSeconds($currentTime);
                $duration = $this->formatDuration($diffInSeconds);
            }

            $previousTimeByQueue[$queueId] = $currentTime;

            $processed[] = [
                'queue_number' => $log->queue->queue_number ?? '-',
                'status' => ucfirst($log->status),
                'status_time' => $currentTime->format('H:i:s'),
                'duration' => $duration,
            ];
        }
        

        return response()->json([
            'message' => 'Queue logs for counter retrieved successfully.',
            'counter_id' => $counter->id,
            'counter_name' => $counter->name,
            'data' => $processed,
        ], 200);
    }

    private function formatDuration($seconds)
    {
        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes > 0
            ? "{$minutes} menit " . ($remainingSeconds > 0 ? "{$remainingSeconds} detik" : '')
            : "{$remainingSeconds} detik";
    }
}
