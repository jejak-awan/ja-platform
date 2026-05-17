<?php

namespace Modules\School\Models\Lms;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\School\Models\Student\Student;

/**
 * @property string $id
 * @property string $topic_id
 * @property string $student_id
 * @property bool $is_completed
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property array<string, mixed>|null $metadata
 * @property-read Topic $topic
 * @property-read Student $student
 */
class TopicProgress extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
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
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<Topic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
