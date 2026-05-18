<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Layout\Http\Controllers\Api\MenuController;
use Modules\Layout\Http\Controllers\Api\ThemeController;
use Modules\Layout\Http\Controllers\Api\UrlRewriteController;
use Modules\Layout\Http\Controllers\Api\WidgetController;

Route::prefix('v1')->group(function (): void {
    // Public Layout API (Read only)
    Route::prefix('public/layout')->group(function (): void {
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::get('widgets/location/{location}', [WidgetController::class, 'getByLocation']);
        Route::get('themes/active', [ThemeController::class, 'getActive']);
    });

    // Console Management (Layout)
    Route::prefix('manage/layout')->middleware(['auth:sanctum', 'bypass_unit_scope'])->group(function (): void {
        // Menus
        Route::get('menus/location/{location}', [MenuController::class, 'getByLocation']);
        Route::post('menus/{menu}/items', [MenuController::class, 'addItem']);
        Route::post('menus/{menu}/reorder', [MenuController::class, 'reorderItems']);
        Route::apiResource('menus', MenuController::class);

        // Widgets
        Route::get('widgets/locations', [WidgetController::class, 'locations']);
        Route::post('widgets/reorder', [WidgetController::class, 'reorder']);
        Route::apiResource('widgets', WidgetController::class);

        // URL Rewrites
        Route::get('url-rewrites/statistics', [UrlRewriteController::class, 'statistics']);
        Route::apiResource('url-rewrites', UrlRewriteController::class);

        // Themes
        Route::get('themes/active', [ThemeController::class, 'getActive']);
        Route::get('themes/active/locations', [ThemeController::class, 'locations']);
        Route::get('themes/available', [ThemeController::class, 'available']);
        Route::post('themes/{theme}/activate', [ThemeController::class, 'activate']);
        Route::post('themes/scan', [ThemeController::class, 'scan']);
        Route::post('themes/install', [ThemeController::class, 'install']);

        // Theme Customization & Metadata API Endpoints
        Route::match(['put', 'patch'], 'themes/{theme}/customization', [ThemeController::class, 'updateCustomization']);
        Route::match(['put', 'patch'], 'themes/{theme}/settings', [ThemeController::class, 'updateSettings']);
        Route::match(['put', 'patch'], 'themes/{theme}/custom-css', [ThemeController::class, 'updateCustomCss']);
        Route::get('themes/{theme}/components', [ThemeController::class, 'getComponents']);
        Route::get('themes/{theme}/config', [ThemeController::class, 'getConfig']);
        Route::get('themes/{theme}/composables', [ThemeController::class, 'getComposables']);
        Route::post('themes/{theme}/validate', [ThemeController::class, 'validate']);

        Route::apiResource('themes', ThemeController::class);
    });
});
