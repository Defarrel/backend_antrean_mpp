<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Queue\QueueController;

Route::prefix('counters')->group(function () {
    Route::get('/', [CounterController::class, 'index']);
    Route::post('/', [CounterController::class, 'store']);
    Route::get('/{id}', [CounterController::class, 'show']);
    Route::put('/{id}', [CounterController::class, 'update']);
    Route::delete('/{id}', [CounterController::class, 'destroy']);
});

Route::prefix('counter-details')->group(function () {
    Route::get('/', [CounterDetailController::class, 'index']);
    Route::post('/', [CounterDetailController::class, 'store']);
    Route::get('/{id}', [CounterDetailController::class, 'show']);
    Route::put('/{id}', [CounterDetailController::class, 'update']);
    Route::delete('/{id}', [CounterDetailController::class, 'destroy']);
});

Route::prefix('queues')->group(function () {
    Route::get('/', [QueueController::class, 'index']);
    Route::post('/', [QueueController::class, 'store']);
    Route::get('/{id}', [QueueController::class, 'show']);
    Route::put('/{id}', [QueueController::class, 'update']);
    Route::delete('/{id}', [QueueController::class, 'destroy']);
});
