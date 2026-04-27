<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and add security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Content Security Policy
        $nonce = $this->generateNonce($request);
        Vite::useCspNonce($nonce);
        $configCsp = config('security.headers.csp');
        $csp = is_string($configCsp) ? $configCsp : $this->getContentSecurityPolicy($nonce);
        $reportOnlyRaw = env('CSP_REPORT_ONLY');
        $reportOnly = $reportOnlyRaw === null
            ? app()->environment('production')
            : filter_var((string) $reportOnlyRaw, FILTER_VALIDATE_BOOLEAN);
        if ($reportOnly) {
            $response->headers->set('Content-Security-Policy-Report-Only', $csp);
        } else {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Standard Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS: Force HTTPS for 1 year including subdomains
        // Only set when connection is HTTPS or APP_URL uses HTTPS (avoid breaking local dev)
        $appUrl = config('app.url', '');
        $appUrlStr = is_string($appUrl) ? $appUrl : '';
        if ($request->isSecure() || str_starts_with($appUrlStr, 'https')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Remove server signature
        $response->headers->remove('X-Powered-By');

        // Force remove upstream/conflicting legacy CSP headers
        // These are often added by Cloudflare or Nginx proxies and can be too strict ('none')
        $response->headers->remove('X-Content-Security-Policy');
        $response->headers->remove('X-WebKit-CSP');

        return $response;
    }

    /**
     * Get Content Security Policy directives
     */
    protected function getContentSecurityPolicy(string $nonce): string
    {
        $allowViteDevServer = $this->isLocalDevelopmentContext();
        $unsafeEvalConfig = config('app.csp_allow_unsafe_eval', app()->environment('local'));
        $allowUnsafeEval = filter_var(
            is_bool($unsafeEvalConfig) ? ($unsafeEvalConfig ? 'true' : 'false') : (is_string($unsafeEvalConfig) ? $unsafeEvalConfig : 'false'),
            FILTER_VALIDATE_BOOLEAN
        );

        $directives = [
            "default-src 'self'",
        ];

        // Script sources
        // Keep unsafe-inline for compatibility, but phase out unsafe-eval by default in production.
        $scriptSrc = [
            "'self'",
            "'nonce-{$nonce}'",
            "'unsafe-inline'",
            'https:',
            'data:',
        ];
        if ($allowUnsafeEval) {
            $scriptSrc[] = "'unsafe-eval'";
        }

        // Allow localhost only in local/dev for Vite HMR.
        if ($allowViteDevServer) {
            $scriptSrc[] = 'http://localhost:5173';
            $scriptSrc[] = 'ws://localhost:5173';
        }

        // Add external script sources
        $scriptSrc = array_merge($scriptSrc, [
            'https://cdn.jsdelivr.net',
            'https://code.jquery.com',
            'https://unpkg.com',
            'https://static.cloudflareinsights.com',
            'https://cloudflareinsights.com',
            'https://*.cloudflareinsights.com',
            'https://cdnjs.cloudflare.com',
        ]);

        // Add same-origin domains explicitly to prevent 'none' fallbacks
        $host = request()->getHost();
        if ($host) {
            $scriptSrc[] = "https://{$host}";
            $scriptSrc[] = "https://*.{$host}";
        }

        $directives[] = 'script-src '.implode(' ', array_unique($scriptSrc));

        // Style sources
        $styleSrc = ["'self'", "'nonce-{$nonce}'", "'unsafe-inline'"];

        if ($allowViteDevServer) {
            $styleSrc[] = 'http://localhost:5173';
        }

        $styleSrc = array_merge($styleSrc, [
            'https://cdn.jsdelivr.net',
            'https://fonts.googleapis.com',
            'https://fonts.bunny.net',
            'https://unpkg.com',
        ]);

        $directives[] = 'style-src '.implode(' ', array_unique($styleSrc));

        // Font sources
        $directives[] = "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://fonts.bunny.net data:";

        // Image sources
        $directives[] = "img-src 'self' data: https: blob:";

        $connectSrc = ["'self'", 'https:', 'wss:', 'ws:'];

        if ($allowViteDevServer) {
            $connectSrc[] = 'http://localhost:5173';
            $connectSrc[] = 'ws://localhost:5173';
            $connectSrc[] = 'wss://localhost:5173';
        }

        $connectSrc = array_merge($connectSrc, [
            'https://nominatim.openstreetmap.org',
            'https://www.emsifa.com',
            'https://static.cloudflareinsights.com',
            'https://cloudflareinsights.com',
            'https://*.cloudflareinsights.com',
            'https://cloudflare.com',
            'https://*.cloudflare.com',
        ]);

        if ($host) {
            $connectSrc[] = "https://{$host}";
            $connectSrc[] = "https://*.{$host}";
            $connectSrc[] = "wss://{$host}";
            $connectSrc[] = "ws://{$host}";
        }
        
        $portalUrl = config('app.url');
        if (is_string($portalUrl) && $portalUrl !== '') {
            $connectSrc[] = $portalUrl;
            
            $portalHost = parse_url($portalUrl, PHP_URL_HOST);
            if (is_string($portalHost) && $portalHost !== '') {
                $connectSrc[] = "https://{$portalHost}";
                $connectSrc[] = "https://*.{$portalHost}";
            }
        }
        
        $rootDomain = config('app.root_domain');
        if (is_string($rootDomain) && $rootDomain !== '') {
            $connectSrc[] = "https://{$rootDomain}";
        }

        $directives[] = 'connect-src '.implode(' ', array_unique($connectSrc));

        // Frame ancestors
        $directives[] = "frame-ancestors 'self'";

        // Base URI
        $directives[] = "base-uri 'self'";

        // Form action
        $directives[] = "form-action 'self'";

        // Worker sources (for service workers, PDF generation, etc.)
        $directives[] = "worker-src 'self' blob:";

        // Frame sources (for iframes - YouTube, Vimeo, Google Maps, etc.)
        $frameSrc = [
            "'self'",
            'https://www.youtube.com',
            'https://youtube.com',
            'https://www.youtube-nocookie.com',
            'https://player.vimeo.com',
            'https://vimeo.com',
            'https://www.google.com',
            'https://maps.google.com',
            'https://www.google.com/maps',
            'https://open.spotify.com',
            'https://w.soundcloud.com',
            'https://www.dailymotion.com',
            'https://codepen.io',
            'https://jsfiddle.net',
        ];

        if ($host) {
            $frameSrc[] = "https://{$host}";
        }

        $directives[] = 'frame-src '.implode(' ', $frameSrc);

        // CSP Violation Reporting
        $directives[] = 'report-uri /api/v1/security/crep-collect';

        return implode('; ', $directives);
    }

    /**
     * Generate or retrieve a nonce for the current request
     */
    protected function generateNonce(Request $request): string
    {
        return $request->cspNonce();
    }

    private function isLocalDevelopmentContext(): bool
    {
        if (app()->environment(['local', 'development', 'testing'])) {
            return true;
        }

        $appUrl = config('app.url', '');
        if (! is_string($appUrl) || $appUrl === '') {
            return false;
        }

        $host = parse_url($appUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return false;
        }

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
