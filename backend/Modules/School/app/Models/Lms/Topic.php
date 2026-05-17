<?php

namespace Modules\School\Models\Lms;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $lesson_id
 * @property string $title
 * @property int $order
 * @property bool $is_active
 * @property bool $preview
 * @property string $topicable_type
 * @property string $topicable_id
 * @property-read \Modules\School\Models\Lms\Lesson $lesson
 * @property-read \Illuminate\Database\Eloquent\Model $topicable
 */
class Topic extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_lms_topics';

    protected $fillable = [
        'workspace_id',
        'lesson_id',
        'title',
        'order',
        'is_active',
        'preview',
        'topicable_type',
        'topicable_id',
    ];

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the actual content of the topic (Polymorphic).
     * @return MorphTo<Model, $this>
     */
    public function topicable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TopicProgress, $this>
     */
    public function progress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TopicProgress::class);
    }
}
