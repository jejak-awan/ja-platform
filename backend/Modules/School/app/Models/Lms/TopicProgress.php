<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\School\Models\Student\Student;

class TopicProgress extends Model
{
    protected $table = 'sch_lms_topic_progress';

    protected $fillable = [
        'topic_id',
        'student_id',
        'is_completed',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'metadata' => 'json',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
