<?php

namespace Modules\School\Services\Lms;

use Modules\School\Models\Lms\QuestionBank;
use Modules\School\Models\Lms\Exam;
use Modules\School\Models\Lms\ExamResult;
use Modules\School\Models\Lms\Question;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Lms\Section;
use Modules\School\Models\Lms\Lesson;
use Modules\School\Models\Lms\Enrollment;
use Modules\School\Models\Lms\LessonProgress;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LmsService
{
    // --- Courses ---

    // --- Courses ---

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Course>
     */
    public function getCourses(array $filters = []): Collection
    {
        /** @var Collection<int, Course> $courses */
        $courses = Course::with(['subject', 'author', 'academicYear'])
            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
            ->when(!empty($filters['academic_year_id']), fn($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(!empty($filters['status']) && is_string($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['author_id']), fn($q) => $q->where('author_id', $filters['author_id']))
            ->latest()
            ->get();
        return $courses;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCourse(array $data): Course
    {
        /** @var Course $course */
        $course = DB::transaction(function () use ($data) {
            /** @var Course $course */
            $course = Course::create($data);
            if (isset($data['sections']) && is_array($data['sections'])) {
                /** @var array<int, array<string, mixed>> $sections */
                $sections = $data['sections'];
                $this->syncCurriculum($course, $sections);
            }
            return $course;
        });
        return $course;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateCourse(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $course->update($data);
            if (isset($data['sections']) && is_array($data['sections'])) {
                /** @var array<int, array<string, mixed>> $sections */
                $sections = $data['sections'];
                $this->syncCurriculum($course, $sections);
            }
            return $course;
        });
    }

    /**
     * @param array<int, array<string, mixed>> $sections
     */
    protected function syncCurriculum(Course $course, array $sections): void
    {
        $newSectionIds = [];

        foreach ($sections as $index => $sectionData) {
            /** @var Section $section */
            $section = $course->sections()->updateOrCreate(
                ['id' => $sectionData['id'] ?? null],
                [
                    'title' => isset($sectionData['title']) && is_string($sectionData['title']) ? $sectionData['title'] : 'Untitled',
                    'sort_order' => isset($sectionData['sort_order']) && is_numeric($sectionData['sort_order']) ? (int)($sectionData['sort_order']) : $index,
                ]
            );
            $newSectionIds[] = $section->id;

            if (isset($sectionData['lessons']) && is_array($sectionData['lessons'])) {
                $newLessonIds = [];

                /** @var array<int, array<string, mixed>> $lessonsData */
                $lessonsData = $sectionData['lessons'];

                foreach ($lessonsData as $lIndex => $lessonData) {
                    /** @var Lesson $lesson */
                    $lesson = $section->lessons()->updateOrCreate(
                        ['id' => $lessonData['id'] ?? null],
                        [
                            'title' => is_string($lessonData['title']) ? $lessonData['title'] : 'Untitled',
                            'slug' => is_string($lessonData['slug'] ?? null) ? (string)$lessonData['slug'] : \Illuminate\Support\Str::slug(is_string($lessonData['title']) ? $lessonData['title'] : 'untitled'),
                            'type' => is_string($lessonData['type'] ?? null) ? (string)$lessonData['type'] : 'text',
                            'content' => is_string($lessonData['content'] ?? null) ? (string)$lessonData['content'] : null,
                            'video_url' => is_string($lessonData['video_url'] ?? null) ? (string)$lessonData['video_url'] : null,
                            'exam_id' => $lessonData['exam_id'] ?? null,
                            'sort_order' => is_numeric($lessonData['sort_order'] ?? null) ? (int)$lessonData['sort_order'] : $lIndex,
                        ]
                    );
                    $newLessonIds[] = (int) $lesson->id;
                }

                // Delete removed lessons
                $section->lessons()->whereNotIn('id', $newLessonIds)->delete();
            }
        }

        // Delete removed sections
        $course->sections()->whereNotIn('id', $newSectionIds)->delete();
    }

    public function getCourseDetail(int $id): Course
    {
        /** @var Course $course */
        $course = Course::with(['sections.lessons.exam', 'subject', 'author'])->findOrFail($id);
        return $course;
    }

    // --- Sections & Lessons ---

    /**
     * @param array<string, mixed> $data
     */
    public function createSection(Course $course, array $data): Section
    {
        /** @var Section $section */
        $section = $course->sections()->create($data);
        return $section;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLesson(Section $section, array $data): Lesson
    {
        /** @var Lesson $lesson */
        $lesson = $section->lessons()->create($data);
        return $lesson;
    }

    // --- Enrollment & Progress ---

    public function enrollStudent(Course $course, int $studentId, ?int $academicYearId = null): Enrollment
    {
        /** @var Enrollment $enrollment */
        $enrollment = Enrollment::firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
        ], [
            'enrolled_at' => now(),
            'status' => 'active',
            'progress' => 0.0,
        ]);
        return $enrollment;
    }

    public function getStudentEnrollment(int $courseId, int $studentId): ?Enrollment
    {
        /** @var Enrollment|null $enrollment */
        $enrollment = Enrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();
        return $enrollment;
    }

    /**
     * @return Collection<int, Enrollment>
     */
    public function getCourseMonitoring(int $courseId): Collection
    {
        /** @var Collection<int, Enrollment> $enrollments */
        $enrollments = Enrollment::with(['student'])
            ->where('course_id', $courseId)
            ->latest()
            ->get();
        return $enrollments;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{total_courses: int, total_students: int, average_progress: float, recent_enrollments: Collection<int, Enrollment>}
     */
    public function getTeacherStats(int $teacherId, array $filters = []): array
    {
        $coursesQuery = Course::where('author_id', $teacherId)
            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
            ->when(!empty($filters['academic_year_id']), fn($q) => $q->where('academic_year_id', $filters['academic_year_id']));
        
        $courses = $coursesQuery->pluck('id');
        
        /** @var Collection<int, Enrollment> $recent */
        $recent = Enrollment::with(['student', 'course'])
                ->whereIn('course_id', $courses)
                ->latest()
                ->limit(5)
                ->get();

        return [
            'total_courses' => (int) $courses->count(),
            'total_students' => (int) Enrollment::whereIn('course_id', $courses)->distinct('student_id')->count(),
            'average_progress' => (float) (Enrollment::whereIn('course_id', $courses)->avg('progress') ?? 0.0),
            'recent_enrollments' => $recent,
        ];
    }

    public function updateLessonProgress(Enrollment $enrollment, int $lessonId, bool $completed = true): LessonProgress
    {
        /** @var LessonProgress $progress */
        $progress = LessonProgress::updateOrCreate([
            'enrollment_id' => $enrollment->id,
            'lesson_id' => $lessonId,
        ], [
            'is_completed' => $completed,
            'completed_at' => $completed ? now() : null,
        ]);

        $this->syncEnrollmentProgress($enrollment);

        return $progress;
    }

    protected function syncEnrollmentProgress(Enrollment $enrollment): void
    {
        /** @var Course $course */
        $course = $enrollment->course;
        $sectionIds = $course->sections->pluck('id');
        
        $totalLessons = (int) Lesson::whereIn('section_id', $sectionIds)->count();
        if ($totalLessons === 0) return;

        $completedLessons = (int) LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->count();

        $percentage = (float) round(($completedLessons / $totalLessons) * 100, 2);
        
        $enrollment->update([
            'progress' => $percentage,
            'status' => $percentage >= 100.0 ? 'completed' : 'active',
            'completed_at' => $percentage >= 100.0 ? now() : null,
        ]);
    }

    // --- Question Banks ---

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, QuestionBank>
     */
    public function getQuestionBanks(array $filters = []): Collection
    {
        /** @var Collection<int, QuestionBank> $banks */
        $banks = QuestionBank::with('subject')
            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
            ->when(!empty($filters['academic_year_id']), fn($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(!empty($filters['subject_id']), fn($q) => $q->where('subject_id', $filters['subject_id']))
            ->latest()
            ->get();
        return $banks;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createQuestionBank(array $data): QuestionBank
    {
        /** @var QuestionBank $bank */
        $bank = QuestionBank::create($data);
        return $bank;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateQuestionBank(QuestionBank $bank, array $data): QuestionBank
    {
        $bank->update($data);
        return $bank;
    }

    // --- Exams ---

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Exam>
     */
    public function getExams(array $filters = []): Collection
    {
        /** @var Collection<int, Exam> $exams */
        $exams = Exam::with(['subject', 'targetGroups'])
            ->withCount('questions')
            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
            ->when(!empty($filters['academic_year_id']), fn($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(!empty($filters['subject_id']), fn($q) => $q->where('subject_id', $filters['subject_id']))
            ->latest()
            ->get();
        return $exams;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createExam(array $data): Exam
    {
        /** @var Exam $exam */
        $exam = Exam::create($data);
        return $exam;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateExam(Exam $exam, array $data): Exam
    {
        $exam->update($data);
        return $exam;
    }

    // --- Exam Results ---

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, ExamResult>
     */
    public function getExamResults(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return ExamResult::with(['exam', 'student'])
            ->when(!empty($filters['exam_id']), fn($q) => $q->where('exam_id', $filters['exam_id']))
            ->when(!empty($filters['student_id']), fn($q) => $q->where('student_id', $filters['student_id']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createExamResult(array $data): ExamResult
    {
        /** @var Exam $exam */
        $exam = Exam::findOrFail($data['exam_id']);
        
        if (!empty($data['answers_snapshot']) && is_array($data['answers_snapshot'])) {
            /** @var array<int, array{question_id: int, answer: string}> $snapshot */
            $snapshot = $data['answers_snapshot'];
            $data['score'] = (float) $this->calculateScore($snapshot);
        } else {
            $data['score'] = 0.0;
        }

        if ((float)($exam->passing_grade ?? 0) > 0.0 && (float)$data['score'] < (float)$exam->passing_grade) {
            $data['status'] = 'failed';
        } else {
            $data['status'] = 'passed';
        }

        /** @var ExamResult $result */
        $result = ExamResult::create($data);
        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateExamResult(ExamResult $result, array $data): ExamResult
    {
        if (!empty($data['answers_snapshot']) && is_array($data['answers_snapshot'])) {
            /** @var array<int, array{question_id: int, answer: string}> $snapshot */
            $snapshot = $data['answers_snapshot'];
            $data['score'] = (float) $this->calculateScore($snapshot);
            
            /** @var Exam $exam */
            $exam = $result->exam;
            if ((float)($exam->passing_grade ?? 0) > 0.0 && (float)$data['score'] < (float)$exam->passing_grade) {
                $data['status'] = 'failed';
            } else {
                $data['status'] = 'passed';
            }
        }

        $result->update($data);
        return $result;
    }

    /**
     * @param array<int, array{question_id: int, answer: string}> $snapshot
     */
    public function calculateScore(array $snapshot): float
    {
        $totalQuestions = count($snapshot);

        if ($totalQuestions === 0) return 0.0;

        /** @var array<int, int> $questionIds */
        $questionIds = array_column($snapshot, 'question_id');
        $questions = Question::whereIn('id', $questionIds)->get()->keyBy('id');

        $totalCorrect = 0;
        foreach ($snapshot as $entry) {
            /** @var Question|null $question */
            $question = $questions->get($entry['question_id']);
            if ($question && $question->type !== 'essay') {
                if (trim(strtolower((string)$question->answer)) === trim(strtolower((string)$entry['answer']))) {
                    $totalCorrect++;
                }
            }
        }

        return (float) round(($totalCorrect / $totalQuestions) * 100, 2);
    }

    // --- Questions ---

    /**
     * @return Collection<int, Question>
     */
    public function getQuestionByBank(int $bankId): Collection
    {
        /** @var Collection<int, Question> $questions */
        $questions = Question::where('question_bank_id', $bankId)->latest()->get();
        return $questions;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createQuestion(QuestionBank $bank, array $data): Question
    {
        /** @var Question $question */
        $question = $bank->questions()->create($data);
        return $question;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateQuestion(Question $question, array $data): Question
    {
        $question->update($data);
        return $question;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getExamQuestions(Exam $exam): Collection
    {
        /** @var Collection<int, Question> $questions */
        $questions = $exam->questions()->orderBy('sch_lms_exam_questions.sort_order')->get();
        return $questions;
    }

    /**
     * @param array<int, array{question_id: int, sort_order?: int, points?: float}> $questions
     */
    public function syncExamQuestions(Exam $exam, array $questions): void
    {
        $syncData = [];
        foreach ($questions as $index => $q) {
            $syncData[$q['question_id']] = [
                'sort_order' => $q['sort_order'] ?? $index,
                'points' => (float) ($q['points'] ?? 1.00)
            ];
        }
        $exam->questions()->sync($syncData);
        
        $exam->update(['total_questions' => count($syncData)]);
    }
}
