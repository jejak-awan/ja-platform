<?php

namespace Modules\School\Http\Controllers\Api\Student;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Requests\Student\StoreStudentRequest;
use Modules\School\Http\Requests\Student\UpdateStudentRequest;
use Modules\School\Models\Student\Student;
use Modules\School\Services\Student\StudentService;

class StudentController extends BaseController
{
    public function __construct(protected StudentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        /** @var array<string, mixed> $allInputs */
        $allInputs = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $students = $this->service->getStudentList($allInputs, $perPage);

        return $this->sendResponse($students, 'Students retrieved successfully.');
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $this->authorize('create', Student::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $student = $this->service->createStudent($validated);

            return $this->sendResponse($student, 'Student created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create student.', [$e->getMessage()], 500);
        }
    }

    public function show(Student $student): JsonResponse
    {
        $this->authorize('view', $student);

        return $this->sendResponse(
            $student->load(['level', 'department', 'studyGroups', 'attendances' => fn ($q) => $q->latest()->limit(10)]),
            'Student retrieved successfully.'
        );
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $this->authorize('update', $student);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $student = $this->service->updateStudent($student, $validated);

            return $this->sendResponse($student, 'Student updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update student.', [$e->getMessage()], 500);
        }
    }

    public function destroy(Student $student): JsonResponse
    {
        $this->authorize('delete', $student);

        try {
            $this->service->deleteStudent($student);

            return $this->sendResponse([], 'Student deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete student.', [$e->getMessage()], 500);
        }
    }
}
