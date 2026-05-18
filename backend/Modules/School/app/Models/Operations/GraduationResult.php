<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $student_id
 * @property string $status
 * @property int $graduation_year
 * @property array<string, mixed>|null $grades
 * @property string|null $certificate_number
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Student $student
 */
class GraduationResult extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

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
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
