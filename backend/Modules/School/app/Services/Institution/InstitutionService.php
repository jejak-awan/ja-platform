<?php

namespace Modules\School\Services\Institution;

use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Student\Student;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class InstitutionService
{
    /**
     * Get a paginated list of schools.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, School>
     */
    public function getAllSchools(int $limit = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return School::latest()->paginate($limit);
    }

    /**
     * Create a new school and handle initial setup.
     *
     * @param array<string, mixed> $data
     */
    public function createSchool(array $data): School
    {
        $schoolData = collect($data)->except(['initial_level', 'initial_level_name'])->toArray();
        /** @var School $school */
        $school = School::create($schoolData);

        // Auto-create initial level if single-level or if initial_level is provided
        if (!$school->is_multi_unit || isset($data['initial_level'])) {
            $initialLevel = $data['initial_level'] ?? 'smk';
            $initialLevelName = $data['initial_level_name'] ?? $school->name;
            $school->levels()->create([
                'level' => is_string($initialLevel) ? $initialLevel : 'smk',
                'name' => is_string($initialLevelName) ? $initialLevelName : $school->name,
            ]);
        }

        // Clear stats cache for new school
        Cache::forget("school_stats_{$school->id}");

        return $school;
    }

    /**
     * Update school profile.
     *
     * @param array<string, mixed> $data
     */
    public function updateSchool(School $school, array $data): School
    {
        $school->update($data);

        // Clear cached data for this school
        Cache::forget("school_stats_{$school->id}");
        Cache::forget("school_setup_{$school->id}");

        return $school;
    }

    /**
     * Get school statistics with caching (5 minute TTL).
     *
     * @return array{stats: array<int, array{title: string, value: string, icon: string}>, personnel: array<int, array{initials: string, name: string, role: string, time: string, statusKey: string}>, alerts: array<int, array{id: int, title: string, status: string, icon: string}>}
     */
    public function getSchoolStats(int $schoolId): array
    {
        $rawUnitId = \Illuminate\Support\Facades\Context::get('school_unit_id');
        $unitId = is_numeric($rawUnitId) ? (int) $rawUnitId : 0;
        $cacheKey = "school_stats_{$schoolId}_unit_{$unitId}";

        /** @var array{stats: array<int, array{title: string, value: string, icon: string}>, personnel: array<int, array{initials: string, name: string, role: string, time: string, statusKey: string}>, alerts: array<int, array{id: int, title: string, status: string, icon: string}>} $result */
        $result = Cache::remember($cacheKey, 300, function () use ($schoolId, $unitId) {
            $queryStudents = Student::query()->where('school_id', $schoolId);
            $queryStaff = Staff::query()->where('school_id', $schoolId);
            $queryStudyGroups = StudyGroup::query()->where('school_id', $schoolId);

            // If global mode, bypass unit scoping to get aggregate totals
            if ($unitId === 0) {
                $queryStudents->withoutGlobalScope('school_unit');
                $queryStaff->withoutGlobalScope('school_unit');
                $queryStudyGroups->withoutGlobalScope('school_unit');
            }

            $stats = [
                ['title' => $unitId === 0 ? 'Total Siswa (Ecosystem)' : 'Total Siswa', 'value' => (string) $queryStudents->count(), 'icon' => 'Users'],
                ['title' => $unitId === 0 ? 'Total Guru/PTK (Ecosystem)' : 'Total Guru/PTK', 'value' => (string) $queryStaff->count(), 'icon' => 'UserSquare'],
                ['title' => 'Rombel', 'value' => (string) $queryStudyGroups->count(), 'icon' => 'Layers'],
                ['title' => 'Aset Sarpras', 'value' => (string) \Modules\School\Models\Logistics\SchoolAsset::query()->where('school_id', $schoolId)->count(), 'icon' => 'Package'],
            ];

            // Get recent personnel presence
            $personnel = $queryStaff->limit(4)->get()
                ->map(function (Staff $staff) {
                    $names = explode(' ', $staff->full_name);
                    $firstName = $names[0];
                    $initials = substr($firstName, 0, 1) . substr($names[1] ?? $firstName, 0, 1);
                    return [
                        'initials' => strtoupper($initials),
                        'name' => $staff->full_name,
                        'role' => $staff->ptk_type ?? 'Guru',
                        'time' => '07:00',
                        'statusKey' => 'present'
                    ];
                })->toArray();

            $alerts = [
                ['id' => 1, 'title' => 'Realisasi Anggaran Melampaui 90%', 'status' => 'Urgent • Finance', 'icon' => 'AlertTriangle'],
                ['id' => 2, 'title' => 'Pembaruan Data Dapodik', 'status' => 'Open • Info', 'icon' => 'Info'],
            ];

            return [
                'stats' => $stats,
                'personnel' => $personnel,
                'alerts' => $alerts
            ];
        });

        return $result;
    }

    /**
     * Check school setup completion status with caching (5 minute TTL).
     *
     * @return array{steps: array<string, bool>, is_completed: bool, progress: float|int}
     */
    public function getSetupStatus(int $schoolId): array
    {
        /** @var array{steps: array<string, bool>, is_completed: bool, progress: float|int} $result */
        $result = Cache::remember("school_setup_{$schoolId}", 300, function () use ($schoolId) {
            $school = School::with(['levels', 'activeAcademicYear.semesters'])->findOrFail($schoolId);
            $levelIds = $school->levels->pluck('id');

            $status = [
                'school_profile' => !empty($school->npsn) && !empty($school->address),
                'school_units' => $levelIds->count() > 0,
                'academic_year' => $school->activeAcademicYear !== null,
                'semesters' => $school->activeAcademicYear?->semesters->count() > 0,
                'departments' => Department::query()->whereIn('school_unit_id', $levelIds)->count() > 0,
                'study_groups' => StudyGroup::query()->where('school_id', $schoolId)->count() > 0,
            ];

            $isCompleted = !in_array(false, array_values($status), true);
            $totalSteps = count($status);
            $completedSteps = count(array_filter($status));

            // Hardcoded 6 steps logic for Level 9 compliance
            $progress = ($completedSteps / 6.0) * 100.0;

            return [
                'steps' => $status,
                'is_completed' => $isCompleted,
                'progress' => $progress
            ];
        });

        return $result;
    }
}

