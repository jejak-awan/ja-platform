<?php

namespace Modules\School\Models\Lms;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\School\Models\Lms\TopicContent\Quiz;

/**
 * @property int $id
 * @property int $quiz_id
 * @property string $type
 * @property string $value
 * @property int $score
 * @property int $order
 * @property-read Quiz $quiz
 * @property-read \Illuminate\Support\Collection<int, QuizOption> $options
 */
class QuizQuestion extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_lms_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'type',
        'value',
        'score',
        'order',
    ];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return HasMany<QuizOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id');
    }
}
