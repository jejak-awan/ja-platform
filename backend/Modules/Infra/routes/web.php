<?php

use Illuminate\Support\Facades\Route;
use Modules\Infra\Http\Controllers\InfraController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('infras', InfraController::class)->names('infra');
});
