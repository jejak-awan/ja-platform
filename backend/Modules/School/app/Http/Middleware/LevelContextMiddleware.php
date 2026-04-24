<?php

namespace Modules\School\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class LevelContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $levelId = $request->header('X-Level-ID');

        if ($levelId) {
            Context::add('school_level_id', (int) $levelId);
        }

        return $next($request);
    }
}
