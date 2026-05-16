<?php

namespace Modules\School\Services\Student;

use Modules\School\Models\Student\Student;
use Modules\School\Models\Student\Violation;
use Modules\School\Models\Student\Achievement;
use Modules\School\Models\Student\CounselingRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentService
{
    /**
     * Get students with filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Student>
     */
    public function getStudentList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Student::with(['level', 'department']);

        if (!empty($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['level_id'])) {
            $query->where('workspace_id', $filters['level_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new student.
     *
     * @param array<string, mixed> $data
     */
    public function createStudent(array $data): Student
    {
        /** @var Student $student */
        $student = Student::create($data);
        return $student;
    }

    /**
     * Update student details.
     *
     * @param array<string, mixed> $data
     */
    public function updateStudent(Student $student, array $data): Student
    {
        $student->update($data);
        return $student;
    }

    /**
     * Delete a student.
     */
    public function deleteStudent(Student $student): bool
    {
        return (bool) $student->delete();
    }

    // --- Violations ---

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Violation>
     */
    public function getViolations(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Violation::with('student')
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->latest()
            ->paginate($perPage);
    }

    public function getViolationPoints(int $studentId): int
    {
        return (int) Violation::where('student_id', $studentId)->sum('points');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createViolation(array $data): Violation
    {
        /** @var Violation $violation */
        $violation = Violation::create($data);
        return $violation;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateViolation(Violation $violation, array $data): Violation
    {
        $violation->update($data);
        return $violation;
    }

    // --- Achievements ---

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Achievement>
     */
    public function getAchievements(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Achievement::with('student')
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAchievement(array $data): Achievement
    {
        /** @var Achievement $achievement */
        $achievement = Achievement::create($data);
        return $achievement;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateAchievement(Achievement $achievement, array $data): Achievement
    {
        $achievement->update($data);
        return $achievement;
    }

    // --- Counseling (BK) ---

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, CounselingRecord>
     */
    public function getCounselingRecords(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return CounselingRecord::with(['student', 'counselor'])
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->when(!empty($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCounselingRecord(array $data): CounselingRecord
    {
        /** @var CounselingRecord $record */
        $record = CounselingRecord::create($data);
        return $record;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCounselingRecord(CounselingRecord $record, array $data): CounselingRecord
    {
        $record->update($data);
        return $record;
    }

    /**
     * Batch update students status to graduated.
     * 
     * @param array<int> $studentIds
     */
    public function batchGraduate(array $studentIds): int
    {
        return Student::whereIn('id', $studentIds)
            ->update(['status' => 'graduated']);
    }

    /**
     * Find student for public graduation check.
     */
    public function findForGraduationCheck(string $identifier, string $dob): ?Student
    {
        return Student::where(function($q) use ($identifier): void {
                $q->where('nisn', $identifier)
                  ->orWhere('nis', $identifier);
            })
            ->where('date_of_birth', $dob)
            ->first();
    }
}
