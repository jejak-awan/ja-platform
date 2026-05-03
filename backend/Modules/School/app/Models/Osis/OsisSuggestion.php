<?php

namespace Modules\School\Models\Osis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Student\Student;

/**
 * @property int $id
 * @property int $school_id
 * @property int $student_id
 * @property string $subject
 * @property string $content
 * @property string $status
 * @property string|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read Student $student
 */
class OsisSuggestion extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $table = 'sch_osis_suggestions';

    protected $fillable = [
        'school_id',
        'title',
        'content',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
