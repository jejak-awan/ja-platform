<?php

namespace Modules\School\Models\Student;

use Modules\Core\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $alumni_id
 * @property string $employment_status
 * @property string|null $company_name
 * @property string|null $position
 * @property string|null $university_name
 * @property string|null $major
 * @property string|null $salary_range
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property bool $is_relevant_to_major
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Alumni $alumni
 */
class TracerStudy extends Model
{
    protected $table = 'sch_std_tracer_studies';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Alumni, $this>
     */
    public function alumni(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
