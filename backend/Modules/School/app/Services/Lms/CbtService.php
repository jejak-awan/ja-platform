<?php

namespace Modules\School\Services\Lms;

use Modules\School\Models\Lms\Exam;
use Modules\School\Models\Lms\ExamSession;
use Illuminate\Support\Collection;

class CbtService
{
    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Exam>
     */
    public function getFormalExams(array $filters = []): Collection
    {
        /** @var Collection<int, Exam> $exams */
        $exams = Exam::with(['subject', 'academicYear'])
            ->whereIn('category', ['pts', 'uas', 'tryout'])
            ->when(!empty($filters['school_id']), fn($q) => $q->where('school_id', $filters['school_id']))
            ->when(!empty($filters['academic_year_id']), fn($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->latest()
            ->get();
        return $exams;
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, ExamSession>
     */
    public function getSessions(array $filters = []): Collection
    {
        /** @var Collection<int, ExamSession> $sessions */
        $sessions = ExamSession::with(['exam', 'studyGroup'])
            ->when(!empty($filters['exam_id']), fn($q) => $q->where('exam_id', $filters['exam_id']))
            ->latest()
            ->get();
        return $sessions;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSession(array $data): ExamSession
    {
        /** @var ExamSession $session */
        $session = ExamSession::create($data);
        return $session;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSession(ExamSession $session, array $data): ExamSession
    {
        $session->update($data);
        return $session;
    }

    public function deleteSession(ExamSession $session): bool
    {
        return (bool) $session->delete();
    }

    public function generateToken(ExamSession $session): string
    {
        $token = strtoupper(\Illuminate\Support\Str::random(6));
        $session->update(['token' => $token]);
        return $token;
    }
}
