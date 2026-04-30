<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\School\Models\Lms\Topic;

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

    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }
}
