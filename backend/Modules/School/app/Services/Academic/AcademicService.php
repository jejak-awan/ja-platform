<?php

namespace Modules\School\Services\Academic;

use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Academic\Subject;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\TeachingJournal;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Logistics\Room;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AcademicService
{
    /**
     * Get academic overview stats.
     *
     * @return array{years_count: int, semesters_count: int, subjects_count: int, study_groups_count: int}
     */
    public function getOverviewStats(): array
    {
        return [
            'years_count' => (int) AcademicYear::count(),
            'semesters_count' => (int) Semester::count(),
            'subjects_count' => (int) Subject::count(),
            'study_groups_count' => (int) StudyGroup::count(),
        ];
    }

    // --- Academic Years ---

    /**
     * @return Collection<int, AcademicYear>
     */
    public function getAllYears(): Collection
    {
        return AcademicYear::with('semesters')->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createYear(array $data): AcademicYear
    {
        /** @var AcademicYear $year */
        $year = AcademicYear::create($data);
        return $year;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateYear(AcademicYear $year, array $data): AcademicYear
    {
        $year->update($data);
        return $year;
    }

    // --- Semesters ---

    /**
     * @return Collection<int, Semester>
     */
    public function getSemesters(?string $academicYearId = null): Collection
    {
        $query = Semester::with('academicYear');
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        return $query->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSemester(array $data): Semester
    {
        /** @var Semester $semester */
        $semester = Semester::create($data);
        return $semester;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSemester(Semester $semester, array $data): Semester
    {
        $semester->update($data);
        return $semester;
    }

    // --- Study Groups ---

    /**
     * @return Collection<int, StudyGroup>
     */
    public function getAllStudyGroups(): Collection
    {
        return StudyGroup::with(['level', 'department', 'homeroomTeacher'])
            ->withCount('students')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createStudyGroup(array $data): StudyGroup
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::create($data);
        return $group;
    }

    public function addGroupMember(StudyGroup $group, string $studentId): void
    {
        $group->students()->syncWithoutDetaching([$studentId]);
    }

    // --- Schedules & Collision Detection ---

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Schedule>
     */
    public function getSchedules(array $filters): Collection
    {
        $query = Schedule::with(['subject', 'staff', 'studyGroup' => fn($q) => $q->withCount('students'), 'room']);

        if (!empty($filters['study_group_id'])) {
            $query->where('study_group_id', $filters['study_group_id']);
        }
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        if (!empty($filters['day']) && is_string($filters['day'])) {
            $query->where('day', $filters['day']);
        }

        return $query->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSchedule(array $data): Schedule
    {
        /** @var Schedule $schedule */
        $schedule = Schedule::create($data);
        return $schedule;
    }

    /**
     * @param array<string, mixed> $data
     * @param int|string|null $excludeId
     * @return array<int, string>
     */
    public function detectCollisions(array $data, mixed $excludeId = null): array
    {
        $collisions = [];

        // 1. Staff Collision
        if (Schedule::where('staff_id', $data['staff_id'])
            ->where('day', $data['day'])
            ->where('is_active', true)
            ->where(function ($q) use ($data): void {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            })
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $collisions[] = 'Guru sudah memiliki jadwal di jam yang sama.';
        }

        // 2. Room Collision & Capacity
        if (!empty($data['room_id'])) {
            /** @var Room|null $room */
            $room = Room::find($data['room_id']);
            /** @var StudyGroup|null $group */
            $group = StudyGroup::withCount('students')->find($data['study_group_id']);
            
            if ($room && $group && (int)$room->capacity < (int)$group->students_count) {
                $collisions[] = "Kapasitas ruangan ($room->capacity) tidak mencukupi untuk jumlah siswa ($group->students_count).";
            }

            if (Schedule::where('room_id', $data['room_id'])
                ->where('day', $data['day'])
                ->where('is_active', true)
                ->where(function ($q) use ($data): void {
                    $q->where('start_time', '<', $data['end_time'])
                      ->where('end_time', '>', $data['start_time']);
                })
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()) {
                $collisions[] = 'Ruangan sudah digunakan di jam yang sama.';
            }
        }

        // 3. Group Collision
        if (Schedule::where('study_group_id', $data['study_group_id'])
            ->where('day', $data['day'])
            ->where('is_active', true)
            ->where(function ($q) use ($data): void {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time', '>', $data['start_time']);
            })
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $collisions[] = 'Kelas sudah memiliki jadwal di jam yang sama.';
        }

        return $collisions;
    }

    // --- Teaching Journals ---

    /**
     * @param array<string, mixed> $data
     * @param \Illuminate\Http\UploadedFile|null $evidenceFile
     */
    public function createJournal(array $data, $evidenceFile = null): TeachingJournal
    {
        if ($evidenceFile) {
            $data['evidence_path'] = $evidenceFile->store('journals', 'public');
        }

        /** @var TeachingJournal $journal */
        $journal = TeachingJournal::create($data);
        return $journal;
    }

    /**
     * @param array<string, mixed> $data
     * @param \Illuminate\Http\UploadedFile|null $evidenceFile
     */
    public function updateJournal(TeachingJournal $journal, array $data, $evidenceFile = null): TeachingJournal
    {
        if ($evidenceFile) {
            if ($journal->evidence_path) {
                Storage::disk('public')->delete((string)$journal->evidence_path);
            }
            $data['evidence_path'] = $evidenceFile->store('journals', 'public');
        }

        $journal->update($data);
        return $journal;
    }

    // --- Attendances ---

    /**
     * @return array<int, array{status: string, count: int}>
     */
    public function getAttendanceStats(string $schoolId): array
    {
        /** @var array<int, array{status: string, count: int}> $stats */
        $stats = Attendance::whereHas('student', function ($q) use ($schoolId): void {
            $q->where('school_id', $schoolId);
        })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->toArray();

        return $stats;
    }

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Attendance>
     */
    public function getAttendances(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Attendance::with(['student', 'academicYear', 'semester'])
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAttendance(array $data): Attendance
    {
        /** @var Attendance $attendance */
        $attendance = Attendance::create($data);
        return $attendance;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);
        return $attendance;
    }
}
