<?php

use Illuminate\Support\Facades\Route;
use Modules\Security\Http\Controllers\SecurityController;
use Modules\Security\Http\Controllers\CspReportController;

Route::prefix('v1')->group(function () {
    // Security Public (Infrastructure Layer)
    Route::post('/security/csp-report', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/crep-collect', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/verify-connection', [SecurityController::class, 'verifyConnection'])->middleware('throttle:60,1');

    // Console Management (Admin Layer)
    Route::prefix('manage/security')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::middleware('permission:manage security logs|manage security operations')->group(function () {
            Route::get('journal', [SecurityController::class, 'index']);
            Route::delete('journal', [SecurityController::class, 'clear']);
            Route::get('journal/{securityLog}', [SecurityController::class, 'show']);
            Route::get('stats', [SecurityController::class, 'stats']);
            Route::get('alerts', [SecurityController::class, 'alerts']);
            Route::get('health', [SecurityController::class, 'health']);
            Route::get('kpi', [SecurityController::class, 'kpi']);
            Route::post('test-notification', [SecurityController::class, 'testNotification']);
            Route::get('csp-reports', [CspReportController::class, 'index']);
            Route::post('csp-reports/bulk-action', [CspReportController::class, 'bulkAction']);
        });

        Route::middleware('permission:manage security ip-lists|manage security operations')->group(function () {
            Route::get('blocklist', [SecurityController::class, 'getBlocklist']);
            Route::post('block-ip', [SecurityController::class, 'blockIp']);
            Route::post('unblock-ip', [SecurityController::class, 'unblockIp']);
        });

        Route::middleware('permission:manage security integrity|manage security operations')->group(function () {
            Route::get('threat-analysis', [SecurityController::class, 'threatAnalysis']);
            Route::get('file-integrity', [SecurityController::class, 'fileIntegrityStatus']);
            Route::post('run-integrity-check', [SecurityController::class, 'runIntegrityCheck']);
        });
    });

    // Legacy Bridge for Frontend compatibility
    Route::prefix('admin/core/security')->middleware(['auth:sanctum'])->group(function () {
         Route::get('journal', [SecurityController::class, 'index']);
         Route::get('stats', [SecurityController::class, 'stats']);
    });
});
