<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\School\Models\Lms\Topic;

/**
 * @property int $id
 * @property string $value
 * @property string|null $poster_path
 * @property int|null $width
 * @property int|null $height
 * @property int|null $duration
 */
class Video extends Model
{
    protected $table = 'sch_lms_topic_videos';

    protected $fillable = [
        'value',
        'poster_path',
        'width',
        'height',
        'duration',
    ];

    /**
     * @return MorphOne<Topic, $this>
     */
    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }
}
