<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Topic extends Model
{
    protected $table = 'sch_lms_topics';

    protected $fillable = [
        'lesson_id',
        'title',
        'order',
        'is_active',
        'preview',
        'topicable_type',
        'topicable_id',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the actual content of the topic (Polymorphic).
     */
    public function topicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function progress(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TopicProgress::class);
    }
}
