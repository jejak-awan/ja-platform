<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\System\Http\Controllers\Console\DashboardController;
use Modules\System\Http\Controllers\Console\UserController;
use Modules\System\Http\Controllers\Console\RoleController;
use Modules\System\Http\Controllers\Console\AuthController;
use Modules\System\Http\Controllers\Console\SettingController;
use Modules\System\Http\Controllers\Console\PublicSettingsController;
use Modules\System\Http\Controllers\Console\NotificationController;
use Modules\System\Http\Controllers\Console\PluginController;
use Modules\System\Http\Controllers\Console\LanguageController;
use Modules\System\Http\Controllers\Console\ActivityLogController;
use Modules\System\Http\Controllers\Console\ScheduledTaskController;
use Modules\System\Http\Controllers\Console\EmailTestController;
use Modules\System\Http\Controllers\Console\LogController;
use Modules\System\Http\Controllers\Console\TranslationController;
use Modules\System\Http\Controllers\Console\EmailTemplateController;
use Modules\System\Http\Controllers\Console\TwoFactorController;
use Modules\System\Http\Controllers\Console\SystemController;

Route::prefix('v1')->group(function (): void {
    // Auth & Public (Surface canonical)
    Route::prefix('public/system/auth')->group(function (): void {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    Route::get('public/system/settings', [PublicSettingsController::class, 'index']);
    Route::get('public/system/languages', [LanguageController::class, 'index']);

    // Dashboard routes
    Route::prefix('dashboard')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('admin', [DashboardController::class, 'admin']);
        Route::get('creator', [DashboardController::class, 'creator']);
        Route::get('viewer', [DashboardController::class, 'viewer']);
    });

    // Two Factor Authentication
    Route::prefix('two-factor')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('status', [TwoFactorController::class, 'status']);
        Route::post('generate', [TwoFactorController::class, 'generate']);
        Route::post('verify', [TwoFactorController::class, 'verify']);
        Route::post('disable', [TwoFactorController::class, 'disable']);
        Route::post('regenerate-backup-codes', [TwoFactorController::class, 'regenerateBackupCodes']);
        Route::post('verify-code', [TwoFactorController::class, 'verifyCode']);
    });

    // Manage API (Canonical)
    Route::prefix('manage/system')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'admin']);

        // System Info, Health, Statistics, and Cache
        Route::get('info', [SystemController::class, 'info']);
        Route::get('health', [SystemController::class, 'health']);
        Route::get('health/detailed', [SystemController::class, 'systemHealth']);
        Route::get('statistics', [SystemController::class, 'statistics']);
        Route::get('cache-status', [SystemController::class, 'cacheStatus']);
        Route::post('cache/clear', [SystemController::class, 'clearCache']);
        Route::post('cache/warm', [SystemController::class, 'warmCache']);
        Route::get('cache-warming-stats', [SystemController::class, 'cacheWarmingStats']);
        Route::get('system-health', [SystemController::class, 'systemHealth']);
        Route::post('clear-rate-limit', [SystemController::class, 'clearRateLimit']);

        // Profile Management
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::post('profile/avatar', [UserController::class, 'uploadAvatar']);
        Route::put('profile/password', [UserController::class, 'updatePassword']);
        Route::get('profile/preferences', [UserController::class, 'getPreferences']);
        Route::put('profile/preferences', [UserController::class, 'updatePreferences']);
        Route::get('profile/login-history', [UserController::class, 'loginHistory']);

        Route::get('users/stats', [UserController::class, 'stats']);
        Route::apiResource('users', UserController::class);
        Route::get('roles/permissions', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class);

        Route::get('settings/group/{group}', [SettingController::class, 'getGroup']);
        Route::post('settings/test-storage', [SettingController::class, 'testStorage']);
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate']);
        Route::apiResource('settings', SettingController::class);

        Route::apiResource('plugins', PluginController::class);
        Route::apiResource('languages', LanguageController::class);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

        Route::get('activity-journal', [ActivityLogController::class, 'index']);

        Route::get('translations', [TranslationController::class, 'getTranslations']);
        Route::post('translations', [TranslationController::class, 'setTranslation']);

        Route::get('scheduled-tasks/allowed-commands', [ScheduledTaskController::class, 'allowedCommands']);
        Route::post('scheduled-tasks/{id}/run', [ScheduledTaskController::class, 'run']);
        Route::apiResource('scheduled-tasks', ScheduledTaskController::class);

        Route::get('email-test/recent-journal', [EmailTestController::class, 'recentJournal']);
        Route::post('email-test/send', [EmailTestController::class, 'sendTestEmail']);

        Route::post('email-templates/{email_template}/preview', [EmailTemplateController::class, 'preview']);
        Route::post('email-templates/{email_template}/send-test', [EmailTemplateController::class, 'sendTest']);
        Route::apiResource('email-templates', EmailTemplateController::class);

        Route::get('logs', [LogController::class, 'index']);
        Route::get('logs/{filename}', [LogController::class, 'show']);
        Route::delete('logs/{filename}', [LogController::class, 'destroy']);
    });

    // System Journal routes for frontend compatibility (registered as api/v1/manage/system-journal)
    Route::prefix('manage/system-journal')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('', [LogController::class, 'index']);
        Route::get('{filename}', [LogController::class, 'show']);
        Route::get('{filename}/download', [LogController::class, 'download']);
        Route::post('clear', [LogController::class, 'clear']);
        Route::delete('{filename}', [LogController::class, 'destroy']);
    });

    // Redis Management routes
    Route::prefix('manage/redis')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('settings', [\Modules\System\Http\Controllers\Console\RedisController::class, 'index']);
        Route::put('settings', [\Modules\System\Http\Controllers\Console\RedisController::class, 'update']);
        Route::post('test-connection', [\Modules\System\Http\Controllers\Console\RedisController::class, 'testConnection']);
        Route::get('info', [\Modules\System\Http\Controllers\Console\RedisController::class, 'info']);
        Route::post('flush-cache', [\Modules\System\Http\Controllers\Console\RedisController::class, 'flushCache']);
        Route::post('warm-cache', [\Modules\System\Http\Controllers\Console\RedisController::class, 'warmCache']);
        Route::get('cache-stats', [\Modules\System\Http\Controllers\Console\RedisController::class, 'cacheStats']);
    });
});
