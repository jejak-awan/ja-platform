<?php

namespace Modules\School\Http\Controllers\Api\Institution;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Services\Institution\InstitutionService;
use Modules\School\Http\Requests\Institution\StoreSchoolRequest;
use Modules\School\Http\Requests\Institution\UpdateSchoolRequest;
use Modules\School\Exceptions\SchoolModuleException;

class SchoolController extends BaseController
{
    protected InstitutionService $service;

    public function __construct(InstitutionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', School::class);

        $schools = $this->service->getAllSchools();

        return $this->sendResponse($schools, 'Schools retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', School::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $school = $this->service->createSchool($validated);
            return $this->sendResponse($school, 'School created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create school.', [$e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(School $school): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $school);

        return $this->sendResponse(
            $school->load(['levels', 'activeAcademicYear.semesters']),
            'School retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolRequest $request, School $school): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $school);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $school = $this->service->updateSchool($school, $validated);
            return $this->sendResponse($school, 'School updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update school.', [$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $school);

        // Prevent deletion if school has related data
        if ($school->students()->count() > 0 || $school->staff()->count() > 0) {
            return $this->sendError(
                'Cannot delete school with active students or staff. Please transfer or remove them first.',
                [],
                409
            );
        }

        try {
            $school->delete();
            return $this->sendResponse([], 'School deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete school.', [$e->getMessage()], 500);
        }
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', School::class);

        try {
            $schoolId = $this->resolveSchoolId($request);
            $stats = $this->service->getSchoolStats($schoolId);
            return $this->sendResponse($stats, 'School statistics retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve statistics.', [$e->getMessage()], 500);
        }
    }

    public function checkSetupStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', School::class);

        try {
            $schoolId = $this->resolveSchoolId($request);
            $result = $this->service->getSetupStatus($schoolId);
            return $this->sendResponse($result, 'Setup status retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve setup status.', [$e->getMessage()], 500);
        }
    }

    public function updateLogo(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'school_id' => 'nullable|integer|exists:sch_ins_schools,id'
        ]);

        $schoolId = $this->resolveSchoolId($request);
        $school = School::findOrFail($schoolId);

        $this->authorize('update', $school);

        if ($request->hasFile('logo')) {
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $request->file('logo');
            $path = $file->store('schools/logos', 'public');
            $school->update(['logo' => $path]);
            
            return $this->sendResponse(['logo_url' => \Illuminate\Support\Facades\Storage::url((string)$path)], 'Logo uploaded successfully.');
        }

        return $this->sendError('No logo file found.', [], 400);
    }

    public function defaultSchool(): \Illuminate\Http\JsonResponse
    {
        $school = School::with(['levels', 'activeAcademicYear.semesters'])->first();
        if (!$school) {
            return $this->sendError('School not found', [], 404);
        }
        return $this->sendResponse($school, 'School retrieved successfully.');
    }

    /**
     * Resolve the school ID from the request context.
     */
    protected function resolveSchoolId(Request $request): int
    {
        $schoolId = $request->input('school_id')
            ?? $request->header('X-School-Id');

        if ($schoolId !== null && is_numeric($schoolId)) {
            return (int) $schoolId;
        }

        /** @var School|null $defaultSchool */
        $defaultSchool = School::first();
        if (!$defaultSchool) {
            throw SchoolModuleException::notFound('School', 0);
        }

        return (int) $defaultSchool->id;
    }
}
