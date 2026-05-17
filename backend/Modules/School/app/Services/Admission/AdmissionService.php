<?php

namespace Modules\School\Services\Admission;

use Modules\School\Models\Admission\Enrollment;
use Modules\School\Models\Admission\EnrollmentDocument;
use Modules\School\Models\Student\Student;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class AdmissionService
{
    /**
     * Get enrollments with filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Enrollment>
     */
    public function getEnrollments(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Enrollment::with(['documents', 'academicYear']);
        
        if (!empty($filters['status']) && is_string($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $searchStr = strtolower($search);
                $q->where(\Illuminate\Support\Facades\DB::raw('lower(full_name)'), 'like', '%' . $searchStr . '%')
                  ->orWhere(\Illuminate\Support\Facades\DB::raw('lower(registration_number)'), 'like', '%' . $searchStr . '%');
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new enrollment.
     *
     * @param array<string, mixed> $data
     */
    public function createEnrollment(array $data): Enrollment
    {
        $data['registration_number'] = 'REG-' . strtoupper(Str::random(10));
        $data['status'] = 'applied';

        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::create($data);
        return $enrollment;
    }

    /**
     * Admit an enrollment as a student.
     *
     * @throws \Exception
     */
    public function admitStudent(Enrollment $enrollment): Student
    {
        if ($enrollment->status === 'admitted') {
            throw new \Exception('Enrollment is already admitted.');
        }

        // Convert enrollment to student
        /** @var Student $student */
        $student = Student::create([
            'school_id' => $enrollment->school_id,
            'workspace_id' => $enrollment->workspace_id,
            'full_name' => $enrollment->full_name,
            'gender' => $enrollment->gender,
            'place_of_birth' => $enrollment->place_of_birth,
            'date_of_birth' => $enrollment->date_of_birth,
            'nisn' => $enrollment->nisn,
            'phone' => $enrollment->phone,
            'email' => $enrollment->email,
        ]);

        $enrollment->update([
            'status' => 'admitted',
            'admitted_student_id' => $student->id
        ]);

        return $student;
    }
}
