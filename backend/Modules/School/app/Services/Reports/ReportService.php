<?php

namespace Modules\School\Services\Reports;

use Modules\School\Models\Student\Student;

use Modules\School\Models\Academic\Attendance;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get student profile data for PDF report.
     * @return array{student: Student, attendance_stats: array<string, int>}
     */
    public function getStudentProfileData(int $studentId): array
    {
        /** @var Student $student */
        $student = Student::with([
            'school',
            'level',
            'department',
            'studyGroups',
            'attendances' => fn($q) => $q->latest()->limit(30),
            'violations' => fn($q) => $q->latest()->limit(10),
            'achievements' => fn($q) => $q->latest()->limit(10),
        ])->findOrFail($studentId);

        /** @var array<string, int> $attendanceStats */
        $attendanceStats = Attendance::where('student_id', $studentId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'student' => $student,
            'attendance_stats' => $attendanceStats,
        ];
    }



    /**
     * Get student ID card data.
     * @return array{student: Student, verification_url: string}
     */
    public function getStudentIdCardData(int $studentId): array
    {
        /** @var Student $student */
        $student = Student::with(['school', 'level', 'department'])->findOrFail($studentId);

        return [
            'student' => $student,
            'verification_url' => $student->getVerificationUrl('id_card'),
        ];
    }

    /**
     * Get graduation certificate (SKL) data.
     * @return array{student: Student, verification_url: string}
     */
    public function getGraduationCertificateData(int $studentId): array
    {
        /** @var Student $student */
        $student = Student::with(['school', 'level', 'department'])->findOrFail($studentId);

        return [
            'student' => $student,
            'verification_url' => $student->getVerificationUrl('graduation'),
        ];
    }

    /**
     * Get attendance report data for a period.
     * @return array{total_records: int, stats: array<string, int>}
     */
    public function getAttendanceReport(int $schoolId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Attendance::with('student')
            ->whereHas('student', fn($q) => $q->where('school_id', $schoolId));

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        /** @var array<string, int> $stats */
        $stats = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total_records' => array_sum($stats),
            'stats' => $stats,
        ];
    }


}
