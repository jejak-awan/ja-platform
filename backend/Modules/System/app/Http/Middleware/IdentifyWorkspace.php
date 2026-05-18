<?php

namespace Modules\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class IdentifyWorkspace
{
    public function handle(Request $request, Closure $next): mixed
    {
        // 0. Automatically bypass scope for Super Admin (checking Sanctum token / session user)
        try {
            $user = auth('sanctum')->user() ?? auth()->user();
            if ($user && is_callable([$user, 'hasRole']) && $user->hasRole('super')) {
                Context::add('bypass_unit_scope', true);
            }
        } catch (\Throwable) {
            // Ignore if auth check fails for any reason
        }

        if (Context::has('workspace_id')) {
            return $next($request);
        }

        // 1. Check X-Workspace-ID header
        $headerWorkspaceId = $request->header('X-Workspace-ID');
        if ($headerWorkspaceId !== null) {
            Context::add('workspace_id', (string) $headerWorkspaceId);
            if ($headerWorkspaceId === '0' || $headerWorkspaceId === '') {
                Context::add('bypass_unit_scope', true);
            }

            return $next($request);
        }

        // 2. Check session
        if ($request->hasSession() && $request->session()->has('active_workspace_id')) {
            $sessionVal = $request->session()->get('active_workspace_id');
            $workspaceId = is_scalar($sessionVal) ? (string) $sessionVal : '';
            Context::add('workspace_id', $workspaceId);

            if ($workspaceId === '0' || empty($workspaceId)) {
                Context::add('bypass_unit_scope', true);
            }

            return $next($request);
        }

        // 3. Delegate to registered resolvers (e.g., School module)
        if (app()->bound('workspace.resolver')) {
            $resolved = app('workspace.resolver')->resolve($request);
            if ($resolved !== null) {
                Context::add('workspace_id', (string) $resolved);
            }
        }

        return $next($request);
    }
}
