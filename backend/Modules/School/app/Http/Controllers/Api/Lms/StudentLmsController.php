<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Services\Lms\LmsService;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Lms\Lesson;

class StudentLmsController extends BaseController
{
    protected LmsService $service;

    public function __construct(LmsService $service)
    {
        $this->service = $service;
    }

    public function myCourses(): \Illuminate\Http\JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (!$student) return $this->sendError('Student not found.', [], 404);

        $courses = Course::whereHas('enrollments', fn($q) => $q->where('student_id', $student->id))
            ->withCount('lessons')
            ->get();

        return $this->sendResponse($courses, 'My enrolled courses retrieved.');
    }

    public function catalog(Request $request): \Illuminate\Http\JsonResponse
    {
        $schoolId = $request->header('X-School-Id');
        
        if (!$schoolId) {
            $school = \Modules\School\Models\Institution\School::first();
            $schoolId = $school ? $school->id : 1;
        }

        $courses = $this->service->getCourses((int)$schoolId, ['status' => 'published']);
        return $this->sendResponse($courses, 'Published courses catalog.');
    }

    public function learn(int $courseId): \Illuminate\Http\JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (!$student) return $this->sendError('Student not found.', [], 404);

        // Check enrollment
        $isEnrolled = Course::where('id', $courseId)
            ->whereHas('enrollments', fn($q) => $q->where('student_id', $student->id))
            ->exists();

        if (!$isEnrolled) {
            return $this->sendError('You are not enrolled in this course.', [], 403);
        }

        $program = $this->service->getCourseProgram($courseId);
        
        // Hide correct answers from student
        $program->each(function(Lesson $lesson) {
            $lesson->topics->each(function(Topic $topic) {
                if ($topic->topicable instanceof \Modules\School\Models\Lms\TopicContent\Quiz) {
                    $topic->topicable->questions->each(function($question) {
                        /** @var \Modules\School\Models\Lms\QuizQuestion $question */
                        /** @var \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Lms\QuizOption> $options */
                        $options = $question->options;
                        $options->makeHidden(['is_correct']);
                    });
                }
            });
        });

        // Also get student progress for these topics
        $progress = \Modules\School\Models\Lms\TopicProgress::where('student_id', $student->id)
            ->whereIn('topic_id', Topic::whereHas('lesson', fn($q) => $q->where('course_id', $courseId))->pluck('id'))
            ->get();

        return $this->sendResponse([
            'program' => $program,
            'progress' => $progress
        ], 'Learning data retrieved.');
    }

    public function completeTopic(Request $request, int $topicId): \Illuminate\Http\JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (!$student) return $this->sendError('Student not found.', [], 404);

        $metadata = $request->input('metadata');
        $metadataArray = is_array($metadata) ? $metadata : [];

        $progress = $this->service->markTopicAsCompleted($topicId, (int)$student->id, $metadataArray);
        return $this->sendResponse($progress, 'Topic marked as completed.');
    }

    public function startQuiz(int $quizId): \Illuminate\Http\JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (!$student) return $this->sendError('Student not found.', [], 404);

        $attempt = $this->service->startQuizAttempt($quizId, (int)$student->id);
        return $this->sendResponse($attempt, 'Quiz attempt started.');
    }

    public function submitQuiz(Request $request, int $attemptId): \Illuminate\Http\JsonResponse
    {
        /** @var \Modules\School\Models\Lms\QuizAttempt $attempt */
        $attempt = \Modules\School\Models\Lms\QuizAttempt::findOrFail($attemptId);
        
        // Ensure student owns the attempt
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (!$student || (int)$attempt->student_id !== (int)$student->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $answers = $request->input('answers');
        $answersArray = is_array($answers) ? $answers : [];

        $attempt = $this->service->submitQuizAttempt($attempt, $answersArray);
        return $this->sendResponse($attempt, 'Quiz submitted.');
    }
}
