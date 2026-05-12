<?php

namespace Modules\School\Http\Controllers\Api\Institution;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;

class SchoolUnitController extends BaseController
{
    /**
     * Display a listing of levels for a specific school.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', SchoolUnit::class);
        $schoolIdValue = $request->input('school_id', 1);
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $levels = SchoolUnit::where('school_id', $schoolId)->get();

        return $this->sendResponse($levels, 'School levels retrieved successfully.');
    }

    /**
     * Store a newly created level.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', SchoolUnit::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'level' => 'required|string|in:sd,smp,sma,smk',
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|size:8',
            'accreditation' => 'nullable|string|max:5',
            'settings' => 'nullable|array',
        ]);

        /** @var SchoolUnit $level */
        $level = SchoolUnit::create($validated);

        return $this->sendResponse($level, 'School level created successfully.', 201);
    }

    /**
     * Display the specified level.
     */
    public function show(SchoolUnit $level): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $level);
        return $this->sendResponse($level, 'School level retrieved successfully.');
    }

    /**
     * Update the specified level.
     */
    public function update(Request $request, SchoolUnit $level): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $level);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'level' => 'sometimes|string|in:sd,smp,sma,smk',
            'name' => 'sometimes|string|max:255',
            'npsn' => 'nullable|string|size:8',
            'accreditation' => 'nullable|string|max:5',
            'settings' => 'nullable|array',
        ]);

        $level->update($validated);

        return $this->sendResponse($level, 'School level updated successfully.');
    }

    /**
     * Switch the active unit context for the current session.
     */
    public function select(string $id, Request $request): \Illuminate\Http\JsonResponse
    {
        // Only allow super-admin or admin to switch context
        $user = $request->user();
        if (!$user || (!$user->hasRole('super') && !$user->hasRole('admin'))) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $unitId = (int)$id;

        if ($unitId === 0) {
            session(['active_school_unit_id' => 0]);
            return $this->sendResponse(null, 'Switched to Global/Foundation context.');
        }

        $level = SchoolUnit::findOrFail($unitId);
        session(['active_school_unit_id' => $level->id]);

        return $this->sendResponse($level, 'Switched to unit: ' . $level->name);
    }

    /**
     * Remove the specified level.
     */
    public function destroy(SchoolUnit $level): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $level);
        $level->delete();

        return $this->sendResponse([], 'School level deleted successfully.');
    }
}
