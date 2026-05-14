<?php

namespace Modules\School\Http\Controllers\Api\Academic;

use Modules\School\Http\Controllers\Api\Common\BaseController;
use Illuminate\Http\Request;
use Modules\School\Services\Academic\GradeService;
use Modules\School\Models\Academic\Grade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Context;

class GradeController extends BaseController
{
    protected GradeService $service;

    public function __construct(GradeService $service)
    {
        $this->service = $service;
    }

    /**
     * Get student's grades for a specific academic year
     */
    public function getStudentGrades(Request $request, int $studentId): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view academic', Grade::class);

        $request->validate([
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'semester_id' => 'nullable|exists:sch_acad_semesters,id',
        ]);

        try {
            $ayIdValue = $request->input('academic_year_id');
            $ayId = is_numeric($ayIdValue) ? (int)$ayIdValue : 0;
            
            $semIdValue = $request->input('semester_id');
            $semId = is_numeric($semIdValue) ? (int)$semIdValue : null;

            $grades = $this->service->getStudentGrades(
                $studentId,
                $ayId,
                $semId
            );
            return $this->sendResponse($grades, 'Grades retrieved successfully.');
        } catch (\Exception $e) {
             return $this->sendError('Failed to retrieve grades.', [$e->getMessage()], 500);
        }
    }

    /**
     * Save a single grade entry
     */
    public function saveGrade(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage academic', Grade::class);

        /** @var array{student_id: int, subject_id: int, academic_year_id: int, semester_id: int, daily_score?: float|null, mid_score?: float|null, final_score?: float|null, practice_score?: float|null, project_score?: float|null, portfolio_score?: float|null, description?: string|null, school_id?: int|null, workspace_id?: int|null} $validated */
        $validated = $request->validate([
            'student_id' => 'required|exists:sch_std_students,id',
            'subject_id' => 'required|exists:sch_acad_subjects,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'semester_id' => 'required|exists:sch_acad_semesters,id',
            'daily_score' => 'nullable|numeric|between:0,100',
            'mid_score' => 'nullable|numeric|between:0,100',
            'final_score' => 'nullable|numeric|between:0,100',
            'practice_score' => 'nullable|numeric|between:0,100',
            'project_score' => 'nullable|numeric|between:0,100',
            'portfolio_score' => 'nullable|numeric|between:0,100',
            'description' => 'nullable|string',
        ]);

        // Add context for school/level
        $contextSchoolId = Context::get('school_id');
        $contextLevelId = Context::get('workspace_id');
        
        $validated['school_id'] = is_numeric($contextSchoolId) ? (int)$contextSchoolId : null;
        $validated['workspace_id'] = is_numeric($contextLevelId) ? (int)$contextLevelId : null;

        try {
            /** @var array<string, mixed> $gradeData */
            $gradeData = $validated;
            $grade = $this->service->saveGrade($gradeData);
            return $this->sendResponse($grade, 'Grade saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to save grade.', [$e->getMessage()], 500);
        }
    }

    /**
     * Bulk save grades for a class
     */
    public function bulkSaveGrades(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage academic', Grade::class);

        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:sch_std_students,id',
            'grades.*.subject_id' => 'required|exists:sch_acad_subjects,id',
            'grades.*.academic_year_id' => 'required|exists:sch_acad_years,id',
            'grades.*.semester_id' => 'required|exists:sch_acad_semesters,id',
            'grades.*.daily_score' => 'nullable|numeric|between:0,100',
        ]);

        $contextSchoolId = Context::get('school_id');
        $contextLevelId = Context::get('workspace_id');
        
        $schoolId = is_numeric($contextSchoolId) ? (int)$contextSchoolId : 1;
        $levelId = is_numeric($contextLevelId) ? (int)$contextLevelId : 1;
        
        /** @var array<int, array<string, mixed>> $rawGrades */
        $rawGrades = (array) $request->input('grades');

        $gradesData = array_map(function($g) use ($schoolId, $levelId) {
            /** @var array<string, mixed> $g */
            $g['school_id'] = $schoolId;
            $g['workspace_id'] = $levelId;
            return $g;
        }, $rawGrades);

        try {
            $count = $this->service->bulkSaveGrades($gradesData);
            return $this->sendResponse([], "$count grades saved successfully.");
        } catch (\Exception $e) {
            return $this->sendError('Failed to bulk save grades.', [$e->getMessage()], 500);
        }
    }
}
