<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $patient_type
 * @property int $patient_id
 * @property string $complaint
 * @property string $treatment
 * @property string|null $medicine_given
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read MorphTo<Model, $this> $patient
 */
class UksVisit extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_ops_uks_visits';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, \Modules\System\Traits\ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'patient_type',
        'patient_id',
        'complaint',
        'treatment',
        'medicine_given',
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
     * @return MorphTo<Model, $this>
     */
    public function patient(): MorphTo
    {
        return $this->morphTo();
    }
}
