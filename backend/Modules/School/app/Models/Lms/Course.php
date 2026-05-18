<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\Grade;
use Modules\School\Models\Academic\Semester;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Student\Student;
use Modules\System\Models\User;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
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
 * @property string $author_id
 * @property string|null $academic_year_id
 * @property string|null $semester_id
 * @property string|null $department_id
 * @property string|null $grade_id
 * @property bool $is_global
 * @property-read Collection<int, Lesson> $lessons
 */
class Course extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

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
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Grade, $this>
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'sch_lms_enrollments', 'course_id', 'student_id');
    }
}
