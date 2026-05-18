<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $student_id
 * @property string $bed_id
 * @property string $start_date
 * @property string|null $end_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Student $student
 * @property-read HostelBed $bed
 */
class HostelAllocation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_log_hostel_allocations';

    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'bed_id',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<HostelBed, $this>
     */
    public function bed(): BelongsTo
    {
        return $this->belongsTo(HostelBed::class, 'bed_id');
    }
}
