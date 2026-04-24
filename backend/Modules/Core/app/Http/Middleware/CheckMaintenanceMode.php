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

        // 2. Check for scheduling (End Time) - Do this BEFORE bypass checks
        // so the system actually goes back online for everyone when time hits.
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

        // 3. Allow access to specific bypass routes (Infrastructure, Auth, APIs)
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
     * Check if the current user is an authorized admin.
     */
    protected function isAuthorizedAdmin(): bool
    {
        try {
            // Check both guards
            foreach (['sanctum', 'web'] as $guard) {
                $user = \Illuminate\Support\Facades\Auth::guard($guard)->user();
                if ($user instanceof \Modules\Core\Models\User) {
                    // Core Admin roles
                    if ($user->hasAnyRole(['admin', 'super-admin'])) {
                        return true;
                    }
                    
                    // School Admin roles (starts with admin- or is kepala-sekolah)
                    /** @var iterable<string> $roles */
                    $roles = $user->getRoleNames();
                    foreach ($roles as $role) {
                        if ($role === 'kepala-sekolah' || str_starts_with($role, 'admin-')) {
                            return true;
                        }
                    }
                }
            }
        } catch (\Exception $e) { }
        
        return false;
    }
}
