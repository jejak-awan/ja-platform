<?php

use Illuminate\Support\Facades\Route;
use Modules\Ai\Http\Controllers\AiController;

Route::prefix('v1')->group(function () {
    // Console Management
    Route::prefix('manage/ai')->middleware(['auth:sanctum'])->group(function () {
        Route::get('providers', [AiController::class, 'getProviders']);
        Route::post('generate', [AiController::class, 'generate']);
    });

    // Legacy Bridge
    Route::prefix('admin/core/ai')->middleware(['auth:sanctum'])->group(function () {
        Route::get('providers', [AiController::class, 'getProviders']);
        Route::post('generate', [AiController::class, 'generate']);
    });
});
