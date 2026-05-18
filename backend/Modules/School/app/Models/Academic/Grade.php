<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $student_id
 * @property string $subject_id
 * @property string $academic_year_id
 * @property string $semester_id
 * @property string $study_group_id
 * @property string $staff_id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Student $student
 * @property-read Subject $subject
 * @property-read AcademicYear $academicYear
 * @property-read Semester $semester
 * @property-read StudyGroup $studyGroup
 * @property-read Staff $teacher
 */
class Grade extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $table = 'sch_acad_grades';

    protected $fillable = [
        'school_id',
        'workspace_id',
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
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return BelongsTo<StudyGroup, $this>
     */
    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
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
        ], fn (?float $s): bool => $s !== null);

        if ($scores === []) {
            return 0;
        }

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
