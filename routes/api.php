<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;

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
    Route::get('users', [UserManagementController::class, 'index']);
    Route::post('users', [UserManagementController::class, 'store']);
    Route::put('users/{id}/role', [UserManagementController::class, 'updateRole']);
    Route::delete('users/{id}', [UserManagementController::class, 'destroy']);
    Route::post('users/{id}/restore', [UserManagementController::class, 'restore']);
    Route::apiResource('roles', RoleController::class);
    Route::patch('roles/{id}/restore', [RoleController::class, 'restore']);
    Route::apiResource('permissions', PermissionController::class)->only(['index','store','destroy']);
});

// Customer Service routes
Route::middleware(['auth:api', 'role:customer_service'])->group(function () {
    Route::get('counters', [CounterController::class, 'index'])->middleware('permission:view_counters');
    Route::get('counters/{id}', [CounterController::class, 'show'])->middleware('permission:view_counters');
    Route::put('counters/{id}', [CounterController::class, 'update'])->middleware('permission:edit_counters');
    Route::patch('queues/{id}/call', [QueueController::class, 'call'])->middleware('permission:call_queue');
    Route::patch('queues/{id}/serve', [QueueController::class, 'serve'])->middleware('permission:serve_queue');
    Route::patch('queues/{id}/done', [QueueController::class, 'done'])->middleware('permission:manage_queues');
    Route::patch('queues/{id}/cancel', [QueueController::class, 'cancel'])->middleware('permission:manage_queues');
    Route::post('queues/call-next', [QueueController::class, 'callNext'])->middleware('permission:call_queue');
    Route::get('queues', [QueueController::class, 'index'])->middleware('permission:manage_queues');
});

// guest routes
Route::prefix('guest')->group(function () {
    Route::get('counters', [CounterController::class, 'index']);
    Route::post('queues', [QueueController::class, 'store']);
    Route::get('counters/{id}', [CounterController::class, 'show']);
    Route::get('queues', [QueueController::class, 'index']);
});
