<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $building_id
 * @property string $type
 * @property string $name
 * @property int $capacity
 * @property string $condition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Building $building
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SchoolAsset> $assets
 */
class Room extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'sch_log_rooms';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'building_id',
        'type',
        'name',
        'capacity',
        'condition',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Building, $this>
     */
    public function building(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<SchoolAsset, $this>
     */
    public function assets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SchoolAsset::class);
    }
}
