<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Member\Http\Controllers\MemberController;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::resource('members', MemberController::class)->names('member');
});
