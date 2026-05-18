<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $room_id
 * @property string $name
 * @property string|null $code
 * @property string|null $category
 * @property int $quantity
 * @property string $condition
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Room $room
 */
class SchoolAsset extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_log_assets';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'room_id',
        'name',
        'code',
        'category',
        'quantity',
        'condition',
    ];

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
