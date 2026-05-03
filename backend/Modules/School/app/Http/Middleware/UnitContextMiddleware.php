<?php

namespace Modules\School\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class UnitContextMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $levelId = $request->header('X-Level-ID');
        $isGlobalAdmin = $user->getRoleRank() >= 95; // super (100), admin-yayasan (100), admin-yayasan (95)

        if ($levelId) {
            $levelId = (int) $levelId;

            // If not a global admin, verify if the user is assigned to this unit
            if (! $isGlobalAdmin) {
                $assignedLevels = $user->levels()->pluck('sch_ins_levels.id')->toArray();
                if (! in_array($levelId, $assignedLevels)) {
                    return response()->json([
                        'message' => 'Unauthorized access to this school unit.',
                    ], 403);
                }
            }

            Context::add('school_unit_id', $levelId);
        } elseif (! $isGlobalAdmin) {
            // Default to first assigned level if no header provided for restricted users
            $firstLevel = $user->levels()->first();
            if ($firstLevel) {
                Context::add('school_unit_id', $firstLevel->id);
            }
        }

        return $next($request);
    }
}
