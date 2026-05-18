<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\QuizAttempt;
use Modules\School\Models\Lms\QuizOption;
use Modules\School\Models\Lms\QuizQuestion;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Lms\TopicContent\Quiz;
use Modules\School\Models\Lms\TopicProgress;
use Modules\School\Models\Student\Student;
use Modules\School\Services\Lms\LmsService;

class StudentLmsController extends BaseController
{
    public function __construct(protected LmsService $service) {}

    public function myCourses(): JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student) {
            return $this->sendError('Student not found.', [], 404);
        }

        $courses = Course::whereHas('enrollments', fn ($q) => $q->where('student_id', $student->id))
            ->withCount('lessons')
            ->get();

        return $this->sendResponse($courses, 'My enrolled courses retrieved.');
    }

    public function catalog(Request $request): JsonResponse
    {
        $schoolId = $request->header('X-School-Id');

        if (! $schoolId) {
            $school = School::first();
            $schoolId = $school ? $school->id : '1';
        }

        $courses = $this->service->getCourses((string) $schoolId, ['status' => 'published']);

        return $this->sendResponse($courses, 'Published courses catalog.');
    }

    public function learn(string $courseId): JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student) {
            return $this->sendError('Student not found.', [], 404);
        }

        // Check enrollment
        $isEnrolled = Course::where('id', $courseId)
            ->whereHas('enrollments', fn ($q) => $q->where('student_id', $student->id))
            ->exists();

        if (! $isEnrolled) {
            return $this->sendError('You are not enrolled in this course.', [], 403);
        }

        $program = $this->service->getCourseProgram($courseId);

        // Hide correct answers from student
        $program->each(function (Lesson $lesson): void {
            $lesson->topics->each(function (Topic $topic): void {
                if ($topic->topicable instanceof Quiz) {
                    $topic->topicable->questions->each(function ($question): void {
                        /** @var QuizQuestion $question */
                        /** @var Collection<int, QuizOption> $options */
                        $options = $question->options;
                        $options->makeHidden(['is_correct']);
                    });
                }
            });
        });

        // Also get student progress for these topics
        $progress = TopicProgress::where('student_id', $student->id)
            ->whereIn('topic_id', Topic::whereHas('lesson', fn ($q) => $q->where('course_id', $courseId))->pluck('id'))
            ->get();

        return $this->sendResponse([
            'program' => $program,
            'progress' => $progress,
        ], 'Learning data retrieved.');
    }

    public function completeTopic(Request $request, string $topicId): JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student) {
            return $this->sendError('Student not found.', [], 404);
        }

        $metadata = $request->input('metadata');
        $metadataArray = is_array($metadata) ? $metadata : [];

        $progress = $this->service->markTopicAsCompleted($topicId, $student->id, $metadataArray);

        return $this->sendResponse($progress, 'Topic marked as completed.');
    }

    public function startQuiz(string $quizId): JsonResponse
    {
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student) {
            return $this->sendError('Student not found.', [], 404);
        }

        $attempt = $this->service->startQuizAttempt($quizId, $student->id);

        return $this->sendResponse($attempt, 'Quiz attempt started.');
    }

    public function submitQuiz(Request $request, string $attemptId): JsonResponse
    {
        /** @var QuizAttempt $attempt */
        $attempt = QuizAttempt::findOrFail($attemptId);

        // Ensure student owns the attempt
        /** @var Student|null $student */
        $student = Student::where('user_id', auth()->id())->first();
        if (! $student || $attempt->student_id !== $student->id) {
            return $this->sendError('Unauthorized.', [], 403);
        }

        $answers = $request->input('answers');
        $answersArray = is_array($answers) ? $answers : [];

        $attempt = $this->service->submitQuizAttempt($attempt, $answersArray);

        return $this->sendResponse($attempt, 'Quiz submitted.');
    }
}
