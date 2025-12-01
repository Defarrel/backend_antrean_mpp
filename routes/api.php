<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;


// Auth Public
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('guest-login', 'guestLogin');
});


// Auth Private
Route::middleware('auth:api')->prefix('auth')->controller(AuthController::class)->group(function () {
    Route::get('me', 'me');
    Route::post('logout', 'logout');
});


// ADMIN
Route::middleware(['auth:api', 'role:admin'])->group(function () {

    // Counter soft delete & force delete
    Route::get('counters/trashed', [CounterController::class, 'trashed']);
    Route::post('counters/restore/{id}', [CounterController::class, 'restore']);
    Route::delete('counters/force/{id}', [CounterController::class, 'forceDelete']);

    // Create & Delete Counter
    Route::post('counters', [CounterController::class, 'store']);
    Route::delete('counters/{id}', [CounterController::class, 'destroy']);

    // Queue admin management
    Route::get('queues', [QueueController::class, 'index']);
    Route::delete('queues/{id}', [QueueController::class, 'destroy']);

    // Counter details CRUD
    Route::get('counter-details', [CounterDetailController::class, 'index']);
    Route::post('counter-details', [CounterDetailController::class, 'store']);
    Route::put('counter-details/{id}', [CounterDetailController::class, 'update']);
    Route::delete('counter-details/{id}', [CounterDetailController::class, 'destroy']);
});


// ADMIN & CUSTOMER SERVICE
Route::middleware(['auth:api', 'role:admin|customer_service'])->group(function () {

    // Counter main endpoints
    Route::get('counters', [CounterController::class, 'index']);
    Route::put('counters/{id}', [CounterController::class, 'update']);
    Route::get('counters/{id}', [CounterController::class, 'show']);

    // Counter Statistics (list & per counter)
    Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
    Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);

    // Queue logs per counter
    Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']);

    // Create queue
    Route::post('queues', [QueueController::class, 'store']);
});


// CUSTOMER SERVICE
Route::middleware(['auth:api', 'role:customer_service'])->group(function () {

    // Queue actions
    Route::patch('queues/{id}/call', [QueueController::class, 'call']);
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve']);
    Route::patch('queues/{id}/done', [QueueController::class, 'done']);
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']);

    // Call next
    Route::post('queues/{counterId}/call-next', [QueueController::class, 'callNext']);
});

// GUEST
Route::prefix('guest')->group(function () {
    Route::post('queues', [QueueController::class, 'store']);
    Route::get('counters', [CounterController::class, 'index']);
    Route::get('counters/{id}', [CounterController::class, 'show']);
    Route::get('queues', [QueueController::class, 'index']);
});
