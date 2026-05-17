<?php

namespace Modules\School\Services\Academic;

use Modules\School\Models\Academic\Grade;
use Illuminate\Support\Collection;

class GradeService
{
    /**
     * Get student grades for a specific academic year and semester.
     *
     * @return Collection<int, Grade>
     */
    public function getStudentGrades(string $studentId, string $academicYearId, ?string $semesterId = null): Collection
    {
        $query = Grade::with(['subject', 'teacher'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId);

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        /** @var Collection<int, Grade> $result */
        $result = $query->get();
        return $result;
    }

    /**
     * Get class grades for a specific subject, year, and semester.
     *
     * @return Collection<int, Grade>
     */
    public function getClassGrades(string $studyGroupId, string $subjectId, string $academicYearId, string $semesterId): Collection
    {
        /** @var Collection<int, Grade> $result */
        $result = Grade::with('student')
            ->where('study_group_id', $studyGroupId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->get();
        return $result;
    }

    /**
     * Save or update a student's grade for a specific subject.
     *
     * @param array<string, mixed> $data
     */
    public function saveGrade(array $data): Grade
    {
        /** @var Grade $grade */
        $grade = Grade::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'],
                'academic_year_id' => $data['academic_year_id'],
                'semester_id' => $data['semester_id'],
            ],
            $data
        );

        // Auto-calculate scores if components are provided
        if (isset($data['daily_score']) || isset($data['mid_score']) || isset($data['final_score'])) {
            $grade->knowledge_score = (float) $grade->calculateKnowledgeScore();
        }

        if (isset($data['practice_score']) || isset($data['project_score']) || isset($data['portfolio_score'])) {
            $grade->skill_score = (float) $grade->calculateSkillScore();
        }

        // Calculate final grade (average of knowledge and skill, or just knowledge if skill is 0)
        $knowledge = (float) $grade->knowledge_score;
        $skill = (float) $grade->skill_score;

        if ($knowledge > 0.0 && $skill > 0.0) {
            $grade->final_grade = round(($knowledge + $skill) / 2, 2);
        } elseif ($knowledge > 0.0) {
            $grade->final_grade = $knowledge;
        }

        if ($grade->final_grade) {
            $grade->grade_letter = Grade::getGradeLetter((float)$grade->final_grade);
            $grade->predicate = Grade::getPredicate((float)$grade->final_grade);
        }

        $grade->save();

        return $grade;
    }

    /**
     * Bulk save grades for a class.
     *
     * @param array<int, array<string, mixed>> $gradesData
     */
    public function bulkSaveGrades(array $gradesData): int
    {
        $count = 0;
        foreach ($gradesData as $data) {
            $this->saveGrade($data);
            $count++;
        }
        return $count;
    }
}
