<?php

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

    // Statistik per counter atau seluruh counter
    Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
    Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);
});
