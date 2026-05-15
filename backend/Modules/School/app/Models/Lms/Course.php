<?php

namespace Modules\School\Models\Lms;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Models\User;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Student\Student;

/**
 * @property int $id
 * @property int $school_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $image_path
 * @property string|null $video_url
 * @property string|null $base_price
 * @property string|null $discount_price
 * @property string $status
 * @property string $level
 * @property array<string, mixed>|null $metadata
 * @property int $author_id
 * @property int|null $academic_year_id
 * @property int|null $semester_id
 * @property int|null $department_id
 * @property int|null $grade_id
 * @property bool $is_global
 * @property-read \Illuminate\Support\Collection<int, \Modules\School\Models\Lms\Lesson> $lessons
 */
class Course extends Model
{
    use ScopedByWorkspace;
    use SoftDeletes;

    protected $table = 'sch_lms_courses';

    protected $fillable = [
        'workspace_id',
        'school_id',
        'title',
        'slug',
        'summary',
        'description',
        'image_path',
        'video_url',
        'base_price',
        'discount_price',
        'status',
        'level',
        'metadata',
        'author_id',
        'academic_year_id',
        'semester_id',
        'department_id',
        'grade_id',
        'is_global',
    ];

    protected $casts = [
        'metadata' => 'array',
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_global' => 'boolean',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return BelongsTo<\Modules\School\Models\Academic\Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\Semester::class);
    }

    /**
     * @return BelongsTo<\Modules\School\Models\Academic\Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\Department::class);
    }

    /**
     * @return BelongsTo<\Modules\School\Models\Academic\Grade, $this>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\Grade::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Student, $this>
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'sch_lms_enrollments', 'course_id', 'student_id');
    }
}
