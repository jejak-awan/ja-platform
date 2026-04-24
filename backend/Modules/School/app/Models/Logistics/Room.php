<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $building_id
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
    protected $table = 'sch_log_rooms';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

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
