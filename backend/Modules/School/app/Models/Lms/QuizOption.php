<?php

namespace Modules\School\Models\Lms;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $question_id
 * @property string $value
 * @property bool $is_correct
 * @property-read QuizQuestion $question
 */
class QuizOption extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_lms_quiz_options';

    protected $fillable = [
        'question_id',
        'value',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * @return BelongsTo<QuizQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
