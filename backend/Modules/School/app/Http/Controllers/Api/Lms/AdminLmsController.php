<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\TopicContent\Quiz;
use Modules\School\Services\Lms\LmsService;

class AdminLmsController extends BaseController
{
    public function __construct(protected LmsService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        $schoolId = $request->header('X-School-Id');
        if (! $schoolId) {
            $school = School::first();
            $schoolId = $school ? $school->id : '1';
        }

        Log::info('LMS Index Request', [
            'header_school_id' => $request->header('X-School-Id'),
            'resolved_school_id' => $schoolId,
            'user_id' => auth()->id(),
        ]);

        $courses = $this->service->getCourses((string) $schoolId, $request->all());

        return $this->sendResponse($courses, 'Courses retrieved successfully.');
    }

    public function storeCourse(Request $request): JsonResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'status' => 'required|in:draft,published,archived',
            'academic_year_id' => 'nullable|string|exists:sch_acad_years,id',
            'semester_id' => 'nullable|string|exists:sch_acad_semesters,id',
            'department_id' => 'nullable|string|exists:sch_acad_departments,id',
            'grade_id' => 'nullable|string|exists:sch_acad_grades,id',
        ]);

        $schoolId = $request->header('X-School-Id');
        if (! $schoolId) {
            $school = School::first();
            $schoolId = $school ? $school->id : '1';
        }

        $validated['school_id'] = (string) $schoolId;
        $validated['author_id'] = (string) auth()->id();

        $course = $this->service->createCourse($validated);

        return $this->sendResponse($course, 'Course created successfully.', 201);
    }

    public function showCourse(string $id): JsonResponse
    {
        $course = Course::with(['lessons.topics.topicable' => function ($morph): void {
            $morph->morphWith([
                Quiz::class => ['questions.options'],
            ]);
        }])->findOrFail($id);

        $this->authorize('view', $course);

        return $this->sendResponse($course, 'Course details retrieved.');
    }

    public function storeLesson(Request $request, string $courseId): JsonResponse
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'order' => 'integer|min:0',
        ]);

        $validated['course_id'] = $courseId;
        $lesson = $this->service->createLesson($validated);

        return $this->sendResponse($lesson, 'Lesson created successfully.', 201);
    }

    public function storeTopic(Request $request, string $lessonId): JsonResponse
    {
        $lesson = Lesson::findOrFail($lessonId);
        $this->authorize('update', $lesson->course);

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:richtext,video,pdf,quiz',
                'content' => 'required|array',
                'content.value' => 'required_unless:type,quiz|string',
                'content.pass_score' => 'required_if:type,quiz|integer|min:0|max:100',
                'content.time_limit' => 'required_if:type,quiz|integer|min:0',
                'order' => 'integer|min:0',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Topic Validation Failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'lesson_id' => $lessonId,
            ]);
            throw $e;
        }

        $topic = $this->service->createTopic(
            [
                'lesson_id' => $lessonId,
                'title' => $validated['title'],
                'order' => $validated['order'] ?? 0,
            ],
            $validated['type'],
            $validated['content']
        );

        return $this->sendResponse($topic, 'Topic created successfully.', 201);
    }

    public function storeQuestion(Request $request, string $quizId): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'value' => 'required|string',
            'score' => 'integer|min:1',
            'order' => 'integer|min:0',
            'options' => 'required_if:type,multiple_choice,true_false|array',
            'options.*.value' => 'required|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question = $this->service->addQuestionToQuiz($quizId, $validated, $validated['options'] ?? []);

        return $this->sendResponse($question, 'Question added to quiz.', 201);
    }
}
