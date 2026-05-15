<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property int $id
 * @property int $school_id
 * @property int $workspace_id
 * @property int $staff_id
 * @property string $base_salary
 * @property string $transport_allowance
 * @property string $meal_allowance
 * @property string $other_allowance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolUnit $level
 * @property-read Staff $staff
 */
class SalaryStructure extends Model
{
    protected $table = 'sch_hr_salary_structures';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolUnit, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Staff, $this>
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
