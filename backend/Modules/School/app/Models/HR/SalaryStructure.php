<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $staff_id
 * @property string $base_salary
 * @property string $transport_allowance
 * @property string $meal_allowance
 * @property string $other_allowance
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read Staff $staff
 */
class SalaryStructure extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_hr_salary_structures';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'staff_id',
        'base_salary',
        'transport_allowance',
        'meal_allowance',
        'other_allowance',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'other_allowance' => 'decimal:2',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<SchoolUnit, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
