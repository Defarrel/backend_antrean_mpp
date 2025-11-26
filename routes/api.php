<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;

// Auth public
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('guest-login', 'guestLogin');
});

// Auth Protected
Route::middleware('auth:api')->controller(AuthController::class)->prefix('auth')->group(function () {
    Route::get('me', 'me');
    Route::post('logout', 'logout');
});

// Mix: ADMIN + CUSTOMER SERVICE
Route::middleware(['auth:api', 'role:admin|customer_service'])
    ->group(function () {

        // UPDATE counter
        Route::put('counters/{id}', [CounterController::class, 'update']);

        // CREATE queue
        Route::post('queues', [QueueController::class, 'store']);

        // Counter details
        Route::get('counters/{id}', [CounterController::class, 'show']);
        Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
        Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']);
        Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);
    });

// Mix: ADMIN + CUSTOMER SERVICE
Route::middleware(['auth:api', 'role:admin|customer_service'])
    ->group(function () {
        Route::get('counters', [CounterController::class, 'index']);
    });


// ADMIN
Route::middleware(['auth:api', 'role:admin'])->group(function () {

    // Counter extras
    Route::get('counters/trashed', [CounterController::class, 'trashed']);
    Route::post('counters/restore/{id}', [CounterController::class, 'restore']);
    Route::delete('counters/force/{id}', [CounterController::class, 'forceDelete']);

    // Counter CRUD manual
    Route::post('counters', [CounterController::class, 'store']);
    Route::delete('counters/{id}', [CounterController::class, 'destroy']);

    // Queue admin CRUD 
    Route::get('queues', [QueueController::class, 'index']);
    Route::get('queues/{id}', [QueueController::class, 'index']);
    Route::delete('queues/{id}', [QueueController::class, 'destroy']);

    // Counter details CRUD
    Route::get('counter-details', [CounterDetailController::class, 'index']);
    Route::post('counter-details', [CounterDetailController::class, 'store']);
    Route::put('counter-details/{id}', [CounterDetailController::class, 'update']);
    Route::delete('counter-details/{id}', [CounterDetailController::class, 'destroy']);
});

// CS
Route::middleware(['auth:api', 'role:customer_service'])->group(function () {

    Route::get('counters', [CounterController::class, 'index']);
    Route::put('counters/{id}', [CounterController::class, 'update']);

    Route::patch('queues/{id}/call', [QueueController::class, 'call']);
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve']);
    Route::patch('queues/{id}/done', [QueueController::class, 'done']);
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']);

    Route::post('queues/{counterId}/call-next', [QueueController::class, 'callNext']);
});

// GUEST
Route::prefix('guest')->group(function () {
    Route::post('queues', [QueueController::class, 'store']);
    Route::get('counters', [CounterController::class, 'index']);
    Route::get('counters/{id}', [CounterController::class, 'show']);
    Route::get('queues', [QueueController::class, 'index']);
});
