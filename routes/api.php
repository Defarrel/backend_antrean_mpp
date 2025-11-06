<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Queue\QueueController;

Route::prefix('v1')->group(function () {
    Route::apiResource('counters', CounterController::class);
    Route::apiResource('counter-details', CounterDetailController::class);
    Route::apiResource('queues', QueueController::class);

    // Endpoint tambahan khusus statistik
    Route::get('counters/{id}/statistics', [CounterDetailController::class, 'getStatistics']);
});
