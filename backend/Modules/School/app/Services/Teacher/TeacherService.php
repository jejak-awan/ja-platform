<?php

namespace Modules\School\Services\Teacher;

use Modules\School\Models\HR\Staff;
use Modules\School\Models\HR\StaffAttendance;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\TeachingJournal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    /**
     * Get the staff record for the authenticated user.
     */
    public function getStaffByUserId(int $userId): ?Staff
    {
        /** @var Staff|null $staff */
        $staff = Staff::where('user_id', $userId)->first();
        return $staff;
    }

    /**
     * Get dashboard statistics for a teacher.
     * @return array{total_schedules: int, today_schedules: int, journal_count_this_month: int, total_students: int}
     */
    public function getDashboardStats(int $staffId, ?int $schoolId = null): array
    {
        $scheduleCount = (int) Schedule::where('staff_id', $staffId)
            ->where('is_active', true)
            ->count();

        $todaySchedules = (int) Schedule::where('staff_id', $staffId)
            ->where('day', strtolower(now()->format('l')))
            ->where('is_active', true)
            ->count();

        $journalCount = (int) TeachingJournal::where('staff_id', $staffId)
            ->whereMonth('created_at', (int)now()->month)
            ->count();

        $totalStudents = (int) DB::table('sch_acad_schedules as s')
            ->join('sch_acad_study_group_members as sgm', 's.study_group_id', '=', 'sgm.study_group_id')
            ->where('s.staff_id', $staffId)
            ->where('s.is_active', true)
            ->distinct('sgm.student_id')
            ->count('sgm.student_id');

        return [
            'total_schedules' => $scheduleCount,
            'today_schedules' => $todaySchedules,
            'journal_count_this_month' => $journalCount,
            'total_students' => $totalStudents,
        ];
    }

    /**
     * Get teacher's schedule list.
     * @return Collection<int, Schedule>
     */
    public function getSchedules(int $staffId): Collection
    {
        /** @var Collection<int, Schedule> $schedules */
        $schedules = Schedule::with(['subject', 'studyGroup', 'room'])
            ->where('staff_id', $staffId)
            ->where('is_active', true)
            ->orderByRaw("FIELD(day, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')")
            ->orderBy('start_time')
            ->get();
        return $schedules;
    }

    /**
     * Get recent teaching journals for a teacher.
     * @return Collection<int, TeachingJournal>
     */
    public function getRecentJournals(int $staffId, int $limit = 10): Collection
    {
        /** @var Collection<int, TeachingJournal> $journals */
        $journals = TeachingJournal::with(['schedule.subject', 'schedule.studyGroup'])
            ->where('staff_id', $staffId)
            ->latest()
            ->limit($limit)
            ->get();
        return $journals;
    }
}
