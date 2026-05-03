<?php
/**
 * Antigravity: Standardized LMS Lesson Model (Level 9)
 */

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $course_id
 * @property int $section_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property int $order
 * @property-read Course $course
 * @property-read Section $section
 */
class Lesson extends Model
{
    protected $table = 'sch_lms_lessons';

    protected $fillable = [
        'section_id',
        'course_id',
        'title',
        'slug',
        'summary',
        'order',
    ];

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Section, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return HasMany<Topic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }
}
