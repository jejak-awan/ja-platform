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
 * @property string $category
 * @property int $points
 * @property string|null $date
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Student $student
 */
class Violation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_std_violations';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'student_id',
        'category',
        'points',
        'date',
        'description',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
