<?php

/**
 * Antigravity: Standardized LMS Lesson Model (Level 9)
 */

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $course_id
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
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_lms_lessons';

    protected $fillable = [
        'workspace_id',
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
