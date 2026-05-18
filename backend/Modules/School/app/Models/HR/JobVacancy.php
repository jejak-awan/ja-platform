<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $company_name
 * @property string $position
 * @property string|null $description
 * @property array<int, string>|null $requirements
 * @property string|null $location
 * @property string|null $salary_range
 * @property string $status
 * @property Carbon|null $deadline
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, JobApplication> $applications
 */
class JobVacancy extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_hr_job_vacancies';

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'company_name',
        'position',
        'description',
        'requirements',
        'location',
        'salary_range',
        'status',
        'deadline',
    ];

    protected $casts = [
        'requirements' => 'array',
        'deadline' => 'date',
    ];

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'vacancy_id');
    }
}
