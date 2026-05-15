<?php

namespace Modules\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class IdentifyWorkspace
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Context::has('workspace_id')) {
            return $next($request);
        }

        // 1. Check X-Workspace-ID header
        $headerWorkspaceId = $request->header('X-Workspace-ID');
        if ($headerWorkspaceId !== null) {
            Context::add('workspace_id', (int) $headerWorkspaceId);
            return $next($request);
        }

        // 2. Check session
        if ($request->hasSession() && $request->session()->has('active_workspace_id')) {
            $workspaceId = (int) $request->session()->get('active_workspace_id');
            Context::add('workspace_id', $workspaceId);
            
            if ($workspaceId === 0) {
                Context::add('bypass_unit_scope', true);
            }
            return $next($request);
        }

        // 3. Delegate to registered resolvers (e.g., School module)
        if (app()->bound('workspace.resolver')) {
            $resolved = app('workspace.resolver')->resolve($request);
            if ($resolved !== null) {
                Context::add('workspace_id', (int) $resolved);
            }
        }

        return $next($request);
    }
}
