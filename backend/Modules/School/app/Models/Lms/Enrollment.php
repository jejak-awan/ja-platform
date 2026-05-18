<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $course_id
 * @property string $student_id
 * @property Carbon|null $enrolled_at
 * @property Carbon|null $expired_at
 * @property bool $is_active
 * @property-read Course $course
 * @property-read Student $student
 */
class Enrollment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_lms_enrollments';

    protected $fillable = [
        'course_id',
        'student_id',
        'enrolled_at',
        'expired_at',
        'is_active',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
