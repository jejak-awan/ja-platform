<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $course_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $summary
 * @property int $order
 * @property bool $is_active
 * @property-read Course $course
 */
class Lesson extends Model
{
    protected $table = 'sch_lms_lessons';

    protected $fillable = [
        'course_id',
        'parent_id',
        'title',
        'summary',
        'order',
        'is_active',
    ];

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Lesson, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'parent_id');
    }

    /**
     * @return HasMany<Lesson, $this>
     */
    public function subLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'parent_id')->orderBy('order');
    }

    /**
     * @return HasMany<Topic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }
}
