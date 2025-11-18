<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;

// auth routes (public)
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::post('guest-login', 'guestLogin');
});

// Auth routes (protected)
Route::middleware('auth:api')->controller(AuthController::class)->prefix('auth')->group(function () {
    Route::get('me', 'me');
    Route::post('logout', 'logout');
});

// Admin routes
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::apiResource('counters', CounterController::class);
    Route::apiResource('queues', QueueController::class);
    Route::get('queues/waiting', [QueueController::class, 'waitingList']);
    Route::apiResource('counter-details', CounterDetailController::class);
    Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
    Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);
    Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']);
    Route::get('/trashed', [CounterController::class, 'trashed']);          
    Route::post('/restore/{id}', [CounterController::class, 'restore']);    
    Route::delete('/force/{id}', [CounterController::class, 'forceDelete']); 
});

// Customer Service routes
Route::middleware(['auth:api', 'role:customer_service'])->group(function () {
    Route::get('counters', [CounterController::class, 'index']);
    Route::put('counters/{id}', [CounterController::class, 'update']);
    Route::patch('queues/{id}/call', [QueueController::class, 'call']);
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve']);
    Route::patch('queues/{id}/done', [QueueController::class, 'done']);
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']);
    Route::post('queues/call-next', [QueueController::class, 'callNext']);
});

// guest routes
Route::prefix('guest')->group(function () {
    Route::get('counters', [CounterController::class, 'index']);
    Route::post('queues', [QueueController::class, 'store']);
    Route::get('counters/{id}', [CounterController::class, 'show']);
    Route::get('queues', [QueueController::class, 'index']);
});
