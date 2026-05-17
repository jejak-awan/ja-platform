<?php

namespace Modules\School\Http\Controllers\Api\Academic;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Subject;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Academic\Schedule;
use Modules\School\Models\Academic\TeachingJournal;
use Modules\School\Models\Academic\Department;
use Modules\School\Services\Academic\AcademicService;
use Modules\School\Http\Requests\Academic\StoreScheduleRequest;
use Modules\School\Http\Requests\Academic\StoreJournalRequest;
use Illuminate\Support\Facades\Storage;

class AcademicController extends BaseController
{
    public function __construct(protected AcademicService $service)
    {
    }

    public function overview(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', AcademicYear::class);
        $stats = $this->service->getOverviewStats();
        return $this->sendResponse($stats, 'Academic overview retrieved successfully.');
    }

    // --- Academic Years ---

    public function years(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', AcademicYear::class);
        $years = $this->service->getAllYears();
        return $this->sendResponse($years, 'Academic years retrieved successfully.');
    }

    public function storeYear(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', AcademicYear::class);

        /** @var array{year: string, is_active?: bool} $validated */
        $validated = $request->validate([
            'year' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        /** @var int|string $schoolId */
        $schoolId = $request->header('X-School-Id') ?? 1;
        
        $yearData = array_merge($validated, ['school_id' => $schoolId]);
        /** @var array<string, mixed> $yearData */
        $year = $this->service->createYear($yearData);

        return $this->sendResponse($year, 'Academic year created successfully.', 201);
    }

    public function updateYear(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var AcademicYear $year */
        $year = AcademicYear::findOrFail($id);
        $this->authorize('update', $year);

        /** @var array{year: string, is_active?: bool} $validated */
        $validated = $request->validate([
            'year' => 'required|string|max:50',
            'is_active' => 'boolean',
        ]);

        $year->update($validated);

        return $this->sendResponse($year, 'Academic year updated successfully.');
    }

    public function destroyYear(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var AcademicYear $year */
        $year = AcademicYear::findOrFail($id);
        $this->authorize('delete', $year);
        $year->delete();

        return $this->sendResponse([], 'Academic year deleted successfully.');
    }

    // --- Semesters ---

    public function semesters(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Semester::class);
        $ayId = (string) $request->string('academic_year_id');
        $semesters = $this->service->getSemesters($ayId);
        return $this->sendResponse($semesters, 'Semesters retrieved successfully.');
    }

    public function storeSemester(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Semester::class);

        /** @var array{academic_year_id: int, type: string, is_active?: bool} $validated */
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'type' => 'required|in:Ganjil,Genap',
            'is_active' => 'boolean',
        ]);

        /** @var array<string, mixed> $validatedArray */
        $validatedArray = $validated;
        $semester = $this->service->createSemester($validatedArray);

        return $this->sendResponse($semester, 'Semester created successfully.', 201);
    }

    public function updateSemester(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Semester $semester */
        $semester = Semester::findOrFail($id);
        $this->authorize('update', $semester);

        /** @var array{type: string, is_active?: bool} $validated */
        $validated = $request->validate([
            'type' => 'required|in:Ganjil,Genap',
            'is_active' => 'boolean',
        ]);

        $semester->update($validated);

        return $this->sendResponse($semester, 'Semester updated successfully.');
    }

    public function destroySemester(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Semester $semester */
        $semester = Semester::findOrFail($id);
        $this->authorize('delete', $semester);
        $semester->delete();

        return $this->sendResponse([], 'Semester deleted successfully.');
    }

    // --- Subjects ---

    public function subjects(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Subject::class);
        $subjects = Subject::all();
        return $this->sendResponse($subjects, 'Subjects retrieved successfully.');
    }

    public function storeSubject(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Subject::class);

        /** @var array{school_id: int, code: string, name: string, group?: string|null, kkm?: int|null} $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'group' => 'nullable|string|max:50',
            'kkm' => 'nullable|integer|min:0|max:100',
        ]);

        $subject = Subject::create($validated);

        return $this->sendResponse($subject, 'Subject created successfully.', 201);
    }

    public function updateSubject(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Subject $subject */
        $subject = Subject::findOrFail($id);
        $this->authorize('update', $subject);

        /** @var array{code: string, name: string, group?: string|null, kkm?: int|null} $validated */
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'group' => 'nullable|string|max:50',
            'kkm' => 'nullable|integer|min:0|max:100',
        ]);

        $subject->update($validated);

        return $this->sendResponse($subject, 'Subject updated successfully.');
    }

    public function destroySubject(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Subject $subject */
        $subject = Subject::findOrFail($id);
        $this->authorize('delete', $subject);
        $subject->delete();

        return $this->sendResponse([], 'Subject deleted successfully.');
    }

    // --- Study Groups ---

    public function studyGroups(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', StudyGroup::class);
        $groups = $this->service->getAllStudyGroups();
        return $this->sendResponse($groups, 'Study groups retrieved successfully.');
    }

    public function storeStudyGroup(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', StudyGroup::class);

        /** @var array{school_id: int, workspace_id: int, department_id?: int|null, academic_year_id: int, homeroom_teacher_id?: int|null, name: string} $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'department_id' => 'nullable|exists:sch_acad_departments,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'homeroom_teacher_id' => 'nullable|exists:sch_hr_staff,id',
            'name' => 'required|string|max:150',
        ]);

        /** @var array<string, mixed> $validatedArray */
        $validatedArray = $validated;
        $group = $this->service->createStudyGroup($validatedArray);

        return $this->sendResponse($group, 'Study group created successfully.', 201);
    }

    public function updateStudyGroup(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::findOrFail($id);
        $this->authorize('update', $group);

        /** @var array{workspace_id: int, department_id?: int|null, academic_year_id: int, homeroom_teacher_id?: int|null, name: string} $validated */
        $validated = $request->validate([
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'department_id' => 'nullable|exists:sch_acad_departments,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'homeroom_teacher_id' => 'nullable|exists:sch_hr_staff,id',
            'name' => 'required|string|max:150',
        ]);

        $group->update($validated);

        return $this->sendResponse($group, 'Study group updated successfully.');
    }

    public function destroyStudyGroup(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::findOrFail($id);
        $this->authorize('delete', $group);
        $group->delete();

        return $this->sendResponse([], 'Study group deleted successfully.');
    }

    // --- Study Group Members ---

    public function groupMembers(string $groupId): \Illuminate\Http\JsonResponse
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::with('students')->findOrFail($groupId);
        $this->authorize('view', $group);

        return $this->sendResponse($group->students, 'Study group members retrieved successfully.');
    }

    public function addGroupMember(Request $request, string $groupId): \Illuminate\Http\JsonResponse
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::findOrFail($groupId);
        $this->authorize('update', $group);

        /** @var array{student_id: int} $validated */
        $validated = $request->validate([
            'student_id' => 'required|exists:sch_std_students,id',
        ]);

        $group->students()->syncWithoutDetaching([(string)$validated['student_id']]);

        return $this->sendResponse([], 'Student added to study group successfully.');
    }

    public function removeGroupMember(Request $request, string $groupId, string $studentId): \Illuminate\Http\JsonResponse
    {
        /** @var StudyGroup $group */
        $group = StudyGroup::findOrFail($groupId);
        $this->authorize('update', $group);

        $group->students()->detach($studentId);

        return $this->sendResponse([], 'Student removed from study group successfully.');
    }

    // --- Departments ---

    public function departments(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $schoolIdValue = $request->input('school_id', 1);
        $schoolId = is_scalar($schoolIdValue) ? (string) $schoolIdValue : '1';
        $departments = Department::whereHas('level', function ($q) use ($schoolId): void {
            $q->where('school_id', $schoolId);
        })->with('level')->get();

        return $this->sendResponse($departments, 'Departments retrieved successfully.');
    }

    public function storeDepartment(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Department::class);

        /** @var array{workspace_id: int, name: string, code: string, description?: string|null} $validated */
        $validated = $request->validate([
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($validated);

        return $this->sendResponse($department, 'Department created successfully.', 201);
    }

    public function updateDepartment(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Department $department */
        $department = Department::findOrFail($id);
        $this->authorize('update', $department);

        /** @var array{workspace_id: int, name: string, code: string, description?: string|null} $validated */
        $validated = $request->validate([
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $department->update($validated);

        return $this->sendResponse($department, 'Department updated successfully.');
    }

    public function destroyDepartment(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Department $department */
        $department = Department::findOrFail($id);
        $this->authorize('delete', $department);
        $department->delete();

        return $this->sendResponse([], 'Department deleted successfully.');
    }

    public function schedules(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Schedule::class);
        /** @var array<string, mixed> $allInputs */
        $allInputs = $request->all();
        $schedules = $this->service->getSchedules($allInputs);
        return $this->sendResponse($schedules, 'Schedules retrieved successfully.');
    }

    public function storeSchedule(StoreScheduleRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Schedule::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $collisions = $this->service->detectCollisions($validated);
        if ($collisions !== []) {
            return $this->sendError('Jadwal bentrok ditemukan.', $collisions, 422);
        }

        $schedule = $this->service->createSchedule($validated);

        return $this->sendResponse($schedule, 'Schedule created successfully.', 201);
    }

    public function updateSchedule(StoreScheduleRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Schedule $schedule */
        $schedule = Schedule::findOrFail($id);
        $this->authorize('update', $schedule);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $collisions = $this->service->detectCollisions($validated, $id);
        if ($collisions !== []) {
            return $this->sendError('Jadwal bentrok ditemukan.', $collisions, 422);
        }

        $schedule->update($validated);

        return $this->sendResponse($schedule, 'Schedule updated successfully.');
    }

    public function destroySchedule(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var Schedule $schedule */
        $schedule = Schedule::findOrFail($id);
        $this->authorize('delete', $schedule);
        $schedule->delete();

        return $this->sendResponse([], 'Schedule deleted successfully.');
    }

    // --- Teaching Journals ---

    public function journals(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', TeachingJournal::class);
        $query = TeachingJournal::with('schedule.subject', 'schedule.staff');

        if ($request->has('schedule_id')) {
            $schIdValue = (string) $request->string('schedule_id');
            $query->where('schedule_id', $schIdValue);
        }
        if ($request->has('date')) {
            $dateValue = $request->input('date');
            $query->whereDate('date', is_scalar($dateValue) ? (string)$dateValue : '');
        }

        return $this->sendResponse($query->get(), 'Teaching journals retrieved successfully.');
    }

    public function storeJournal(StoreJournalRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', TeachingJournal::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        /** @var \Illuminate\Http\UploadedFile|null $file */
        $file = $request->file('evidence');
        $journal = $this->service->createJournal($validated, $file);

        return $this->sendResponse($journal, 'Teaching journal created successfully.', 201);
    }

    public function updateJournal(StoreJournalRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        /** @var TeachingJournal $journal */
        $journal = TeachingJournal::findOrFail($id);
        $this->authorize('update', $journal);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        /** @var \Illuminate\Http\UploadedFile|null $file */
        $file = $request->file('evidence');
        $journal = $this->service->updateJournal($journal, $validated, $file);

        return $this->sendResponse($journal, 'Teaching journal updated successfully.');
    }

    public function destroyJournal(string $id): \Illuminate\Http\JsonResponse
    {
        /** @var TeachingJournal $journal */
        $journal = TeachingJournal::findOrFail($id);
        $this->authorize('delete', $journal);
        $journal->delete();

        return $this->sendResponse([], 'Teaching journal deleted successfully.');
    }
}
