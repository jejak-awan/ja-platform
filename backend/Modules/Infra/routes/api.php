<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Infra\Http\Controllers\Api\InfraRedirectController;
use Modules\Infra\Http\Controllers\BackupController;
use Modules\Infra\Http\Controllers\FileManagerController;
use Modules\Infra\Http\Controllers\WebhookController;

Route::prefix('v1')->group(function (): void {
    // Console Management
    Route::prefix('manage/infra')->middleware(['auth:sanctum'])->group(function (): void {
        // Backups
        Route::get('backups/stats', [BackupController::class, 'stats']);
        Route::get('backups/statistics', [BackupController::class, 'stats']);
        Route::apiResource('backups', BackupController::class);

        // Webhooks
        Route::apiResource('webhooks', WebhookController::class);

        // File Manager
        Route::get('file-manager', [FileManagerController::class, 'index']);
        Route::post('file-manager/upload', [FileManagerController::class, 'upload']);

        // Redirects
        Route::apiResource('redirects', InfraRedirectController::class);
        Route::patch('redirects/{redirect}/toggle', [InfraRedirectController::class, 'toggle']);
    });

    // System Backups compatibility routes (maps api/v1/manage/system/backups to BackupController)
    Route::prefix('manage/system')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('backups/stats', [BackupController::class, 'stats'])->name('compat.api.backups.stats');
        Route::get('backups/statistics', [BackupController::class, 'stats'])->name('compat.api.backups.statistics');
        Route::apiResource('backups', BackupController::class)->names([
            'index' => 'compat.api.backups.index',
            'store' => 'compat.api.backups.store',
            'show' => 'compat.api.backups.show',
            'update' => 'compat.api.backups.update',
            'destroy' => 'compat.api.backups.destroy',
        ]);
    });

});
