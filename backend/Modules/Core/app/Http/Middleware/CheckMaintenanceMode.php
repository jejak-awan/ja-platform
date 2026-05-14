<?php

declare(strict_types=1);

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\Setting;
use Modules\Core\Traits\MaintenanceBypass;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    use MaintenanceBypass;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if maintenance mode is enabled
        $maintenanceEnabled = filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN);
        
        if (! $maintenanceEnabled) {
            return $next($request);
        }

        // 2. Check for scheduling (End Time)
        $endTimeRaw = Setting::get('maintenance_end_time');
        $endTime = is_string($endTimeRaw) ? $endTimeRaw : null;

        if ($endTime) {
            try {
                $endDateTime = \Illuminate\Support\Carbon::parse($endTime);
                if ($endDateTime->isPast()) {
                    Setting::set('maintenance_mode', false, 'boolean', 'general');
                    return $next($request);
                }
            } catch (\Exception $e) { }
        }

        // 3. Allow access to specific bypass routes
        if ($this->shouldBypassMaintenance($request)) {
            return $next($request);
        }

        // 4. User Bypass: If logged in as admin in ANY guard (Sanctum or Web)
        if ($this->isAuthorizedAdmin()) {
            return $next($request);
        }

        // 5. Return JSON response
        return response()->json([
            'success' => false,
            'message' => Setting::get('maintenance_message', 'Under Maintenance'),
            'maintenance' => true
        ], 503);
    }

    /**
     * Check if the current user is an authorized admin using role ranks.
     */
    protected function isAuthorizedAdmin(): bool
    {
        try {
            foreach (['sanctum', 'web'] as $guard) {
                $user = \Illuminate\Support\Facades\Auth::guard($guard)->user();
                if ($user instanceof \Modules\Core\Models\User) {
                    // Bypass maintenance if role rank is 90 or higher (System Admins)
                    if ($user->getRoleRank() >= 90) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) { }
        
        return false;
    }
}
