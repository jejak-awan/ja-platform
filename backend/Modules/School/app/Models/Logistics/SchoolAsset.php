<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $room_id
 * @property string $name
 * @property string|null $code
 * @property string|null $category
 * @property int $quantity
 * @property string $condition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Room $room
 */
class SchoolAsset extends Model
{
    protected $table = 'sch_log_assets';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'name',
        'code',
        'category',
        'quantity',
        'condition',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Room, $this>
     */
    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
