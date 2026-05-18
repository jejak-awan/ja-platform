<?php

/**
 * Antigravity: Standardized LMS Section Model (Level 9)
 */

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Traits\ScopedByWorkspace;

class Section extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_lms_sections';

    protected $fillable = [
        'workspace_id',
        'course_id',
        'title',
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
     * @return HasMany<Lesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
