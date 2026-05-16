<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\LoginController;
use Modules\Member\Http\Controllers\RegisterController;
use Modules\Member\Http\Controllers\MemberController;

Route::prefix('v1')->group(function (): void {
    // Public Member API
    Route::prefix('public/member')->group(function (): void {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('register', [RegisterController::class, 'register']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [LoginController::class, 'logout']);
            Route::get('profile', [MemberController::class, 'profile']);
            Route::put('profile', [MemberController::class, 'updateProfile']);
        });
    });

    // Bridge for legacy v1/mbr
    Route::prefix('mbr')->group(function (): void {
        Route::post('login', [LoginController::class, 'login']);
        Route::post('register', [RegisterController::class, 'register']);
        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', [LoginController::class, 'logout']);
            Route::get('profile', [MemberController::class, 'profile']);
        });
    });
});
