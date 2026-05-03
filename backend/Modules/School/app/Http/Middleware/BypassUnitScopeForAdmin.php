<?php

namespace Modules\School\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class BypassUnitScopeForAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $force = null): mixed
    {
        $user = $request->user();

        // If forced via middleware parameter (e.g. bypass_unit_scope:always) 
        // OR if user is authenticated admin/super
        if ($force === 'always' || ($user && ($user->hasRole('super') || $user->hasRole('admin')))) {
            Context::add('bypass_unit_scope', true);
        }

        return $next($request);
    }
}
