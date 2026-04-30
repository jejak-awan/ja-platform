<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'parent_id');
    }

    public function subLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'parent_id')->orderBy('order');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('order');
    }
}
