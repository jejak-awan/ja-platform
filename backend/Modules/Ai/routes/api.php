<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Ai\Http\Controllers\AiController;

Route::prefix('v1')->group(function (): void {
    // Console Management
    Route::prefix('manage/ai')->middleware(['auth:sanctum'])->group(function (): void {
        Route::get('providers', [AiController::class, 'getProviders']);
        Route::get('models/{provider}', [AiController::class, 'getModels']);
        Route::post('generate', [AiController::class, 'generate']);
    });
});
