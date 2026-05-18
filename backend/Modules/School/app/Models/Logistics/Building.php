<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $land_asset_id
 * @property string $name
 * @property string|null $area
 * @property int $floor_count
 * @property string|null $year_built
 * @property string $condition
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LandAsset $landAsset
 * @property-read Collection<int, Room> $rooms
 */
class Building extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_log_buildings';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'land_asset_id',
        'name',
        'area',
        'floor_count',
        'year_built',
        'condition',
    ];

    /**
     * @return BelongsTo<LandAsset, $this>
     */
    public function landAsset(): BelongsTo
    {
        return $this->belongsTo(LandAsset::class);
    }

    /**
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
