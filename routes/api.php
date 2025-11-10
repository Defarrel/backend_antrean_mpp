<?php

use App\Http\Controllers\Queue\QueueLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Counter\CounterStatisticController;

Route::prefix('v1')->group(function () {
    // Resource routes
    Route::apiResource('counters', CounterController::class);
    Route::apiResource('counter-details', CounterDetailController::class);
    Route::apiResource('queues', QueueController::class);

    // Custom routes
    Route::patch('queues/{id}/call', [QueueController::class, 'call']);
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve']);
    Route::patch('queues/{id}/done', [QueueController::class, 'done']);
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']);

    // Statistik per counter atau seluruh counter
    Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
    Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);

    // Antian Otomatis  
    Route::post('queues/call-next', [QueueController::class, 'callNext']);

    // QueueLog
    Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']);
});
