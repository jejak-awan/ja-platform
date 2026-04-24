<?php

namespace Modules\School\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property string $name
 * @property string $code
 * @property string $amount
 * @property string $period
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolLevel $level
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentBill> $bills
 */
class FeeType extends Model
{
    protected $table = 'sch_fin_fee_types';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'name',
        'code',
        'amount',
        'period',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolLevel, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolLevel::class, 'school_level_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<StudentBill, $this>
     */
    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentBill::class);
    }
}
