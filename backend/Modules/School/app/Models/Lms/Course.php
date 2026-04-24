<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Academic\Subject;
use Modules\Core\Models\User;

/**
 * @property int $id
 * @property int $school_id
 * @property int $academic_year_id
 * @property int $subject_id
 * @property int $author_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string|null $thumbnail
 * @property string $status
 * @property string|null $level
 * @property array<string, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read School $school
 * @property-read Subject $subject
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 * @property-read User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Section> $sections
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Enrollment> $enrollments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Student\Student> $students
 */
class Course extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, SoftDeletes;

    protected $table = 'sch_lms_courses';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'subject_id',
        'author_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'status',
        'level',
        'meta',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Subject, $this>
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Section, $this>
     */
    public function sections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort_order');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Enrollment, $this>
     */
    public function enrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Modules\School\Models\Student\Student, $this>
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\School\Models\Student\Student::class, 'sch_lms_enrollments', 'course_id', 'student_id')
            ->withPivot('enrolled_at', 'completed_at', 'progress', 'status')
            ->withTimestamps();
    }
}
