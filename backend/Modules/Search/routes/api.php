<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::prefix('v1')->group(function (): void {
    // Public API
    Route::prefix('public/search')->middleware(['throttle:search-public'])->group(function (): void {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
    });

    // Manage API
    Route::middleware(['auth:sanctum'])->prefix('manage/search')->group(function (): void {
        Route::get('queries', [SearchController::class, 'getQueries']);
        Route::post('reindex', [SearchController::class, 'reindex']);
        Route::get('stats', [SearchController::class, 'getStats']);
    });
});
