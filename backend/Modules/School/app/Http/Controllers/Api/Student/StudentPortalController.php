<?php

namespace Modules\School\Http\Controllers\Api\Student;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\Grade;
use Modules\School\Models\Operations\GraduationResult;
use Modules\School\Models\Operations\DocumentTemplate;

class StudentPortalController extends BaseController
{
    public function __construct(protected \Modules\School\Services\Operations\DocumentService $documentService)
    {
    }

    private function getStudent(): Student
    {
        /** @var \Modules\System\Models\User|null $user */
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $userId = (int) $user->id;

        /** @var Student|null $student */
        $student = Student::where('user_id', $userId)->first();
        if (!$student) {
            abort(403, 'Akses ditolak. Anda bukan siswa terdaftar.');
        }
        return $student;
    }

    public function dashboard(): \Illuminate\Http\JsonResponse
    {
        $student = $this->getStudent();
        
        return $this->sendResponse([
            'student' => $student->only(['full_name', 'nisn', 'nis']),
            'summary' => [
                'attendance_rate' => $this->calculateAttendanceRate((int)$student->id),
                'gpa' => $this->calculateGPA((int)$student->id),
            ]
        ], 'Dashboard data retrieved.');
    }



    public function attendance(): \Illuminate\Http\JsonResponse
    {
        $student = $this->getStudent();
        $attendance = Attendance::where('student_id', $student->id)
            ->latest()
            ->paginate(30);
            
        return $this->sendResponse($attendance, 'Attendance history retrieved.');
    }

    public function grades(): \Illuminate\Http\JsonResponse
    {
        $student = $this->getStudent();
        // Fetch actual E-Rapor Grades instead of LMS Exam Results
        $grades = Grade::with(['subject', 'teacher'])
            ->where('student_id', $student->id)
            ->latest('academic_year_id')
            ->get();
            
        return $this->sendResponse($grades, 'Academic E-Rapor grades retrieved.');
    }

    public function schedule(): \Illuminate\Http\JsonResponse
    {
        $student = $this->getStudent();
        /** @var \Illuminate\Support\Collection<int, int> $groupIds */
        $groupIds = $student->studyGroups()->pluck('sch_acad_study_groups.id');
        
        $schedule = Schedule::with(['subject', 'staff', 'room'])
            ->whereIn('study_group_id', $groupIds)
            ->where('is_active', true)
            ->get();
            
        return $this->sendResponse($schedule, 'Weekly schedule retrieved.');
    }

    private function calculateAttendanceRate(int $studentId): float
    {
        $total = Attendance::where('student_id', $studentId)->count();
        if ($total === 0) {
            return 0.0;
        }
        
        $present = Attendance::where('student_id', $studentId)
            ->whereIn('status', ['h', 'i', 's']) // Hadir, Izin, Sakit are often counted as non-absent in basic stats
            ->count();
            
        return round(($present / $total) * 100, 1);
    }

    private function calculateGPA(int $studentId): float
    {
        // Calculate GPA based on E-Rapor final grades
        $avg = Grade::where('student_id', $studentId)->avg('final_grade');
        return round((float)$avg, 2);
    }

    public function graduation(): \Illuminate\Http\JsonResponse
    {
        $student = $this->getStudent();
        $result = GraduationResult::where('student_id', $student->id)
            ->latest('graduation_year')
            ->first();

        return $this->sendResponse([
            'student' => $student->only(['full_name', 'nisn', 'nis']),
            'result' => $result
        ], 'Graduation data retrieved.');
    }

    public function downloadCertificate(): mixed
    {
        $student = $this->getStudent();
        $result = GraduationResult::where('student_id', $student->id)
            ->where('status', 'graduated')
            ->latest('graduation_year')
            ->firstOrFail();

        $template = DocumentTemplate::where('school_id', $student->school_id)
            ->where('type', 'skl')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return $this->sendError('Template sertifikat belum diatur oleh sekolah.', [], 404);
        }

        $data = array_merge($student->toArray(), [
            'graduation_year' => $result->graduation_year,
            'certificate_number' => $result->certificate_number,
            'grades' => $result->grades,
            'published_date' => $result->published_at ? $result->published_at->format('d F Y') : now()->format('d F Y'),
        ]);

        $pdfBinary = $this->documentService->generatePdf($template, $data);

        return response($pdfBinary)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="SKL_' . $student->nisn . '.pdf"');
    }
}
