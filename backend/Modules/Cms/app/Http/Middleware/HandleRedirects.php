<?php

namespace Modules\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\Redirect;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip API routes and static files
        if ($request->is('api/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('sitemap.xml*') ||
            $request->is('robots.txt')) {
            return $next($request);
        }

        // Check for redirects
        $redirect = Redirect::where('from_url', $request->path())
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->recordHit();

            return redirect($redirect->to_url, (int) $redirect->type);
        }

        return $next($request);
    }
}
