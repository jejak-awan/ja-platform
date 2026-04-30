<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Lms\QuizQuestion;
use Modules\School\Models\Lms\QuizAttempt;

class Quiz extends Model
{
    protected $table = 'sch_lms_topic_quizzes';

    protected $fillable = [
        'value',
        'max_attempts',
        'max_time',
        'pass_score',
    ];

    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}
