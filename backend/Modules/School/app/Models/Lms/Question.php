<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $question_bank_id
 * @property string $content
 * @property string $type
 * @property string $level
 * @property array<string, mixed>|null $options
 * @property string $answer
 * @property string|null $media_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read QuestionBank $questionBank
 */
class Question extends Model
{
    protected $table = 'sch_lms_questions';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'content',
        'type',
        'level',
        'options',
        'answer',
        'media_path',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<QuestionBank, $this>
     */
    public function questionBank(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }
}
