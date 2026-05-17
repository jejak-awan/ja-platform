<?php

namespace Modules\School\Http\Controllers\Api\Teacher;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Controllers\Api\Common\BaseController as CommonBaseController;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\TeachingJournal;
use Illuminate\Support\Facades\Auth;
use Modules\School\Services\Teacher\TeacherService;

class TeacherPortalController extends CommonBaseController
{
    public function __construct(protected TeacherService $service)
    {
    }

    /**
     * Get the logged-in teacher's staff record.
     */
    private function getStaff(): ?Staff
    {
        /** @var string|null $userId */
        $userId = Auth::id();
        if (!$userId) {
            return null;
        }
        return $this->service->getStaffByUserId($userId);
    }

    public function dashboardStats(): \Illuminate\Http\JsonResponse
    {
        $staff = $this->getStaff();
        if (!$staff instanceof \Modules\School\Models\HR\Staff) {
            return $this->sendError('Staff record not found for this user.', [], 404);
        }

        $this->authorize('view', $staff);
        
        $staffId = (string) $staff->id;
        $stats = $this->service->getDashboardStats($staffId);

        return $this->sendResponse([
            'total_classes' => $stats['total_schedules'],
            'active_students' => $stats['total_students'],
            'journals_count' => $stats['journal_count_this_month'],
            'grading_queue' => 0
        ], 'Teacher stats retrieved successfully.');
    }

    public function schedules(Request $request): \Illuminate\Http\JsonResponse
    {
        $staff = $this->getStaff();
        if (!$staff instanceof \Modules\School\Models\HR\Staff) {
            return $this->sendError('Staff record not found.', [], 404);
        }

        $this->authorize('view', $staff);

        $schedules = $this->service->getSchedules($staff->id);

        if ($request->has('day')) {
            $dayValue = $request->input('day');
            $day = is_string($dayValue) ? $dayValue : '';
            /** @var \Illuminate\Support\Collection<int, Schedule> $schedules */
            $schedules = $schedules->where('day', $day);
        }

        return $this->sendResponse($schedules, 'Teacher schedules retrieved successfully.');
    }

    public function recentJournals(): \Illuminate\Http\JsonResponse
    {
        $staff = $this->getStaff();
        if (!$staff instanceof \Modules\School\Models\HR\Staff) {
            return $this->sendError('Staff record not found.', [], 404);
        }

        $this->authorize('view', $staff);

        $journals = $this->service->getRecentJournals($staff->id, 5);

        return $this->sendResponse($journals, 'Recent journals retrieved successfully.');
    }
}
