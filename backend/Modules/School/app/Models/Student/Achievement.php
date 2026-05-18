<?php

namespace Modules\School\Models\Student;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $student_id
 * @property string $title
 * @property string $level
 * @property string $type
 * @property string|null $date
 * @property string|null $evidence_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Student $student
 */
class Achievement extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_std_achievements';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'student_id',
        'title',
        'level',
        'type',
        'date',
        'evidence_path',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
