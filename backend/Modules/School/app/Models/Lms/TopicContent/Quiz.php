<?php

namespace Modules\School\Models\Lms\TopicContent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\School\Models\Lms\Topic;
use Modules\School\Models\Lms\QuizQuestion;
use Modules\School\Models\Lms\QuizAttempt;

/**
 * @property int $id
 * @property string|null $value
 * @property int|null $max_attempts
 * @property int|null $max_time
 * @property int|null $pass_score
 * @property-read \Illuminate\Support\Collection<int, QuizQuestion> $questions
 * @property-read \Illuminate\Support\Collection<int, QuizAttempt> $attempts
 */
class Quiz extends Model
{
    protected $table = 'sch_lms_topic_quizzes';

    protected $fillable = [
        'value',
        'max_attempts',
        'max_time',
        'pass_score',
    ];

    /**
     * @return MorphOne<Topic, $this>
     */
    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    /**
     * @return HasMany<QuizQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('order');
    }

    /**
     * @return HasMany<QuizAttempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}
