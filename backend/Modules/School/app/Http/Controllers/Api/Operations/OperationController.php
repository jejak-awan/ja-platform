<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Requests\Academic\StoreAttendanceRequest;
use Modules\School\Http\Requests\Academic\UpdateAttendanceRequest;
use Modules\School\Http\Requests\Operations\CheckInVisitorRequest;
use Modules\School\Http\Requests\Student\StoreAchievementRequest;
use Modules\School\Http\Requests\Student\StoreCounselingRecordRequest;
use Modules\School\Http\Requests\Student\StoreViolationRequest;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Operations\Visitor;
use Modules\School\Models\Student\Achievement;
use Modules\School\Models\Student\CounselingRecord;
use Modules\School\Models\Student\Violation;
use Modules\School\Services\Academic\AcademicService;
use Modules\School\Services\Operations\OperationsService;
use Modules\School\Services\Student\StudentService;

class OperationController extends BaseController
{
    public function __construct(protected AcademicService $academicService, protected StudentService $studentService, protected OperationsService $opsService) {}

    // --- Attendances ---

    public function attendanceOverview(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);
        $schoolIdValue = $request->header('X-School-Id') ?? (string) $request->string('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (string) $schoolIdValue : '1';

        $stats = $this->academicService->getAttendanceStats($schoolId);

        return $this->sendResponse($stats, 'Attendance overview retrieved successfully.');
    }

    public function attendances(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $attendances = $this->academicService->getAttendances($filters, $perPage);

        return $this->sendResponse($attendances, 'Attendances retrieved successfully.');
    }

    public function storeAttendance(StoreAttendanceRequest $request): JsonResponse
    {
        $this->authorize('create', Attendance::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $attendance = $this->academicService->createAttendance($validated);

        return $this->sendResponse($attendance, 'Attendance recorded successfully.', 201);
    }

    public function updateAttendance(UpdateAttendanceRequest $request, string $id): JsonResponse
    {
        /** @var Attendance $attendance */
        $attendance = Attendance::findOrFail($id);
        $this->authorize('update', $attendance);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $attendance = $this->academicService->updateAttendance($attendance, $validated);

        return $this->sendResponse($attendance, 'Attendance updated successfully.');
    }

    public function destroyAttendance(string $id): JsonResponse
    {
        /** @var Attendance $attendance */
        $attendance = Attendance::findOrFail($id);
        $this->authorize('delete', $attendance);
        $attendance->delete();

        return $this->sendResponse([], 'Attendance record deleted successfully.');
    }

    // --- Violations ---

    public function violations(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Violation::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $violations = $this->studentService->getViolations($filters, $perPage);

        return $this->sendResponse($violations, 'Violations retrieved successfully.');
    }

    public function studentViolationPoints(string $studentId): JsonResponse
    {
        $this->authorize('viewAny', Violation::class);
        $totalPoints = $this->studentService->getViolationPoints($studentId);

        return $this->sendResponse(['total_points' => $totalPoints], 'Student violation points calculated.');
    }

    public function storeViolation(StoreViolationRequest $request): JsonResponse
    {
        $this->authorize('create', Violation::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $violation = $this->studentService->createViolation($validated);

        return $this->sendResponse($violation, 'Violation recorded successfully.', 201);
    }

    public function updateViolation(StoreViolationRequest $request, string $id): JsonResponse
    {
        /** @var Violation $violation */
        $violation = Violation::findOrFail($id);
        $this->authorize('update', $violation);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $violation = $this->studentService->updateViolation($violation, $validated);

        return $this->sendResponse($violation, 'Violation updated successfully.');
    }

    public function destroyViolation(string $id): JsonResponse
    {
        /** @var Violation $violation */
        $violation = Violation::findOrFail($id);
        $this->authorize('delete', $violation);
        $violation->delete();

        return $this->sendResponse([], 'Violation record deleted successfully.');
    }

    // --- Achievements ---

    public function achievements(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Achievement::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $achievements = $this->studentService->getAchievements($filters, $perPage);

        return $this->sendResponse($achievements, 'Achievements retrieved successfully.');
    }

    public function storeAchievement(StoreAchievementRequest $request): JsonResponse
    {
        $this->authorize('create', Achievement::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $achievement = $this->studentService->createAchievement($validated);

        return $this->sendResponse($achievement, 'Achievement recorded successfully.', 201);
    }

    public function updateAchievement(StoreAchievementRequest $request, string $id): JsonResponse
    {
        /** @var Achievement $achievement */
        $achievement = Achievement::findOrFail($id);
        $this->authorize('update', $achievement);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $achievement = $this->studentService->updateAchievement($achievement, $validated);

        return $this->sendResponse($achievement, 'Achievement updated successfully.');
    }

    public function destroyAchievement(string $id): JsonResponse
    {
        /** @var Achievement $achievement */
        $achievement = Achievement::findOrFail($id);
        $this->authorize('delete', $achievement);
        $achievement->delete();

        return $this->sendResponse([], 'Achievement record deleted successfully.');
    }

    // --- Counseling Records (BK) ---

    public function counselingRecords(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CounselingRecord::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $records = $this->studentService->getCounselingRecords($filters, $perPage);

        return $this->sendResponse($records, 'Counseling records retrieved successfully.');
    }

    public function storeCounselingRecord(StoreCounselingRecordRequest $request): JsonResponse
    {
        $this->authorize('create', CounselingRecord::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $record = $this->studentService->createCounselingRecord($validated);

        return $this->sendResponse($record, 'Counseling record added successfully.', 201);
    }

    public function updateCounselingRecord(StoreCounselingRecordRequest $request, string $id): JsonResponse
    {
        /** @var CounselingRecord $record */
        $record = CounselingRecord::findOrFail($id);
        $this->authorize('update', $record);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $record = $this->studentService->updateCounselingRecord($record, $validated);

        return $this->sendResponse($record, 'Counseling record updated successfully.');
    }

    public function destroyCounselingRecord(string $id): JsonResponse
    {
        /** @var CounselingRecord $record */
        $record = CounselingRecord::findOrFail($id);
        $this->authorize('delete', $record);
        $record->delete();

        return $this->sendResponse([], 'Counseling record deleted successfully.');
    }

    // --- Visitor Management (VMS) ---

    public function visitors(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Visitor::class);
        $status = $request->input('status');
        $statusStr = is_string($status) ? $status : null;
        $perPage = $request->has('per_page') && is_numeric($request->input('per_page'))
            ? (int) $request->input('per_page')
            : 20;

        $visitors = $this->opsService->getVisitors($statusStr, $perPage);

        return $this->sendResponse($visitors, 'Visitors retrieved successfully.');
    }

    public function checkInVisitor(CheckInVisitorRequest $request): JsonResponse
    {
        $this->authorize('create', Visitor::class);
        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $visitor = $this->opsService->checkInVisitor(
                $validated,
                $request->file('photo'),
                $request->file('id_card_photo')
            );

            return $this->sendResponse($visitor, 'Visitor checked in successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function checkOutVisitor(string $id): JsonResponse
    {
        try {
            /** @var Visitor $visitor */
            $visitor = Visitor::findOrFail($id);
            $this->authorize('update', $visitor);
            $visitor = $this->opsService->checkOutVisitor($visitor);

            return $this->sendResponse($visitor, 'Visitor checked out successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
