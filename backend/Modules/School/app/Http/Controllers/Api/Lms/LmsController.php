<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\Core\Models\User;
use Modules\School\Models\Lms\QuestionBank;
use Modules\School\Models\Lms\Exam;
use Modules\School\Models\Lms\ExamResult;
use Modules\School\Models\Lms\Question;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Section;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\Enrollment;
use Modules\School\Models\Lms\LessonProgress;
use Modules\School\Services\Lms\LmsService;
use Modules\School\Http\Requests\Lms\StoreQuestionBankRequest;
use Modules\School\Http\Requests\Lms\StoreExamRequest;
use Modules\School\Http\Requests\Lms\StoreExamResultRequest;
use Modules\School\Http\Requests\Lms\StoreQuestionRequest;

class LmsController extends BaseController
{
    protected LmsService $service;

    public function __construct(LmsService $service)
    {
        $this->service = $service;
    }

    public function overview(): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', QuestionBank::class);
        return $this->sendResponse([
            'banks_count' => (int) QuestionBank::count(),
            'exams_count' => (int) Exam::count(),
        ], 'LMS overview retrieved successfully.');
    }

    // --- Courses ---

    public function courses(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $schoolIdValue = $request->header('X-School-Id') ?? ($filters['school_id'] ?? 1);
        $filters['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($filters['academic_year_id'] ?? '');
        $filters['academic_year_id'] = is_string($yearValue) ? $yearValue : (is_scalar($yearValue) ? (string)$yearValue : '');
        
        $courses = $this->service->getCourses($filters);
        return $this->sendResponse($courses, 'Courses retrieved successfully.');
    }

    public function showCourse(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = $this->service->getCourseDetail($id);
        $this->authorize('view', $course);

        return $this->sendResponse($course, 'Course detail retrieved successfully.');
    }

    public function storeCourse(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Course::class);

        /** @var array{school_id?: int, subject_id?: int|null, title: string, slug: string, description?: string|null, status: string, level: string, academic_year_id?: string|int, sections?: array<int, array<string, mixed>>} $validated */
        $validated = $request->validate([
            'school_id' => 'sometimes|integer',
            'subject_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:sch_lms_courses,slug',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'level' => 'required|in:beginner,intermediate,advanced',
            'sections' => 'nullable|array',
            'sections.*.title' => 'required_with:sections|string|max:255',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.title' => 'required_with:sections.*.lessons|string|max:255',
            'sections.*.lessons.*.type' => 'required_with:sections.*.lessons|in:video,text,quiz,assignment,file',
        ]);

        $validated['author_id'] = (int) auth()->id();
        $schoolIdValue = $request->header('X-School-Id') ?? ($validated['school_id'] ?? 1);
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($validated['academic_year_id'] ?? '');
        $validated['academic_year_id'] = is_string($yearValue) ? $yearValue : (string)$yearValue;

        /** @var array<string, mixed> $courseData */
        $courseData = $validated;
        $course = $this->service->createCourse($courseData);
        return $this->sendResponse($course, 'Course created successfully.', 201);
    }

    public function updateCourse(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = Course::findOrFail($id);
        $this->authorize('update', $course);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:sch_lms_courses,slug,'.$id,
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,archived',
            'level' => 'sometimes|in:beginner,intermediate,advanced',
            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer',
            'sections.*.title' => 'sometimes|string|max:255',
            'sections.*.lessons' => 'nullable|array',
            'sections.*.lessons.*.id' => 'nullable|integer',
            'sections.*.lessons.*.title' => 'sometimes|string|max:255',
            'sections.*.lessons.*.type' => 'sometimes|in:video,text,quiz,assignment,file',
            'sections.*.lessons.*.exam_id' => 'nullable|integer',
        ]);

        $course = $this->service->updateCourse($course, $validated);
        return $this->sendResponse($course, 'Course updated successfully.');
    }

    public function destroyCourse(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = Course::findOrFail($id);
        $this->authorize('delete', $course);
        $course->delete();
        return $this->sendResponse([], 'Course deleted successfully.');
    }

    // --- Sections & Lessons ---

    public function storeSection(Request $request, int $courseId): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = Course::findOrFail($courseId);
        $this->authorize('update', $course);

        /** @var array{title: string, sort_order?: int} $validated */
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'sort_order' => 'integer',
        ]);

        /** @var array<string, mixed> $sectionData */
        $sectionData = $validated;
        $section = $this->service->createSection($course, $sectionData);
        return $this->sendResponse($section, 'Section created successfully.', 201);
    }

    public function storeLesson(Request $request, int $sectionId): \Illuminate\Http\JsonResponse
    {
        /** @var Section $section */
        $section = Section::with('course')->findOrFail($sectionId);
        $this->authorize('update', $section->course);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string',
            'type' => 'required|in:video,text,quiz,assignment,file',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'exam_id' => 'nullable|integer',
            'sort_order' => 'integer',
        ]);

        $lesson = $this->service->createLesson($section, $validated);
        return $this->sendResponse($lesson, 'Lesson created successfully.', 201);
    }

    // --- Enrollment & Progress ---

    public function enroll(Request $request, int $courseId): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = Course::findOrFail($courseId);
        $this->authorize('enroll', Course::class);

        /** @var User $user */
        $user = auth()->user(); // @phpstan-ignore-line
        /** @var int $studentId */
        $studentId = (int) ($user->student_id ?? 1); 

        /** @var string|null $academicYearRaw */
        $academicYearRaw = $request->header('X-Academic-Year-Id');
        $academicYearId = $academicYearRaw ? (int)$academicYearRaw : null;

        $enrollment = $this->service->enrollStudent($course, $studentId, $academicYearId);
        return $this->sendResponse($enrollment, 'Enrolled successfully.');
    }

    public function myEnrollment(int $courseId): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = auth()->user(); // @phpstan-ignore-line
        /** @var int $studentId */
        $studentId = (int) ($user->student_id ?? 1);

        $enrollment = $this->service->getStudentEnrollment($courseId, $studentId);
        
        if (!$enrollment) {
            return $this->sendError('Enrollment not found.', [], 404);
        }

        return $this->sendResponse($enrollment, 'Enrollment retrieved successfully.');
    }

    public function completeLesson(int $enrollmentId, int $lessonId): \Illuminate\Http\JsonResponse
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $this->authorize('update', $enrollment);

        $progress = $this->service->updateLessonProgress($enrollment, $lessonId, true);
        return $this->sendResponse($progress, 'Lesson marked as completed.');
    }

    // --- Teacher Management ---

    public function teacherStats(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewStats', Course::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $schoolIdValue = $request->header('X-School-Id') ?? ($filters['school_id'] ?? 1);
        $filters['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($filters['academic_year_id'] ?? '');
        $filters['academic_year_id'] = is_string($yearValue) ? $yearValue : (is_scalar($yearValue) ? (string)$yearValue : '');

        $stats = $this->service->getTeacherStats((int)auth()->id(), $filters);
        return $this->sendResponse($stats, 'Teacher statistics retrieved successfully.');
    }

    public function teacherCourses(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $filters['author_id'] = (int) auth()->id();
        $courses = $this->service->getCourses($filters);
        return $this->sendResponse($courses, 'Teacher courses retrieved successfully.');
    }

    public function courseMonitoring(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Course $course */
        $course = Course::findOrFail($id);
        /** @var User $user */
        $user = auth()->user(); // @phpstan-ignore-line

        if ((int)$course->author_id !== (int)$user->id && !$user->hasRole('admin')) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $monitoring = $this->service->getCourseMonitoring($id);
        return $this->sendResponse($monitoring, 'Course monitoring data retrieved successfully.');
    }

    // --- Question Banks ---

    public function questionBanks(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', QuestionBank::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $schoolIdValue = $request->header('X-School-Id') ?? ($filters['school_id'] ?? 1);
        $filters['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($filters['academic_year_id'] ?? '');
        $filters['academic_year_id'] = is_string($yearValue) ? $yearValue : (is_scalar($yearValue) ? (string)$yearValue : '');

        $banks = $this->service->getQuestionBanks($filters);
        return $this->sendResponse($banks, 'Question banks retrieved successfully.');
    }

    public function storeQuestionBank(StoreQuestionBankRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', QuestionBank::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $schoolIdValue = $request->header('X-School-Id') ?? ($validated['school_id'] ?? 1);
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($validated['academic_year_id'] ?? '');
        $validated['academic_year_id'] = is_numeric($yearValue) || is_string($yearValue) ? (string)$yearValue : '';

        $bank = $this->service->createQuestionBank($validated);
        return $this->sendResponse($bank, 'Question bank created successfully.', 201);
    }

    public function updateQuestionBank(StoreQuestionBankRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var QuestionBank $bank */
        $bank = QuestionBank::findOrFail($id);
        $this->authorize('update', $bank);

        $bank = $this->service->updateQuestionBank($bank, $request->validated());
        return $this->sendResponse($bank, 'Question bank updated successfully.');
    }

    public function destroyQuestionBank(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var QuestionBank $bank */
        $bank = QuestionBank::findOrFail($id);
        $this->authorize('delete', $bank);
        $bank->delete();
        return $this->sendResponse([], 'Question bank deleted successfully.');
    }

    // --- Exams ---

    public function exams(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Exam::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $schoolIdValue = $request->header('X-School-Id') ?? ($filters['school_id'] ?? 1);
        $filters['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($filters['academic_year_id'] ?? '');
        $filters['academic_year_id'] = is_string($yearValue) ? $yearValue : (is_scalar($yearValue) ? (string)$yearValue : '');

        $exams = $this->service->getExams($filters);
        return $this->sendResponse($exams, 'Exams retrieved successfully.');
    }

    public function storeExam(StoreExamRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Exam::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $schoolIdValue = $request->header('X-School-Id') ?? ($validated['school_id'] ?? 1);
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $yearValue = $request->header('X-Academic-Year-Id') ?? ($validated['academic_year_id'] ?? '');
        $validated['academic_year_id'] = is_numeric($yearValue) || is_string($yearValue) ? (string)$yearValue : '';

        $exam = $this->service->createExam($validated);
        return $this->sendResponse($exam, 'Exam created successfully.', 201);
    }

    public function updateExam(StoreExamRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Exam $exam */
        $exam = Exam::findOrFail($id);
        $this->authorize('update', $exam);

        $exam = $this->service->updateExam($exam, $request->validated());
        return $this->sendResponse($exam, 'Exam updated successfully.');
    }

    public function destroyExam(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Exam $exam */
        $exam = Exam::findOrFail($id);
        $this->authorize('delete', $exam);
        $exam->delete();
        return $this->sendResponse([], 'Exam deleted successfully.');
    }

    public function examQuestions(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Exam $exam */
        $exam = Exam::findOrFail($id);
        $this->authorize('view', $exam);

        $questions = $this->service->getExamQuestions($exam);
        return $this->sendResponse($questions, 'Exam questions retrieved successfully.');
    }

    public function syncExamQuestions(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Exam $exam */
        $exam = Exam::findOrFail($id);
        $this->authorize('update', $exam);

        /** @var array{questions: array<int, array{question_id: int, sort_order?: int, points?: float}>} $validated */
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question_id' => 'required|integer|exists:sch_lms_questions,id',
            'questions.*.sort_order' => 'nullable|integer',
            'questions.*.points' => 'nullable|numeric',
        ]);

        $this->service->syncExamQuestions($exam, $validated['questions']);
        return $this->sendResponse([], 'Exam questions synced successfully.');
    }

    // --- Exam Results ---

    public function examResults(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', ExamResult::class);

        /** @var array<string, mixed> $allInputs */
        $allInputs = $request->all();
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        $results = $this->service->getExamResults($allInputs, $perPage);
        return $this->sendResponse($results, 'Exam results retrieved successfully.');
    }

    public function storeExamResult(StoreExamResultRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', ExamResult::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $result = $this->service->createExamResult($validated);
        return $this->sendResponse($result, 'Exam result recorded and graded successfully.', 201);
    }

    public function updateExamResult(StoreExamResultRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var ExamResult $result */
        $result = ExamResult::findOrFail($id);
        $this->authorize('update', $result);

        $result = $this->service->updateExamResult($result, $request->validated());
        return $this->sendResponse($result, 'Exam result updated and re-graded successfully.');
    }

    public function destroyExamResult(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var ExamResult $result */
        $result = ExamResult::findOrFail($id);
        $this->authorize('delete', $result);
        $result->delete();
        return $this->sendResponse([], 'Exam result deleted successfully.');
    }

    // --- Questions ---

    public function questions(int $bankId): \Illuminate\Http\JsonResponse
    {
        /** @var QuestionBank $bank */
        $bank = QuestionBank::findOrFail($bankId);
        $this->authorize('view', $bank);

        $questions = $this->service->getQuestionByBank($bankId);
        return $this->sendResponse($questions, 'Questions retrieved successfully.');
    }

    public function storeQuestion(StoreQuestionRequest $request, int $bankId): \Illuminate\Http\JsonResponse
    {
        /** @var QuestionBank $bank */
        $bank = QuestionBank::findOrFail($bankId);
        $this->authorize('update', $bank);

        $question = $this->service->createQuestion($bank, $request->validated());
        return $this->sendResponse($question, 'Question created successfully.', 201);
    }

    public function updateQuestion(StoreQuestionRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Question $question */
        $question = Question::findOrFail($id);
        $this->authorize('update', $question);

        $question = $this->service->updateQuestion($question, $request->validated());
        return $this->sendResponse($question, 'Question updated successfully.');
    }

    public function destroyQuestion(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var Question $question */
        $question = Question::findOrFail($id);
        $this->authorize('delete', $question);
        $question->delete();
        return $this->sendResponse([], 'Question deleted successfully.');
    }
}
