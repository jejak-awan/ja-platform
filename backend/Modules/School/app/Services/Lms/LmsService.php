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
use Illuminate\Support\Facades\Storage;

class LmsService
{
    // --- Storage Helpers ---

    /**
     * Get the private storage path for a specific course.
     * Stored in storage/app/private/lms/school_{id}/course_{id}/{type}
     * 
     * @param int $schoolId
     * @param int $courseId
     * @param string $type materials, media, tasks
     * @return string
     */
    public function getCourseStoragePath(int $schoolId, int $courseId, string $type = 'materials'): string
    {
        return "lms/school_{$schoolId}/course_{$courseId}/{$type}";
    }

    /**
     * Get the public storage path for LMS assets.
     * Stored in storage/app/public/lms/{type}
     * 
     * @param string $type covers, thumbnails, icons
     * @return string
     */
    public function getPublicLmsPath(string $type = 'covers'): string
    {
        return "lms/{$type}";
    }

    /**
     * Upload a file to course storage.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $schoolId
     * @param int $courseId
     * @param string $type materials, media, tasks
     * @param string $visibility 'private' or 'public'
     * @return string The stored path
     */
    public function uploadCourseFile($file, int $schoolId, int $courseId, string $type = 'materials', string $visibility = 'private'): string
    {
        $path = $visibility === 'private' 
            ? $this->getCourseStoragePath($schoolId, $courseId, $type)
            : $this->getPublicLmsPath($type);
            
        $disk = $visibility === 'private' ? 'local' : 'public';
        
        $storedPath = $file->store($path, $disk);
        if ($storedPath === false) {
            throw new \RuntimeException("Failed to store file in {$disk} disk at {$path}");
        }
        
        return $storedPath;
    }

    // --- Course Management ---

    /**
     * @param int $schoolId
     * @param array<string, mixed> $filters
     * @return Collection<int, Course>
     */
    public function getCourses(int $schoolId, array $filters = []): Collection
    {
        $status = isset($filters['status']) && is_string($filters['status']) ? $filters['status'] : null;
        $level = isset($filters['level']) && is_string($filters['level']) ? $filters['level'] : null;
        $search = isset($filters['search']) && is_string($filters['search']) ? $filters['search'] : null;

        /** @var Collection<int, Course> */
        return Course::where('school_id', $schoolId)
            ->with(['academicYear', 'semester', 'department', 'grade'])
            ->withCount('lessons')
            ->when($status, fn($q) => $q->where('status', (string)$status))
            ->when($level, fn($q) => $q->where('level', (string)$level))
            ->when($search, fn($q) => $q->where('title', 'like', '%' . (string)$search . '%'))
            ->latest()
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     * @return Course
     */
    public function createCourse(array $data): Course
    {
        if (empty($data['slug']) && isset($data['title']) && is_string($data['title'])) {
            $title = $data['title'];
            $baseSlug = Str::slug($title);
            $slug = $baseSlug;
            $counter = 1;

            while (Course::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }
        
        /** @var Course $course */
        $course = Course::create($data);
        return $course;
    }

    /**
     * @param Course $course
     * @param array<string, mixed> $data
     * @return Course
     */
    public function updateCourse(Course $course, array $data): Course
    {
        $course->update($data);
        return $course;
    }

    // --- Lesson Management ---

    /**
     * @param array<string, mixed> $data
     * @return Lesson
     */
    public function createLesson(array $data): Lesson
    {
        /** @var Lesson $lesson */
        $lesson = Lesson::create($data);
        return $lesson;
    }

    /**
     * @param Lesson $lesson
     * @param array<string, mixed> $data
     * @return Lesson
     */
    public function updateLesson(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson;
    }

    // --- Topic Management (Polymorphic) ---

    /**
     * Create a topic with specific content.
     * 
     * @param array<string, mixed> $topicData Basic topic info (lesson_id, title, order, etc.)
     * @param string $contentType 'richtext', 'video', 'pdf', 'quiz'
     * @param array<string, mixed> $contentData Specific data for the content type
     * @return Topic
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

    /**
     * @param int $quizId
     * @param array<string, mixed> $questionData
     * @param array<int, array<string, mixed>> $options
     * @return QuizQuestion
     */
    public function addQuestionToQuiz(int $quizId, array $questionData, array $options = []): QuizQuestion
    {
        $questionData['quiz_id'] = $quizId;
        /** @var QuizQuestion $question */
        $question = QuizQuestion::create($questionData);

        foreach ($options as $option) {
            $val = isset($option['value']) && is_scalar($option['value']) ? (string)$option['value'] : '';
            $isCorrect = isset($option['is_correct']) ? (bool)$option['is_correct'] : false;

            QuizOption::create([
                'question_id' => $question->id,
                'value' => $val,
                'is_correct' => $isCorrect,
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

    /**
     * @param QuizAttempt $attempt
     * @param array<int|string, mixed> $answers
     * @return QuizAttempt
     */
    public function submitQuizAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        $score = 0;
        $totalPossible = 0;
        $quiz = $attempt->quiz;

        foreach ($quiz->questions as $question) {
            $totalPossible += (int)($question->score ?? 0);
            $studentAnswer = $answers[$question->id] ?? null;

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $correctOption = $question->options()->where('is_correct', true)->first();
                $correctId = $correctOption ? (string)$correctOption->id : null;
                if (is_scalar($studentAnswer) && $correctId === (string)$studentAnswer) {
                    $score += (int)($question->score ?? 0);
                }
            }
        }

        $percentage = $totalPossible > 0 ? ($score / $totalPossible) * 100 : 0;

        $attempt->update([
            'score' => (int)round($percentage),
            'finished_at' => now(),
            'answers' => $answers,
        ]);

        if ($percentage >= (int)($quiz->pass_score ?? 0)) {
            $topic = Topic::where('topicable_type', Quiz::class)
                ->where('topicable_id', $quiz->id)
                ->first();
            
            if ($topic) {
                $studentId = (int)$attempt->student_id;
                $this->markTopicAsCompleted($topic->id, $studentId, [
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

    /**
     * @param int $topicId
     * @param int $studentId
     * @param array<string, mixed> $metadata
     * @return TopicProgress
     */
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
     * 
     * @param int $courseId
     * @return Collection<int, Lesson>
     */
    public function getCourseProgram(int $courseId): Collection
    {
        /** @var Collection<int, Lesson> */
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
