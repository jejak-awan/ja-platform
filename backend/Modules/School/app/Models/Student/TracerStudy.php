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
 * @property int $alumni_id
 * @property string $employment_status
 * @property string|null $company_name
 * @property string|null $position
 * @property string|null $university_name
 * @property string|null $major
 * @property string|null $salary_range
 * @property Carbon|null $start_date
 * @property bool $is_relevant_to_major
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Alumni $alumni
 */
class TracerStudy extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_std_tracer_studies';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'alumni_id',
        'employment_status',
        'company_name',
        'position',
        'university_name',
        'major',
        'salary_range',
        'start_date',
        'is_relevant_to_major',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_relevant_to_major' => 'boolean',
    ];

    /**
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
