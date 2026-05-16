<?php

namespace Modules\School\Models\HR;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $school_id
 * @property string $company_name
 * @property string $position
 * @property string|null $description
 * @property array<int, string>|null $requirements
 * @property string|null $location
 * @property string|null $salary_range
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, JobApplication> $applications
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<JobApplication, $this>
     */
    public function applications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JobApplication::class, 'vacancy_id');
    }
}
