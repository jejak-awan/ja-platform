<?php

namespace Modules\Analytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Analytics\Models\AnalyticsSession;
use Modules\Analytics\Models\AnalyticsVisit;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
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
                $session = AnalyticsSession::start($request, $sessionId);

                // Track visit
                AnalyticsVisit::trackVisit($request);

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
        return ! preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path);
    }
}
