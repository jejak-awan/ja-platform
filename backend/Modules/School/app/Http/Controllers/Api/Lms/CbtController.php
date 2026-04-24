<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Services\Lms\CbtService;
use Modules\School\Models\Lms\ExamSession;

class CbtController extends BaseController
{
    protected CbtService $service;

    public function __construct(CbtService $service)
    {
        $this->service = $service;
    }

    public function exams(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', \Modules\School\Models\Lms\Exam::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $schoolIdValue = $request->header('X-School-Id') ?? ($filters['school_id'] ?? 1);
        $filters['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($filters['academic_year_id'] ?? '');
        $filters['academic_year_id'] = is_string($yearValue) ? $yearValue : (is_scalar($yearValue) ? (string)$yearValue : '');

        $exams = $this->service->getFormalExams($filters);
        return $this->sendResponse($exams, 'Formal exams retrieved successfully.');
    }

    public function indexSessions(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', ExamSession::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $sessions = $this->service->getSessions($filters);
        return $this->sendResponse($sessions, 'Exam sessions retrieved successfully.');
    }

    public function storeSession(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', ExamSession::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'exam_id' => 'required|exists:sch_lms_exams,id',
            'study_group_id' => 'required|exists:sch_acad_study_groups,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $session = $this->service->createSession($validated);
        return $this->sendResponse($session, 'Exam session created successfully.', 201);
    }

    public function updateSession(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var ExamSession $session */
        $session = ExamSession::findOrFail($id);
        $this->authorize('update', $session);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'study_group_id' => 'sometimes|exists:sch_acad_study_groups,id',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'is_active' => 'boolean',
        ]);

        $session = $this->service->updateSession($session, $validated);
        return $this->sendResponse($session, 'Exam session updated successfully.');
    }

    public function destroySession(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var ExamSession $session */
        $session = ExamSession::findOrFail($id);
        $this->authorize('delete', $session);
        $this->service->deleteSession($session);

        return $this->sendResponse([], 'Exam session deleted successfully.');
    }

    public function generateToken(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var ExamSession $session */
        $session = ExamSession::findOrFail($id);
        $this->authorize('update', $session);

        $token = $this->service->generateToken($session);
        return $this->sendResponse(['token' => $token], 'Token generated successfully.');
    }
}
