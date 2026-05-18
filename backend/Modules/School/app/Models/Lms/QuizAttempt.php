<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Lms\TopicContent\Quiz;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $quiz_id
 * @property string $student_id
 * @property int|null $score
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property array<string, mixed>|null $answers
 * @property-read Quiz $quiz
 * @property-read Student $student
 */
class QuizAttempt extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_lms_quiz_attempts';

    protected $fillable = [
        'quiz_id',
        'student_id',
        'score',
        'started_at',
        'finished_at',
        'answers',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'answers' => 'json',
    ];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
