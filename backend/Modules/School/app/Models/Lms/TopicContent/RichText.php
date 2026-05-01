<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\School\Models\Lms\Topic;

/**
 * @property int $id
 * @property string $value
 */
class RichText extends Model
{
    protected $table = 'sch_lms_topic_richtexts';

    protected $fillable = ['value'];

    /**
     * @return MorphOne<Topic, $this>
     */
    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }
}
