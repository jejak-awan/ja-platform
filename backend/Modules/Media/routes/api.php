<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\Api\MediaController;
use Modules\Media\Http\Controllers\Api\FolderController;

Route::prefix('v1/manage')->middleware(['auth:sanctum'])->group(function () {
    // Media Routes
    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::post('/upload', [MediaController::class, 'upload']);
        Route::post('/bulk', [MediaController::class, 'bulk']);
        Route::get('/{file}', [MediaController::class, 'show']);
        Route::put('/{file}', [MediaController::class, 'update']);
        Route::delete('/{file}', [MediaController::class, 'destroy']);
    });

    // Folder Routes
    Route::prefix('folders')->group(function () {
        Route::get('/', [FolderController::class, 'index']);
        Route::post('/', [FolderController::class, 'store']);
        Route::put('/{folder}', [FolderController::class, 'update']);
        Route::delete('/{folder}', [FolderController::class, 'destroy']);
    });
});
