<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $enrollment_id
 * @property int $lesson_id
 * @property bool $is_completed
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Enrollment $enrollment
 * @property-read Lesson $lesson
 */
class LessonProgress extends Model
{
    protected $table = 'sch_lms_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Enrollment, $this>
     */
    public function enrollment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Lesson, $this>
     */
    public function lesson(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
