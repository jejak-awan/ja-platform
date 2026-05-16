<?php

namespace Modules\School\Http\Controllers\Api\Admission;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Admission\Enrollment;
use Modules\School\Models\Admission\EnrollmentDocument;
use Modules\School\Services\Admission\AdmissionService;
use Modules\School\Http\Requests\Admission\StoreEnrollmentRequest;

class AdmissionController extends BaseController
{
    public function __construct(protected AdmissionService $service)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Enrollment::class);

        /** @var array<string, mixed> $allInputs */
        $allInputs = $request->all();
        $perPageValue = $request->get('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        $enrollments = $this->service->getEnrollments($allInputs, $perPage);
        
        return $this->sendResponse($enrollments, 'Enrollments retrieved successfully.');
    }

    public function store(StoreEnrollmentRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Enrollment::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $enrollment = $this->service->createEnrollment($validated);
            return $this->sendResponse($enrollment, 'Enrollment created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create enrollment.', [$e->getMessage()], 500);
        }
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::with(['documents', 'academicYear'])->findOrFail($id);
        $this->authorize('view', $enrollment);
        
        return $this->sendResponse($enrollment, 'Enrollment retrieved successfully.');
    }

    public function updateStatus(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::findOrFail($id);
        $this->authorize('update', $enrollment);

        /** @var array{status: string} $validated */
        $validated = $request->validate([
            'status' => 'required|in:draft,applied,verified,exam,admitted,rejected',
        ]);

        $enrollment->update(['status' => $validated['status']]);
        return $this->sendResponse($enrollment, 'Status updated successfully.');
    }

    public function verifyDocument(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var EnrollmentDocument $document */
        $document = EnrollmentDocument::findOrFail($id);
        
        // Document authorization should ideally pass through the Enrollment policy
        $this->authorize('update', $document->enrollment);

        /** @var array{status: string, notes?: string|null} $validated */
        $validated = $request->validate([
            'status' => 'required|in:pending,verified,rejected',
            'notes' => 'nullable|string',
        ]);

        $document->update($validated);
        return $this->sendResponse($document, 'Document verification updated.');
    }

    public function admit(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::findOrFail($id);
        $this->authorize('admit', $enrollment);
        
        try {
            $student = $this->service->admitStudent($enrollment);
            return $this->sendResponse($student, 'Student admitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
