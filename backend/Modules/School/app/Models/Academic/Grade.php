<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByUnit;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_unit_id
 * @property int $student_id
 * @property int $subject_id
 * @property int $academic_year_id
 * @property int $semester_id
 * @property int $study_group_id
 * @property int $staff_id
 * @property float|null $daily_score
 * @property float|null $mid_score
 * @property float|null $final_score
 * @property float|null $knowledge_score
 * @property float|null $practice_score
 * @property float|null $project_score
 * @property float|null $portfolio_score
 * @property float|null $skill_score
 * @property float|null $final_grade
 * @property string|null $grade_letter
 * @property string|null $predicate
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Student\Student $student
 * @property-read Subject $subject
 * @property-read AcademicYear $academicYear
 * @property-read Semester $semester
 * @property-read StudyGroup $studyGroup
 * @property-read \Modules\School\Models\HR\Staff $teacher
 */
class Grade extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByUnit;

    protected $table = 'sch_acad_grades';

    protected $fillable = [
        'school_id',
        'school_unit_id',
        'student_id',
        'subject_id',
        'academic_year_id',
        'semester_id',
        'study_group_id',
        'staff_id',
        'daily_score',
        'mid_score',
        'final_score',
        'knowledge_score',
        'practice_score',
        'project_score',
        'portfolio_score',
        'skill_score',
        'final_grade',
        'grade_letter',
        'predicate',
        'description',
    ];

    protected $casts = [
        'daily_score' => 'float',
        'mid_score' => 'float',
        'final_score' => 'float',
        'knowledge_score' => 'float',
        'practice_score' => 'float',
        'project_score' => 'float',
        'portfolio_score' => 'float',
        'skill_score' => 'float',
        'final_grade' => 'float',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Subject, $this>
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Semester, $this>
     */
    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<StudyGroup, $this>
     */
    public function studyGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\HR\Staff, $this>
     */
    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\HR\Staff::class, 'staff_id');
    }

    /**
     * Calculate knowledge score from component scores.
     */
    public function calculateKnowledgeScore(float $dailyWeight = 0.5, float $midWeight = 0.2, float $finalWeight = 0.3): float
    {
        $score = ($this->daily_score ?? 0) * $dailyWeight
               + ($this->mid_score ?? 0) * $midWeight
               + ($this->final_score ?? 0) * $finalWeight;

        return round($score, 2);
    }

    /**
     * Calculate skill score from component scores.
     */
    public function calculateSkillScore(): float
    {
        $scores = array_filter([
            $this->practice_score,
            $this->project_score,
            $this->portfolio_score,
        ], fn($s) => $s !== null);

        if (empty($scores)) return 0;

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Get grade letter based on final grade.
     */
    public static function getGradeLetter(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }

    /**
     * Get predicate based on final grade.
     */
    public static function getPredicate(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Sangat Baik',
            $score >= 80 => 'Baik',
            $score >= 70 => 'Cukup',
            $score >= 60 => 'Kurang',
            default => 'Sangat Kurang',
        };
    }
}
