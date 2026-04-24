<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Student\Student;

/**
 * @property int $id
 * @property int $course_id
 * @property int $student_id
 * @property int $academic_year_id
 * @property \Illuminate\Support\Carbon|null $enrolled_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int $progress
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Course $course
 * @property-read Student $student
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LessonProgress> $lessonProgresses
 */
class Enrollment extends Model
{
    protected $table = 'sch_lms_enrollments';

    protected $fillable = [
        'course_id',
        'student_id',
        'academic_year_id',
        'enrolled_at',
        'completed_at',
        'progress',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Course, $this>
     */
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LessonProgress, $this>
     */
    public function lessonProgresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
