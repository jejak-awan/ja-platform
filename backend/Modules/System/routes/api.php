<?php

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\Console\DashboardController;
use Modules\System\Http\Controllers\Console\UserController;
use Modules\System\Http\Controllers\Console\AuthController;
use Modules\System\Http\Controllers\Console\SettingController;
use Modules\System\Http\Controllers\Console\NotificationController;

Route::prefix('v1')->group(function () {
    // Auth & Public
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

    // Console (Management Layer) - The New Standard
    Route::prefix('manage')->middleware(['auth:sanctum'])->group(function () {
        Route::get('dashboard/admin', [DashboardController::class, 'admin']);
        Route::get('dashboard/creator', [DashboardController::class, 'creator']);
        
        Route::apiResource('users', UserController::class);
        Route::apiResource('settings', SettingController::class);
        
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Legacy Bridge (Will be deprecated)
    Route::prefix('admin/core')->middleware(['auth:sanctum'])->group(function () {
        Route::get('dashboard/admin', [DashboardController::class, 'admin']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('settings', SettingController::class);
    });
});
