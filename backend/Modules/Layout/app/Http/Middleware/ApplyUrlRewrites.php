<?php

namespace Modules\Layout\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Layout\Models\UrlRewrite;
use Symfony\Component\HttpFoundation\Response;

class ApplyUrlRewrites
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip API routes and critical system files
        if ($request->is('api/*') ||
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('sitemap.xml*') ||
            $request->is('robots.txt')) {
            return $next($request);
        }

        // Find rewrite for this path
        $path = $request->path();
        
        $rewrite = UrlRewrite::where('source_path', $path)
            ->where('is_active', true)
            ->first();

        if ($rewrite) {
            $rewrite->increment('hits');
            $rewrite->update(['last_hit_at' => now()]);

            return redirect()->to($rewrite->target_path, $rewrite->status_code);
        }

        return $next($request);
    }
}
