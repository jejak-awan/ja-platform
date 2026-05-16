<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders()
    ->withMiddleware(function (Middleware $middleware): void {
        // Security Layer: Order matters (TrustProxies first, then Domain enforcement, then WAF, etc.)
        $middleware->prepend(\App\Http\Middleware\CheckIfInstalled::class);
        $middleware->prepend(\Modules\System\Http\Middleware\TrustProxies::class);

        // Enable Sanctum stateful API for SPA
        $middleware->statefulApi();

        // Never resolve a named login route here (the SPA handles auth screens).
        // This prevents RouteNotFoundException when guest/API requests do not send JSON headers.
        $middleware->redirectGuestsTo(fn (Request $request) => ($request->is('api/*') || $request->expectsJson()) ? null : '/');

        $middleware->web(prepend: [
            \Modules\Infra\Http\Middleware\HandleDomainRedirects::class,
            \Modules\System\Http\Middleware\IdentifyWorkspace::class,
            \Modules\Security\Http\Middleware\VerifyConnection::class,
            \Modules\Security\Http\Middleware\BlockMaliciousBots::class,
            \Modules\Security\Http\Middleware\WafMiddleware::class,
            \Modules\Security\Http\Middleware\HoneypotMiddleware::class,
        ], append: [
            \Modules\Layout\Http\Middleware\ApplyUrlRewrites::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Modules\Security\Http\Middleware\SecurityHeaders::class,
            \Modules\Analytics\Http\Middleware\TrackAnalytics::class,
            \Modules\System\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        $middleware->api(prepend: [
            \Modules\Infra\Http\Middleware\HandleDomainRedirects::class,
            \Modules\System\Http\Middleware\IdentifyWorkspace::class,
            \Modules\Security\Http\Middleware\VerifyConnection::class,
            \Modules\Security\Http\Middleware\BlockMaliciousBots::class,
            \Modules\Security\Http\Middleware\WafMiddleware::class,
            \Modules\Security\Http\Middleware\HoneypotMiddleware::class,
            \Modules\System\Http\Middleware\NormalizePaginationParams::class,
        ], append: [
            \Modules\Security\Http\Middleware\SecurityHeaders::class,
            \Modules\System\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        // Register permission middleware alias
        $middleware->alias([
            'permission' => \Modules\System\Http\Middleware\CheckPermission::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'bypass_unit_scope' => \Modules\System\Http\Middleware\BypassWorkspaceScope::class,
        ]);

        // Exempt analytics and security verification from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/v1/analytics/*',
            'api/v1/security/csp-report*',
            'api/v1/security/crep-collect*',
            'api/v1/security/verify-connection',
            'api/v1/journal/frontend',
            'api/v1/manage/*',
            'v1/security/csp-report*',
            'v1/security/crep-collect*',
            '*/security/crep-collect*',
            'cdn-cgi/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Advanced 429 response (Parity with ja-cms)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $headers = $e->getHeaders();
                $retryRaw = $headers['Retry-After'] ?? $headers['retry-after'] ?? null;
                if (is_array($retryRaw)) {
                    $retryAfter = (int) ($retryRaw[0] ?? 60);
                } elseif (is_numeric($retryRaw)) {
                    $retryAfter = (int) $retryRaw;
                } else {
                    $retryAfter = 60;
                }
                $retryAfter = max(1, $retryAfter);
                $minutes = max(1, (int) ceil($retryAfter / 60));

                $forward = ['Retry-After' => (string) $retryAfter];
                foreach (['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset'] as $h) {
                    $v = $headers[$h] ?? $headers[strtolower($h)] ?? null;
                    if (is_array($v) && isset($v[0])) {
                        $forward[$h] = (string) $v[0];
                    } elseif (is_scalar($v) && $v !== '') {
                        $forward[$h] = (string) $v;
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$minutes} minute".($minutes > 1 ? 's' : '').'.',
                    'retry_after' => $retryAfter,
                ], 429)->withHeaders($forward);
            }
        });

        // Consistent 401 response
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });
    })->create();
