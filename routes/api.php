<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);       
        Route::post('/login', [AuthController::class, 'login']);             
        Route::post('/guest-login', [AuthController::class, 'guestLogin']);  

        Route::middleware('auth:api')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);               
            Route::post('/logout', [AuthController::class, 'logout']);      
        });
    });

    // Admin
    Route::middleware(['auth:api', 'role:admin'])->group(function () {
        Route::apiResource('counters', CounterController::class);            
        Route::apiResource('counter-details', CounterDetailController::class); 
        Route::get('counters/statistics', [CounterStatisticController::class, 'index']);
        Route::get('counters/{id}/statistics', [CounterStatisticController::class, 'show']);
    });

    // Customer Service
    Route::middleware(['auth:api', 'role:customer_service'])->group(function () {
        Route::apiResource('queues', QueueController::class);                
        Route::patch('queues/{id}/call', [QueueController::class, 'call']); 
        Route::patch('queues/{id}/serve', [QueueController::class, 'serve']); 
        Route::patch('queues/{id}/done', [QueueController::class, 'done']); 
        Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel']); 
        Route::post('queues/call-next', [QueueController::class, 'callNext']); 
        Route::get('counters/{id}/logs', [QueueLogController::class, 'indexByCounter']); 
    });

    // Guest
    Route::get('guest/counters', [CounterController::class, 'index']);       
    Route::get('guest/counters/{id}', [CounterController::class, 'show']);  
    Route::get('guest/queues', [QueueController::class, 'index']);          
});
