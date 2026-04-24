<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $land_asset_id
 * @property string $name
 * @property string|null $area
 * @property int $floor_count
 * @property string|null $year_built
 * @property string $condition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read LandAsset $landAsset
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Room> $rooms
 */
class Building extends Model
{
    protected $table = 'sch_log_buildings';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'land_asset_id',
        'name',
        'area',
        'floor_count',
        'year_built',
        'condition',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<LandAsset, $this>
     */
    public function landAsset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LandAsset::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Room, $this>
     */
    public function rooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Room::class);
    }
}
