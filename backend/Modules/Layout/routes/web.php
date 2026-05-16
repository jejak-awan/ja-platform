<?php

use Illuminate\Support\Facades\Route;
use Modules\Layout\Http\Controllers\LayoutController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('layouts', LayoutController::class)->names('layout');
});
