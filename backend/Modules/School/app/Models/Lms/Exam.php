<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $category
 * @property string $type
 * @property int $school_id
 * @property int $academic_year_id
 * @property int $subject_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property int $duration
 * @property int $total_questions
 * @property int $passing_grade
 * @property bool $is_randomized
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 * @property-read \Modules\School\Models\Academic\Subject $subject
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Academic\StudyGroup> $targetGroups
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ExamResult> $results
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Question> $questions
 */
class Exam extends Model
{
    protected $table = 'sch_lms_exams';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'category',
        'type',
        'school_id',
        'academic_year_id',
        'subject_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration',
        'total_questions',
        'passing_grade',
        'is_randomized',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_randomized' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\Subject, $this>
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Modules\School\Models\Academic\StudyGroup, $this>
     */
    public function targetGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\School\Models\Academic\StudyGroup::class, 'exam_targets');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ExamResult, $this>
     */
    public function results(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Question, $this>
     */
    public function questions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'sch_lms_exam_questions')
                    ->withPivot(['sort_order', 'points'])
                    ->withTimestamps();
    }
}
