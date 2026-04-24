<?php

namespace Modules\School\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\HR\JobVacancy;
use Modules\School\Models\HR\JobApplication;
use Modules\School\Models\Student\Student;

class CareerController extends BaseController
{
    public function vacancies(): \Illuminate\Http\JsonResponse
    {
        $vacancies = JobVacancy::withCount('applications')->latest()->get();
        return $this->sendResponse($vacancies, 'Job vacancies retrieved successfully.');
    }

    public function storeVacancy(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
        ]);

        $vacancy = JobVacancy::create($validated);
        return $this->sendResponse($vacancy, 'Job vacancy posted successfully.', 201);
    }

    public function applications(?int $vacancyId = null): \Illuminate\Http\JsonResponse
    {
        $query = JobApplication::with(['vacancy', 'student']);
        if ($vacancyId) {
            $query->where('vacancy_id', $vacancyId);
        }
        return $this->sendResponse($query->latest()->get(), 'Job applications retrieved successfully.');
    }

    public function apply(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $validated = $request->validate([
            'vacancy_id' => 'required|exists:job_vacancies,id',
            'student_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $canManageLogistics = $user->can('manage logistics');
        if ($canManageLogistics) {
            $requestStudentId = $request->input('student_id');
            if (! is_numeric($requestStudentId)) {
                return $this->sendError('student_id is required for privileged apply action.', [], 422);
            }

            $student = Student::query()->find((int) $requestStudentId);
            if (! $student) {
                return $this->sendError('Student not found.', [], 404);
            }
        } else {
            $student = Student::query()->where('user_id', $user->id)->first();
            if (! $student) {
                return $this->sendError('Authenticated user is not linked to a student account.', [], 403);
            }
        }

        $validated['student_id'] = $student->id;

        $alreadyApplied = JobApplication::query()
            ->where('vacancy_id', $validated['vacancy_id'])
            ->where('student_id', $validated['student_id'])
            ->exists();
        if ($alreadyApplied) {
            return $this->sendError('You have already applied to this vacancy.', [], 409);
        }

        $application = JobApplication::create($validated);
        return $this->sendResponse($application, 'Application submitted successfully.', 201);
    }
}
