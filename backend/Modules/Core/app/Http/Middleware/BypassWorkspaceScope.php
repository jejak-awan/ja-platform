<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;

class BypassWorkspaceScope
{
    public function handle(Request $request, Closure $next): mixed
    {
        Context::add('bypass_unit_scope', true);

        return $next($request);
    }
}
