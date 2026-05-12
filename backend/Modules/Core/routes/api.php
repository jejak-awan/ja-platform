<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\ActivityLogController;
use Modules\Core\Http\Controllers\Api\AiController;
use Modules\Core\Http\Controllers\Api\AuthController;
use Modules\Core\Http\Controllers\Api\BackupController;
use Modules\Core\Http\Controllers\Api\CaptchaController;
use Modules\Core\Http\Controllers\Api\CspReportController;
use Modules\Core\Http\Controllers\Api\DashboardController;
use Modules\Core\Http\Controllers\Api\DependencyPackageController;
use Modules\Core\Http\Controllers\Api\DependencyVulnerabilityController;
use Modules\Core\Http\Controllers\Api\EmailTestController;
use Modules\Core\Http\Controllers\Api\FileManagerController;
use Modules\Core\Http\Controllers\Api\FrontendLogController;
use Modules\Core\Http\Controllers\Api\LanguageController;
use Modules\Core\Http\Controllers\Api\LogController;
use Modules\Core\Http\Controllers\Api\LoginHistoryController;
use Modules\Core\Http\Controllers\Api\NotificationController;
use Modules\Core\Http\Controllers\Api\PluginController;
use Modules\Core\Http\Controllers\Api\PublicSettingsController;
use Modules\Core\Http\Controllers\Api\RedisController;
use Modules\Core\Http\Controllers\Api\RoleController;
use Modules\Core\Http\Controllers\Api\ScheduledTaskController;
use Modules\Core\Http\Controllers\Api\SecurityController;
use Modules\Core\Http\Controllers\Api\SettingController;
use Modules\Core\Http\Controllers\Api\SlowQueryController;
use Modules\Core\Http\Controllers\Api\StorageMigrationController;
use Modules\Core\Http\Controllers\Api\SystemController;
use Modules\Core\Http\Controllers\Api\TranslationController;
use Modules\Core\Http\Controllers\Api\TwoFactorController;
use Modules\Core\Http\Controllers\Api\UserController;
use Modules\Core\Http\Controllers\Api\WebhookController;
use Modules\Core\Http\Controllers\Api\ModuleAccessController;

Route::prefix('v1')->group(function () {
    // Test connectivity
    Route::get('/test-connectivity', function() {
        return response()->json(['success' => true, 'message' => 'API is reachable']);
    });

    // Public Settings
    Route::middleware(['bypass_unit_scope:always'])->group(function () {
        Route::get('/public/settings', [PublicSettingsController::class, 'index']);
        Route::get('/public-settings', [PublicSettingsController::class, 'index']);
    });

    // Captcha
    Route::middleware(['bypass_unit_scope:always'])->group(function () {
        Route::get('/captcha/generate', [CaptchaController::class, 'generate']);
        Route::post('/captcha/verify', [CaptchaController::class, 'verify']);
        Route::get('/captcha/settings', [CaptchaController::class, 'settings']);
    });

    // System Public
    Route::post('/clear-rate-limit', [SystemController::class, 'clearRateLimit'])->middleware(['auth:sanctum', 'permission:manage settings', 'throttle:5,1']);

    // Security Public
    Route::post('/security/csp-report', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/crep-collect', [CspReportController::class, 'store'])->middleware('throttle:100,1');
    Route::post('/security/verify-connection', [SecurityController::class, 'verifyConnection'])->middleware('throttle:60,1');

    // Auth
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

    // Email Verification
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email', [AuthController::class, 'verifyEmailApi'])->middleware('throttle:5,1');
    Route::post('/resend-verification', [AuthController::class, 'resendVerificationEmailApi'])->middleware('throttle:3,1');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:sanctum');

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

    // User Profile
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/profile/avatar', [UserController::class, 'uploadAvatar']);
        Route::put('/profile/password', [UserController::class, 'updatePassword']);
        Route::get('/profile/access-journal', [UserController::class, 'loginHistory']);
        Route::get('/profile/preferences', [UserController::class, 'getPreferences']);
        Route::put('/profile/preferences', [UserController::class, 'updatePreferences']);

        // Dashboard
        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('permission:manage users|manage settings');
        Route::get('/dashboard/creator', [DashboardController::class, 'creator'])->middleware('permission:create content|edit content');
        Route::get('/dashboard/viewer', [DashboardController::class, 'viewer']);

        // Two-Factor
        Route::prefix('two-factor')->group(function () {
            Route::get('/status', [TwoFactorController::class, 'status']);
            Route::post('/generate', [TwoFactorController::class, 'generate']);
            Route::post('/verify', [TwoFactorController::class, 'verify']);
            Route::post('/disable', [TwoFactorController::class, 'disable']);
            Route::post('/regenerate-backup-codes', [TwoFactorController::class, 'regenerateBackupCodes']);
        });
    });

    Route::post('/two-factor/verify-code', [TwoFactorController::class, 'verifyCode'])->middleware('throttle:two-factor-verify');

    // Languages (Public)
    Route::prefix('ja')->group(function () {
        Route::get('/languages', [LanguageController::class, 'index']);
    });

    // Frontend Logging
    Route::post('journal/frontend', [FrontendLogController::class, 'store'])->middleware('throttle:600,1');

    // Admin Core - Unthrottled High-Frequency Polling
    Route::prefix('admin/core')->middleware(['auth:sanctum'])->group(function () {
        Route::get('system/cache-status', [SystemController::class, 'cacheStatus'])->middleware('permission:view settings');
        Route::post('system/cache/warm', [SystemController::class, 'warmCache'])->middleware('permission:manage settings');
        Route::get('redis/cache-stats', [RedisController::class, 'cacheStats'])->middleware('permission:manage settings');
    });

    // Admin Core
    Route::prefix('admin/core')->middleware(['auth:sanctum', 'throttle:admin', 'bypass_unit_scope'])->group(function () {
        // AI
        Route::get('ai/providers', [AiController::class, 'getProviders'])->middleware('permission:create content');
        Route::get('ai/models/{provider}', [AiController::class, 'getModels'])->middleware('permission:create content');
        Route::post('ai/test', [AiController::class, 'testConnection'])->middleware('permission:create content');
        Route::post('ai/generate', [AiController::class, 'generate'])->middleware('permission:create content');

        // Users
        Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->middleware('permission:manage users');
        Route::get('users/stats', [UserController::class, 'stats'])->middleware('permission:manage users');
        Route::apiResource('users', UserController::class)->middleware('permission:manage users');
        Route::post('users/{user}/force-logout', [UserController::class, 'forceLogout'])->middleware('permission:manage users');
        Route::post('users/{user}/verify', [UserController::class, 'verify'])->middleware('permission:manage users');
        Route::post('users/{user}/restore', [UserController::class, 'restore'])->middleware('permission:manage users');
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->middleware('permission:manage users');

        // Roles
        Route::post('roles/bulk-action', [RoleController::class, 'bulkAction'])->middleware('permission:manage users');
        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:manage users');
        Route::get('roles/permissions', [RoleController::class, 'permissions'])->middleware('permission:manage users');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:manage users');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:manage users');
        Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:manage users');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:manage users');
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->middleware('permission:manage users');
        Route::post('roles/{role}/duplicate', [RoleController::class, 'duplicate'])->middleware('permission:manage users');

        // Module access management (scoped RBAC)
        Route::prefix('module-access')->group(function () {
            Route::get('{module}/roles', [ModuleAccessController::class, 'roles'])->middleware('permission:manage cms access|manage school access');
            Route::get('{module}/users', [ModuleAccessController::class, 'users'])->middleware('permission:manage cms access|manage school access');
            Route::put('{module}/users/{user}/roles', [ModuleAccessController::class, 'updateUserRoles'])->middleware('permission:manage cms access|manage school access');
        });

        // Activity Logs (Activity Journal)
        Route::get('activity-journal', [ActivityLogController::class, 'index'])->middleware('permission:manage users');
            Route::post('activity-journal/clear', [ActivityLogController::class, 'clear'])->middleware(['permission:manage settings', 'throttle:admin-journal-clear']);
        Route::get('activity-journal/recent', [ActivityLogController::class, 'recent'])->middleware('permission:manage users');
        Route::get('activity-journal/statistics', [ActivityLogController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('activity-journal/export', [ActivityLogController::class, 'export'])->middleware('permission:manage users');
        Route::get('activity-journal/user/{userId}', [ActivityLogController::class, 'userActivity'])->middleware('permission:manage users');
        Route::get('activity-journal/{activityLog}', [ActivityLogController::class, 'show'])->middleware('permission:manage users');

        // Login History (Access Journal)
        Route::get('access-journal', [LoginHistoryController::class, 'index'])->middleware('permission:manage users');
        Route::get('access-journal/suspicious', [LoginHistoryController::class, 'suspicious'])->middleware('permission:manage users');
            Route::post('access-journal/clear', [LoginHistoryController::class, 'clear'])->middleware(['permission:manage settings', 'throttle:admin-journal-clear']);
        Route::get('access-journal/statistics', [LoginHistoryController::class, 'statistics'])->middleware('permission:manage users');
        Route::get('access-journal/export', [LoginHistoryController::class, 'export'])->middleware('permission:manage users');

        // Backups
        Route::get('backups/statistics', [BackupController::class, 'stats'])->middleware('permission:manage backups');
        Route::get('backups/stats', [BackupController::class, 'stats'])->middleware('permission:manage backups');
        Route::match(['GET', 'POST'], 'backups/schedule', [BackupController::class, 'schedule'])->middleware('permission:manage backups');
        Route::post('backups/cleanup', [BackupController::class, 'cleanup'])->middleware('permission:manage backups');
        Route::apiResource('backups', BackupController::class)->middleware('permission:manage backups');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->middleware('permission:manage backups');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->middleware('permission:manage backups');

        // Security Monitoring
        // Least-privilege security domains.
        Route::prefix('security')->middleware('throttle:120,1')->group(function () {
            Route::middleware('permission:manage security logs|manage security operations')->group(function () {
                Route::get('journal', [SecurityController::class, 'index']);
                Route::delete('journal', [SecurityController::class, 'clear']);
                Route::get('journal/{securityLog}', [SecurityController::class, 'show']);
                Route::get('stats', [SecurityController::class, 'stats']);
                Route::get('alerts', [SecurityController::class, 'alerts']);
                Route::get('health', [SecurityController::class, 'health']);
                Route::get('kpi', [SecurityController::class, 'kpi']);
                Route::post('test-notification', [SecurityController::class, 'testNotification']);
                Route::get('auto-tune/logs', [SecurityController::class, 'autoTuneLogs']);
                Route::get('csp-reports', [CspReportController::class, 'index']);
                Route::post('csp-reports/bulk-action', [CspReportController::class, 'bulkAction']);
                Route::get('csp-reports/statistics', [CspReportController::class, 'statistics']);
                Route::get('slow-queries', [SlowQueryController::class, 'index']);
                Route::get('slow-queries/statistics', [SlowQueryController::class, 'statistics']);
            });

            Route::middleware('permission:manage security ip-lists|manage security operations')->group(function () {
                Route::get('blocklist', [SecurityController::class, 'getBlocklist']);
                Route::post('block-ip', [SecurityController::class, 'blockIp']);
                Route::post('unblock-ip', [SecurityController::class, 'unblockIp']);
                Route::post('bulk-block', [SecurityController::class, 'bulkBlock'])->middleware('throttle:20,1');
                Route::post('bulk-unblock', [SecurityController::class, 'bulkUnblock'])->middleware('throttle:20,1');
                Route::get('whitelist', [SecurityController::class, 'getWhitelist']);
                Route::post('whitelist', [SecurityController::class, 'addToWhitelist']);
                Route::delete('whitelist', [SecurityController::class, 'removeFromWhitelist']);
                Route::post('remove-whitelist', [SecurityController::class, 'removeFromWhitelist']);
                Route::post('bulk-whitelist', [SecurityController::class, 'bulkWhitelist'])->middleware('throttle:20,1');
                Route::post('bulk-remove-whitelist', [SecurityController::class, 'bulkRemoveWhitelist'])->middleware('throttle:20,1');
            });

            Route::middleware('permission:manage security integrity|manage security operations')->group(function () {
                Route::get('shield/journal', [SecurityController::class, 'shieldJournal']);
                Route::post('shield/clear', [SecurityController::class, 'clearShieldLogs']);
                Route::get('shield/stats', [SecurityController::class, 'shieldStats']);
                Route::get('threat-analysis', [SecurityController::class, 'threatAnalysis']);
                Route::get('file-integrity', [SecurityController::class, 'fileIntegrityStatus']);
                Route::post('run-integrity-check', [SecurityController::class, 'runIntegrityCheck'])->middleware('throttle:30,1');
                Route::post('file-integrity/resync', [SecurityController::class, 'resyncFileIntegrity'])->middleware('throttle:60,1');
                Route::get('dependency-vulnerabilities', [DependencyVulnerabilityController::class, 'index']);
                Route::get('dependency-vulnerabilities/statistics', [DependencyVulnerabilityController::class, 'statistics']);
                Route::get('dependency-packages', [DependencyPackageController::class, 'index']);
                Route::get('dependency-packages/statistics', [DependencyPackageController::class, 'statistics']);
                Route::post('run-dependency-audit', [DependencyVulnerabilityController::class, 'runAudit']);
            });

            Route::middleware('permission:manage security maintenance|manage security operations')->group(function () {
                Route::get('maintenance', [SecurityController::class, 'maintenanceStatus']);
                Route::post('maintenance/activate', [SecurityController::class, 'maintenanceActivate'])->middleware('throttle:10,1');
                Route::post('maintenance/deactivate', [SecurityController::class, 'maintenanceDeactivate'])->middleware('throttle:10,1');
                Route::get('settings', [SecurityController::class, 'getSettings']);
                Route::put('settings', [SecurityController::class, 'updateSettings']);
            });
        });

        // Scheduled Tasks
        Route::get('scheduled-tasks/allowed-commands', [ScheduledTaskController::class, 'allowedCommands'])->middleware('permission:manage scheduled tasks');
        Route::post('scheduled-tasks/run-adhoc', [ScheduledTaskController::class, 'runAdhoc'])->middleware('permission:manage scheduled tasks');
        Route::post('scheduled-tasks/{id}/run', [ScheduledTaskController::class, 'run'])->middleware('permission:manage scheduled tasks');
        Route::apiResource('scheduled-tasks', ScheduledTaskController::class)->middleware('permission:manage scheduled tasks');



        // File Manager
        Route::get('file-manager', [FileManagerController::class, 'index'])->middleware('permission:manage files');
        Route::post('file-manager/upload', [FileManagerController::class, 'upload'])->middleware('permission:manage files');
        Route::post('file-manager/delete', [FileManagerController::class, 'delete'])->middleware('permission:manage files');
        Route::post('file-manager/folder', [FileManagerController::class, 'createFolder'])->middleware('permission:manage files');
        Route::post('file-manager/folder/delete', [FileManagerController::class, 'deleteFolder'])->middleware('permission:manage files');
        Route::post('file-manager/move', [FileManagerController::class, 'move'])->middleware('permission:manage files');
        Route::post('file-manager/rename', [FileManagerController::class, 'rename'])->middleware('permission:manage files');
        Route::get('file-manager/trash', [FileManagerController::class, 'trash'])->middleware('permission:manage files');
        Route::post('file-manager/trash/empty', [FileManagerController::class, 'emptyTrash'])->middleware('permission:manage files');
        Route::post('file-manager/restore', [FileManagerController::class, 'restore'])->middleware('permission:manage files');
        Route::post('file-manager/trash/permanent', [FileManagerController::class, 'deletePermanently'])->middleware('permission:manage files');

        // Logs (System Journal)
        Route::get('system-journal', [LogController::class, 'index'])->middleware('permission:manage settings');
        Route::delete('system-journal', [LogController::class, 'clear'])->middleware(['permission:manage settings', 'throttle:admin-journal-clear']);
        Route::post('system-journal/clear', [LogController::class, 'clear'])->middleware(['permission:manage settings', 'throttle:admin-journal-clear']);
        Route::get('system-journal/{filename}', [LogController::class, 'show'])->middleware('permission:manage settings');
        Route::get('system-journal/{filename}/download', [LogController::class, 'download'])->middleware('permission:manage settings');

        // System
        Route::get('system/info', [SystemController::class, 'info'])->middleware('permission:manage system');
        Route::get('system/health', [SystemController::class, 'health'])->middleware('permission:manage system');
        Route::get('system/health/detailed', [SystemController::class, 'systemHealth'])->middleware('permission:manage system');
        Route::get('system/statistics', [SystemController::class, 'statistics'])->middleware('permission:manage system');
        Route::post('system/cache/clear', [SystemController::class, 'clearCache'])->middleware('permission:manage settings');

        // Redis
        Route::get('redis/settings', [RedisController::class, 'index'])->middleware('permission:manage settings');
        Route::put('redis/settings', [RedisController::class, 'update'])->middleware('permission:manage settings');
        Route::match(['GET', 'POST'], 'redis/test-connection', [RedisController::class, 'testConnection'])->middleware('permission:manage settings');
        Route::post('redis/flush-cache', [RedisController::class, 'flushCache'])->middleware(['permission:manage settings', 'throttle:5,1']);
        Route::post('redis/warm-cache', [RedisController::class, 'warmCache'])->middleware(['permission:manage settings', 'throttle:10,1']);
        Route::get('redis/info', [RedisController::class, 'info'])->middleware('permission:manage settings');

        // Plugins
        Route::apiResource('plugins', PluginController::class)->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/activate', [PluginController::class, 'activate'])->middleware('permission:manage plugins');
        Route::post('plugins/{plugin}/deactivate', [PluginController::class, 'deactivate'])->middleware('permission:manage plugins');

        // Webhooks
        Route::get('webhooks/statistics', [WebhookController::class, 'statistics'])->middleware('permission:manage settings');
        Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->middleware('permission:manage settings');
        Route::apiResource('webhooks', WebhookController::class)->middleware('permission:manage settings');

        // Settings
        Route::get('settings/group/{group}', [SettingController::class, 'getGroup'])->middleware('permission:view settings');
        Route::post('settings/bulk-update', [SettingController::class, 'bulkUpdate'])->middleware('permission:manage settings');
        Route::apiResource('settings', SettingController::class)->middleware('permission:view settings');

        // Storage Migration
        Route::get('storage/migration/files', [StorageMigrationController::class, 'index'])->middleware('permission:manage settings');
        Route::post('storage/migration/batch', [StorageMigrationController::class, 'migrate'])->middleware('permission:manage settings');

        // Email Testing
        Route::prefix('email-test')->middleware('permission:manage settings')->group(function () {
            Route::post('test-connection', [EmailTestController::class, 'testConnection']);
            Route::post('send-test', [EmailTestController::class, 'sendTest']);
            Route::get('queue-status', [EmailTestController::class, 'getQueueStatus']);
            Route::get('recent-journal', [EmailTestController::class, 'getRecentLogs']);
        });

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::get('notifications/system', [NotificationController::class, 'indexSystem'])->middleware('permission:manage system');
        Route::post('notifications/system/revoke', [NotificationController::class, 'revokeSystem'])->middleware('permission:manage system');
        Route::post('notifications/system/bulk-revoke', [NotificationController::class, 'bulkRevokeSystem'])->middleware('permission:manage system');
        Route::post('notifications/broadcast', [NotificationController::class, 'broadcast'])->middleware('permission:manage system');
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

        // Multi-language
        Route::get('languages/ui-stats', [LanguageController::class, 'uiStats'])->middleware('permission:manage settings');
        Route::apiResource('languages', LanguageController::class)->middleware('permission:manage settings');

        // Entity Translations
        Route::get('translations', [TranslationController::class, 'getTranslations'])->middleware('permission:manage settings');
        Route::post('translations', [TranslationController::class, 'setTranslation'])->middleware('permission:manage settings');
    });
});
