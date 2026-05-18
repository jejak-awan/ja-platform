<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Security\Models\SecurityLog;
use Modules\System\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'JA-Platform Backend API is running',
    ]);
});

// Cloudflare RUM (Real User Monitoring) dummy route to prevent 404s on origin
Route::any('/cdn-cgi/{path?}', function () {
    Log::info('Cloudflare RUM hit: '.request()->path());

    return response()->noContent();
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| Probe Path Sinkhole
|--------------------------------------------------------------------------
|
| Common reconnaissance URLs should always look like plain 404s.
| We also rate-limit and security-log these hits to support incident response.
|
*/
$probePaths = [
    'admin',
    'admin/*',
    'dashboard',
    'dashboard/*',
    'panel',
    'panel/*',
    'wp-admin',
    'wp-admin/*',
    'wp-login.php',
    'phpmyadmin',
    'phpmyadmin/*',
    'pma',
    'cpanel',
    'administrator',
    'administrator/*',
    'manager',
    'manage',
];

Route::middleware('throttle:probe-paths')->any('/{path}', function (Request $request): Response {
    $path = (string) $request->route('path', '');
    $ip = (string) $request->ip();
    $cacheKey = 'security:probe:'.sha1($ip.'|'.$path);

    if (! Cache::has($cacheKey)) {
        SecurityLog::log(
            'path_probe_detected',
            null,
            $ip,
            'Common probe path requested',
            [
                'path' => $path,
                'method' => $request->method(),
                'host' => $request->getHost(),
            ]
        );
        Cache::put($cacheKey, true, now()->addMinutes(5));
    }

    Log::warning('Probe path sinkhole triggered', [
        'path' => $path,
        'ip' => $ip,
        'method' => $request->method(),
    ]);

    abort(404);
})->whereIn('path', $probePaths);

Route::fallback(function () {
    $path = request()->path();
    $segments = explode('/', $path);
    $firstSegment = $segments[0] ?? '';

    // Retrieve dynamic admin dashboard slug from settings
    $adminSlug = 'dash';
    try {
        $adminSlug = Setting::get('admin_dashboard_slug', 'dash');
    } catch (Throwable $e) {
        // Fallback
    }

    if ($firstSegment === $adminSlug || $firstSegment === 'auth') {
        if (file_exists(public_path('console.html'))) {
            return file_get_contents(public_path('console.html'));
        }
    }

    if (file_exists(public_path('index.html'))) {
        return file_get_contents(public_path('index.html'));
    }

    return response()->json(['message' => 'Resource not found'], 404);
});
