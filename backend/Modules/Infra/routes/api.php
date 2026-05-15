<?php

use Illuminate\Support\Facades\Route;
use Modules\Infra\Http\Controllers\BackupController;
use Modules\Infra\Http\Controllers\WebhookController;
use Modules\Infra\Http\Controllers\FileManagerController;

Route::prefix('v1')->group(function () {
    // Console Management
    Route::prefix('manage')->middleware(['auth:sanctum'])->group(function () {
        // Backups
        Route::apiResource('backups', BackupController::class);
        Route::get('backups/stats', [BackupController::class, 'stats']);
        
        // Webhooks
        Route::apiResource('webhooks', WebhookController::class);
        
        // File Manager
        Route::get('file-manager', [FileManagerController::class, 'index']);
        Route::post('file-manager/upload', [FileManagerController::class, 'upload']);
    });

    // Legacy Bridge
    Route::prefix('admin/core')->middleware(['auth:sanctum'])->group(function () {
        Route::apiResource('backups', BackupController::class);
        Route::apiResource('webhooks', WebhookController::class);
        Route::get('file-manager', [FileManagerController::class, 'index']);
    });
});
