<?php

namespace Modules\School\Http\Controllers\Api\Institution;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolLevel;

class SchoolLevelController extends BaseController
{
    /**
     * Display a listing of levels for a specific school.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', SchoolLevel::class);
        $schoolIdValue = $request->input('school_id', 1);
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $levels = SchoolLevel::where('school_id', $schoolId)->get();

        return $this->sendResponse($levels, 'School levels retrieved successfully.');
    }

    /**
     * Store a newly created level.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', SchoolLevel::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'level' => 'required|string|in:sd,smp,sma,smk',
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|size:8',
            'accreditation' => 'nullable|string|max:5',
            'settings' => 'nullable|array',
        ]);

        /** @var SchoolLevel $level */
        $level = SchoolLevel::create($validated);

        return $this->sendResponse($level, 'School level created successfully.', 201);
    }

    /**
     * Display the specified level.
     */
    public function show(SchoolLevel $level): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $level);
        return $this->sendResponse($level, 'School level retrieved successfully.');
    }

    /**
     * Update the specified level.
     */
    public function update(Request $request, SchoolLevel $level): \Illuminate\Http\JsonResponse
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
     * Remove the specified level.
     */
    public function destroy(SchoolLevel $level): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $level);
        $level->delete();

        return $this->sendResponse([], 'School level deleted successfully.');
    }
}
