<?php

use App\Http\Controllers\Queue\QueueLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Auth\AuthController;

Route::prefix('v1')->group(function () {
    // Resource routes
    Route::apiResource('counters', CounterController::class);
    Route::apiResource('counter-details', CounterDetailController::class);
    Route::apiResource('queues', QueueController::class);

<<<<<<< HEAD
    // Custom routes
    Route::patch('queues/{id}/call', [QueueController::class, 'call']);
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve']);
    Route::patch('queues/{id}/done', [QueueController::class, 'done']);
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']);
=======
    // Auth routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/guest-login', [AuthController::class, 'guestLogin']);
    });

    // Protected routes (auth:api)
    Route::middleware('auth:api')->prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
>>>>>>> Laravel_passport

    // Statistik per counter atau seluruh counter
    Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
    Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);

    // Antian Otomatis  
    Route::post('queues/call-next', [QueueController::class, 'callNext']);

    // QueueLog
    Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']);
});
