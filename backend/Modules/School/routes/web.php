<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond to
| and give it the Closure or controller method. env('APP_URL')
|
*/

Route::get('/school', function (): Factory|View {
    /** @var view-string $view */
    $view = 'school::index';

    return view($view);
});
