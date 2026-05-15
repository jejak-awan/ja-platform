<?php

declare(strict_types=1);

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\System\Helpers\IpHelper;
use Modules\System\Services\HoneypotService;
use Modules\System\Services\SecurityMaintenanceService;
use Modules\Security\Services\SecurityService;
use Modules\System\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honeypot Middleware.
 * Intercepts requests to trap paths and blocks the requester immediately.
 */
class HoneypotMiddleware
{
    use MaintenanceBypass;
    protected HoneypotService $honeypot;

    protected SecurityService $security;

    protected SecurityMaintenanceService $maintenance;

    public function __construct(
        HoneypotService $honeypot,
        SecurityService $security,
        SecurityMaintenanceService $maintenance
    ) {
        $this->honeypot = $honeypot;
        $this->security = $security;
        $this->maintenance = $maintenance;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = IpHelper::getClientIp($request);
        
        // Bypass for OPTIONS requests (Preflight)
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // Fast-path: Reject if already blocked
        if ($this->security->isIpBlocked($ip)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $seconds = $this->security->getRemainingBlockTime($ip);
                $minutes = max(1, (int) ceil($seconds / 60));

                return response()->json([
                    'success' => false,
                    'message' => "Your IP address has been temporarily blocked. Please try again in {$minutes} minute(s).",
                    'retry_after' => $seconds,
                ], 429);
            }

            return response()->json(['message' => 'Access Denied'], 403);
        }

        // Skip during maintenance mode or for whitelisted maintenance routes
        if ($this->maintenance->isModulePaused('shield') || $this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        $path = $request->path();

        if ($this->honeypot->isTrap($path)) {
            $this->honeypot->handleHit($ip, $path, (string) $request->userAgent());

            return response()->json(['message' => 'Access Denied'], 403);
        }

        return $next($request);
    }
}
