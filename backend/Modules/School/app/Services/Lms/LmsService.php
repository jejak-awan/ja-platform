<?php

namespace Modules\School\Services\Lms;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Lms\Enrollment;
use Modules\School\Models\Lms\TopicProgress;
use Modules\School\Models\Lms\TopicContent\RichText;
use Modules\School\Models\Lms\TopicContent\Video;
use Modules\School\Models\Lms\TopicContent\Pdf;

use Modules\School\Models\Lms\TopicContent\Quiz;
use Modules\School\Models\Lms\QuizQuestion;
use Modules\School\Models\Lms\QuizOption;
use Modules\School\Models\Lms\QuizAttempt;

class LmsService
{
    // --- Course Management ---

    public function getCourses(int $schoolId, array $filters = []): Collection
    {
        return Course::where('school_id', $schoolId)
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['level']), fn($q) => $q->where('level', $filters['level']))
            ->latest()
            ->get();
    }

    public function createCourse(array $data): Course
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        
        /** @var Course $course */
        $course = Course::create($data);
        return $course;
    }

    public function updateCourse(Course $course, array $data): Course
    {
        $course->update($data);
        return $course;
    }

    // --- Lesson Management ---

    public function createLesson(array $data): Lesson
    {
        /** @var Lesson $lesson */
        $lesson = Lesson::create($data);
        return $lesson;
    }

    public function updateLesson(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson;
    }

    // --- Topic Management (Polymorphic) ---

    /**
     * Create a topic with specific content.
     * 
     * @param array $topicData Basic topic info (lesson_id, title, order, etc.)
     * @param string $contentType 'richtext', 'video', 'pdf', 'quiz'
     * @param array $contentData Specific data for the content type
     */
    public function createTopic(array $topicData, string $contentType, array $contentData): Topic
    {
        $content = match ($contentType) {
            'richtext' => RichText::create($contentData),
            'video' => Video::create($contentData),
            'pdf' => Pdf::create($contentData),
            'quiz' => Quiz::create($contentData),
            default => throw new \InvalidArgumentException("Unsupported content type: $contentType"),
        };

        $topicData['topicable_type'] = get_class($content);
        $topicData['topicable_id'] = $content->id;

        /** @var Topic $topic */
        $topic = Topic::create($topicData);
        return $topic;
    }

    // --- Quiz Management ---

    public function addQuestionToQuiz(int $quizId, array $questionData, array $options = []): QuizQuestion
    {
        $questionData['quiz_id'] = $quizId;
        /** @var QuizQuestion $question */
        $question = QuizQuestion::create($questionData);

        foreach ($options as $option) {
            QuizOption::create([
                'question_id' => $question->id,
                'value' => $option['value'],
                'is_correct' => $option['is_correct'] ?? false,
            ]);
        }

        return $question;
    }

    public function startQuizAttempt(int $quizId, int $studentId): QuizAttempt
    {
        /** @var QuizAttempt $attempt */
        $attempt = QuizAttempt::create([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'started_at' => now(),
        ]);
        return $attempt;
    }

    public function submitQuizAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        // Simple scoring logic for Multiple Choice
        $score = 0;
        $totalPossible = 0;
        $quiz = $attempt->quiz;

        foreach ($quiz->questions as $question) {
            $totalPossible += $question->score;
            $studentAnswer = $answers[$question->id] ?? null;

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $correctOption = $question->options()->where('is_correct', true)->first();
                if ($correctOption && (string)$correctOption->id === (string)$studentAnswer) {
                    $score += $question->score;
                }
            }
            // Add short_answer logic if needed
        }

        // Calculate percentage
        $percentage = $totalPossible > 0 ? ($score / $totalPossible) * 100 : 0;

        $attempt->update([
            'score' => round($percentage),
            'finished_at' => now(),
            'answers' => $answers,
        ]);

        // Auto-mark topic as completed if passed
        if ($percentage >= $quiz->pass_score) {
            $topic = Topic::where('topicable_type', Quiz::class)
                ->where('topicable_id', $quiz->id)
                ->first();
            
            if ($topic) {
                $this->markTopicAsCompleted($topic->id, $attempt->student_id, [
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->score
                ]);
            }
        }

        return $attempt;
    }

    // --- Enrollment & Progress ---

    public function enrollStudent(int $courseId, int $studentId): Enrollment
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::firstOrCreate([
            'course_id' => $courseId,
            'student_id' => $studentId,
        ], [
            'enrolled_at' => now(),
            'is_active' => true,
        ]);
        return $enrollment;
    }

    public function markTopicAsCompleted(int $topicId, int $studentId, array $metadata = []): TopicProgress
    {
        /** @var TopicProgress $progress */
        $progress = TopicProgress::updateOrCreate([
            'topic_id' => $topicId,
            'student_id' => $studentId,
        ], [
            'is_completed' => true,
            'completed_at' => now(),
            'metadata' => $metadata,
        ]);
        return $progress;
    }

    /**
     * Get the entire course program structure (Lessons -> Topics -> Content).
     */
    public function getCourseProgram(int $courseId): Collection
    {
        return Lesson::with(['topics.topicable' => function ($morph) {
            $morph->morphWith([
                \Modules\School\Models\Lms\TopicContent\Quiz::class => ['questions.options'],
            ]);
        }])
            ->where('course_id', $courseId)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
    }
}
