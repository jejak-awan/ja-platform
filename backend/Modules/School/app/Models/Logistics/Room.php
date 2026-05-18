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
 * @property string $building_id
 * @property string $type
 * @property string $name
 * @property int $capacity
 * @property string $condition
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Building $building
 * @property-read Collection<int, SchoolAsset> $assets
 */
class Room extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_log_rooms';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'building_id',
        'type',
        'name',
        'capacity',
        'condition',
    ];

    /**
     * @return BelongsTo<Building, $this>
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * @return HasMany<SchoolAsset, $this>
     */
    public function assets(): HasMany
    {
        return $this->hasMany(SchoolAsset::class);
    }
}
