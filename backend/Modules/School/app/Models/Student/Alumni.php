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
 * @property string $graduation_year
 * @property string|null $current_activity
 * @property string|null $institution_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Student $student
 */
class Alumni extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $table = 'sch_std_alumni';

    protected $fillable = [
        'student_id',
        'graduation_year',
        'current_activity',
        'institution_name',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
