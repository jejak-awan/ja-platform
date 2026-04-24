<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Lesson> $lessons
 */
class Section extends Model
{
    protected $table = 'sch_lms_sections';

    protected $fillable = [
        'course_id',
        'title',
        'sort_order',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Course, $this>
     */
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Lesson, $this>
     */
    public function lessons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }
}
