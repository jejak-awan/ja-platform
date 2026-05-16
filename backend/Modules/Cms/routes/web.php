<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\CmsController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('cms', CmsController::class)->names('cms');
});
