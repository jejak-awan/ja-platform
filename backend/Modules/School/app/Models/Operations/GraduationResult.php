<?php

namespace Modules\School\Models\Operations;

use Modules\Core\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Student\Student;

/**
 * @property int $id
 * @property int $student_id
 * @property string $status
 * @property int $graduation_year
 * @property array<string, mixed>|null $grades
 * @property string|null $certificate_number
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property \Modules\School\Models\Student\Student $student
 */
class GraduationResult extends Model
{
    use ScopedByWorkspace;
    protected $table = 'sch_grad_results';

    protected $fillable = [
        'student_id',
        'status',
        'graduation_year',
        'grades',
        'certificate_number',
        'published_at',
    ];

    protected $casts = [
        'grades' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
