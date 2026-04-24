<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property string $patient_type
 * @property int $patient_id
 * @property string $complaint
 * @property string $treatment
 * @property string|null $medicine_given
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolLevel $level
 * @property-read \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, $this> $patient
 */
class UksVisit extends Model
{
    protected $table = 'sch_ops_uks_visits';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, \Modules\School\Traits\ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'patient_type',
        'patient_id',
        'complaint',
        'treatment',
        'medicine_given',
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function patient(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
