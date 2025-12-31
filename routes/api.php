<?php

declare(strict_types=1);

use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::middleware('auth:sanctum')->get('/user', function (Illuminate\Http\Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/user/licenses', function (Illuminate\Http\Request $request) {
    return $request->user()->licenses()->get()->filter(function ($license) {
        return $license->isValid();
    })->values();
});

Route::middleware('auth:sanctum')->group(function () {
    // Thread stats (must be before resource route)
    Route::get('threads/stats', [ThreadController::class, 'stats']);
    
    // Threads
    Route::apiResource('threads', ThreadController::class);
    
    // Messages
    Route::post('threads/{thread}/messages', [MessageController::class, 'store']);
    Route::put('messages/{message}', [MessageController::class, 'update']);
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);
    Route::get('messages/{message}/revisions', [MessageController::class, 'revisions']);
    
    // Settings
    Route::get('settings', [\App\Http\Controllers\Api\SettingsController::class, 'index']);
    Route::put('settings', [\App\Http\Controllers\Api\SettingsController::class, 'update']);
    Route::put('settings/oauth-scopes', [\App\Http\Controllers\Api\SettingsController::class, 'updateOAuthScopes']);
    
    // Departments (staff only)
    Route::apiResource('departments', DepartmentController::class);
    
    // Customers (staff only)
    Route::get('customers', [CustomerController::class, 'index']);
    Route::put('customers/{customer}/status', [CustomerController::class, 'updateStatus']);
    
    // Reports (staff only)
    Route::get('reports', [ReportController::class, 'index']);
});
