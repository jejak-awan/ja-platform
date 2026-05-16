<?php

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

Route::prefix('v1')->group(function () {
    // Auth & Public (Surface canonical)
    Route::prefix('public/system/auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [AuthController::class, 'user'])->middleware('auth:sanctum');
    });

    Route::get('public/system/settings', [PublicSettingsController::class, 'index']);
    Route::get('public/system/languages', [LanguageController::class, 'index']);

    // Manage API (Canonical)
    Route::prefix('manage/system')->middleware(['auth:sanctum'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'admin']);
        
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        
        Route::get('settings/group/{group}', [SettingController::class, 'getGroup']);
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
});
