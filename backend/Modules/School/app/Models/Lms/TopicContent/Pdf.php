<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\School\Models\Lms\Topic;

class Pdf extends Model
{
    protected $table = 'sch_lms_topic_pdfs';

    protected $fillable = ['value'];

    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }
}
