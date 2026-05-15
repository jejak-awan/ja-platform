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
 * @property string $period
 * @property string $basic_salary
 * @property string $total_allowance
 * @property string $total_deduction
 * @property string $net_salary
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolUnit $level
 * @property-read Staff $staff
 */
class Payroll extends Model
{
    protected $table = 'sch_hr_payrolls';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'staff_id',
        'period',
        'basic_salary',
        'total_allowance',
        'total_deduction',
        'net_salary',
        'status',
        'payment_date',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
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
