<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string|null $area
 * @property string $ownership_status
 * @property string|null $certificate_info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Building> $buildings
 */
class LandAsset extends Model
{
    protected $table = 'sch_log_land';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'name',
        'area',
        'ownership_status',
        'certificate_info',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Building, $this>
     */
    public function buildings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Building::class);
    }
}
