<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Services\Lms\LmsService;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\Topic;

class AdminLmsController extends BaseController
{
    protected LmsService $service;

    public function __construct(LmsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Course::class);

        $schoolId = $request->header('X-School-Id');
        if (!$schoolId) {
            $school = \Modules\School\Models\Institution\School::first();
            $schoolId = $school ? $school->id : 1;
        }

        \Illuminate\Support\Facades\Log::info('LMS Index Request', [
            'header_school_id' => $request->header('X-School-Id'),
            'resolved_school_id' => $schoolId,
            'user_id' => auth()->id()
        ]);

        $courses = $this->service->getCourses((int)$schoolId, $request->all());
        return $this->sendResponse($courses, 'Courses retrieved successfully.');
    }

    public function storeCourse(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'level' => 'required|in:beginner,intermediate,advanced',
            'status' => 'required|in:draft,published,archived',
        ]);

        $schoolId = $request->header('X-School-Id');
        if (!$schoolId) {
            $school = \Modules\School\Models\Institution\School::first();
            $schoolId = $school ? $school->id : 1;
        }

        $validated['school_id'] = (int) $schoolId;
        $validated['author_id'] = (int) auth()->id();

        $course = $this->service->createCourse($validated);
        return $this->sendResponse($course, 'Course created successfully.', 201);
    }

    public function showCourse(int $id): \Illuminate\Http\JsonResponse
    {
        $course = Course::with(['lessons.topics.topicable' => function ($morph) {
            $morph->morphWith([
                \Modules\School\Models\Lms\TopicContent\Quiz::class => ['questions.options'],
            ]);
        }])->findOrFail($id);
        
        $this->authorize('view', $course);
        return $this->sendResponse($course, 'Course details retrieved.');
    }

    public function storeLesson(Request $request, int $courseId): \Illuminate\Http\JsonResponse
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

    public function storeTopic(Request $request, int $lessonId): \Illuminate\Http\JsonResponse
    {
        $lesson = Lesson::findOrFail($lessonId);
        $this->authorize('update', $lesson->course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:richtext,video,pdf,quiz',
            'content' => 'required|array',
            'content.value' => 'required_unless:type,quiz|string',
            'content.pass_score' => 'required_if:type,quiz|integer|min:0|max:100',
            'content.time_limit' => 'required_if:type,quiz|integer|min:0',
            'order' => 'integer|min:0',
        ]);

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

    public function storeQuestion(Request $request, int $quizId): \Illuminate\Http\JsonResponse
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
