<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Counter\CounterController;
use App\Http\Controllers\Counter\CounterDetailController;
use App\Http\Controllers\Counter\CounterStatisticController;
use App\Http\Controllers\Queue\QueueController;
use App\Http\Controllers\Queue\QueueLogController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\UserRoleController;


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

    // --- USER MANAGEMENT ---
    Route::get('users/trashed', [UserManagementController::class, 'trashed']);
    Route::post('users/restore/{id}', [UserManagementController::class, 'restore']);
    Route::delete('users/force/{id}', [UserManagementController::class, 'forceDelete']);
    Route::put('users/{id}/role', [UserManagementController::class, 'updateRole']);
    Route::apiResource('users', UserManagementController::class)->except(['update', 'show']); 

    // --- ROLE MANAGEMENT ---
    Route::get('roles/trashed', [RoleController::class, 'trashed']);
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/restore/{id}', [RoleController::class, 'restore']);
    Route::delete('roles/force/{id}', [RoleController::class, 'forceDelete']);


    // --- PERMISSION MANAGEMENT ---
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);


    // --- COUNTER & QUEUE ADMIN ---
    
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