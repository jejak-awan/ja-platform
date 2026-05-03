<?php

namespace Modules\School\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Modules\School\Models\Institution\SchoolUnit;

class IdentifySchoolUnit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $host = $request->getHost();
        
        // Skip identification if it's already set
        if (Context::has('school_unit_id')) {
            return $next($request);
        }

        // 1. Priority: Check session for admin unit switcher
        // This allows Super Admins to switch units regardless of the domain they are on
        if ($request->hasSession() && $request->session()->has('active_school_unit_id')) {
            $rawUnitId = $request->session()->get('active_school_unit_id');
            $unitId = is_numeric($rawUnitId) ? (int) $rawUnitId : 0;
            
            if ($unitId === 0) {
                // Global context - explicitly set context to null/0
                Context::add('school_unit_id', 0);
                
                // Allow seeing everything in Global context (Aggregated Dashboard)
                Context::add('bypass_unit_scope', true);
                
                // Still need school_id for ScopedBySchool to work
                // For now, we take the first school if it exists
                $school = \Modules\School\Models\Institution\School::first();
                if ($school) {
                    Context::add('school_id', $school->id);
                }
                
                return $next($request);
            }

            /** @var SchoolUnit|null $unit */
            $unit = SchoolUnit::find($unitId);
            if ($unit && $unit->is_active) {
                $this->setUnitContext($unit);
                return $next($request);
            } else {
                $request->session()->forget('active_school_unit_id');
            }
        }

        // 2. Fallback: Find the unit by domain or subdomain
        $unit = SchoolUnit::where('is_active', true)
            ->where(function ($query) use ($host) {
                $query->where('domain', $host)
                    ->orWhere('subdomain', $host);
            })
            ->first();

        if ($unit) {
            $this->setUnitContext($unit);
            return $next($request);
        }

        // 3. Last Resort: If it's a Single-Unit installation (e.g. State School), 
        // and no specific domain matched, auto-resolve to the first available unit.
        // We only do this if there's exactly one active unit to avoid ambiguity.
        if (SchoolUnit::where('is_active', true)->count() === 1) {
            $unit = SchoolUnit::where('is_active', true)->first();
            if ($unit) {
                $this->setUnitContext($unit);
            }
        }

        return $next($request);
    }

    /**
     * Set the unit context
     */
    protected function setUnitContext(SchoolUnit $unit): void
    {
        Context::add('school_unit_id', $unit->id);
        Context::add('school_id', $unit->school_id);
        
        // Share the unit data with views if needed
        view()->share('activeUnit', $unit);
    }
}
