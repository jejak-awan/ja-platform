<?php

namespace Modules\School\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Modules\System\Contracts\WorkspaceResolver;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;

class SchoolWorkspaceResolver implements WorkspaceResolver
{
    /**
     * Resolve the active workspace ID.
     */
    public function resolve(Request $request): ?int
    {
        $host = $request->getHost();

        // 1. Resolve by Domain/Subdomain
        $unit = SchoolUnit::where('is_active', true)
            ->where(function ($query) use ($host) {
                $query->where('domain', $host)
                    ->orWhere('subdomain', $host);
            })
            ->first();

        if ($unit) {
            $this->setSchoolDomainContext($unit);
            return (int) $unit->id;
        }

        // 2. Auto-resolve for Single-Unit installations
        if (SchoolUnit::where('is_active', true)->count() === 1) {
            $unit = SchoolUnit::where('is_active', true)->first();
            if ($unit) {
                $this->setSchoolDomainContext($unit);
                return (int) $unit->id;
            }
        }

        return null;
    }

    /**
     * Set School-specific domain context (school_id).
     */
    protected function setSchoolDomainContext(SchoolUnit $unit): void
    {
        // Still need school_id for ScopedBySchool to work
        Context::add('school_id', (int) $unit->school_id);
        
        // Share with views if needed
        view()->share('activeUnit', $unit);
    }
}
