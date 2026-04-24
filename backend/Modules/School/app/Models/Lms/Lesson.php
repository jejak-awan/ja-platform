<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $section_id
 * @property string $title
 * @property string $slug
 * @property string $type
 * @property string|null $content
 * @property string|null $video_url
 * @property string|null $file_path
 * @property int|null $exam_id
 * @property int|null $duration
 * @property int $sort_order
 * @property bool $is_preview
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Section $section
 * @property-read Exam|null $exam
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LessonProgress> $progresses
 */
class Lesson extends Model
{
    use SoftDeletes;

    protected $table = 'sch_lms_lessons';

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'type',
        'content',
        'video_url',
        'file_path',
        'exam_id',
        'duration',
        'sort_order',
        'is_preview',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Section, $this>
     */
    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Exam, $this>
     */
    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LessonProgress, $this>
     */
    public function progresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
