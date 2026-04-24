<?php

namespace Modules\School\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property int $academic_year_id
 * @property string $category
 * @property string $planned_amount
 * @property string $actual_amount
 * @property bool $is_warning
 * @property int $warning_threshold
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 */
class Budget extends Model
{
    protected $table = 'sch_fin_budgets';

    use ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'academic_year_id',
        'category',
        'planned_amount',
        'actual_amount',
        'is_warning',
        'warning_threshold',
        'notes',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'is_warning' => 'boolean',
        'warning_threshold' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }
}
