<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\School\Models\Student\Student;

class Enrollment extends Model
{
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
