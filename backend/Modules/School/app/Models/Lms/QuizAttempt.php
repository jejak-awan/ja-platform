<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Lms\TopicContent\Quiz;

class QuizAttempt extends Model
{
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

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
