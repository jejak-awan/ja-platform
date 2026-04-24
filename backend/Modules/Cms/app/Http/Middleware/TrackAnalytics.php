<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only track GET requests and successful responses
        if ($request->method() === 'GET' && $response->getStatusCode() === 200) {
            // Skip tracking for admin/api routes
            if (! $this->shouldTrack($request)) {
                return;
            }

            try {
                $sessionId = session()->getId();

                // Start or get session (this always returns a session)
                $session = \Modules\Core\Models\AnalyticsSession::start($request, $sessionId);

                // Track visit
                \Modules\Core\Models\AnalyticsVisit::trackVisit($request);

                // Update session
                // Update session
                // Page views are now incremented inside trackVisit()

            } catch (\Exception $e) {
                // Log error but don't break anything
                \Log::error('Analytics tracking failed: '.$e->getMessage());
            }
        }
    }

    protected function shouldTrack(Request $request): bool
    {
        $path = $request->path();

        // Don't track admin routes
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'api')) {
            return false;
        }

        // Don't track static assets
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        return true;
    }
}
