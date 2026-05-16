<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::prefix('v1')->group(function () {
    // Public API
    Route::prefix('public/search')->middleware(['throttle:search-public'])->group(function () {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
    });

    // Manage API
    Route::middleware(['auth:sanctum'])->prefix('manage/search')->group(function () {
        Route::get('queries', [SearchController::class, 'getQueries']);
        Route::post('reindex', [SearchController::class, 'reindex']);
        Route::get('stats', [SearchController::class, 'getStats']);
    });
});
