<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $block_id
 * @property string $room_number
 * @property int $capacity
 * @property array<string, mixed>|null $features
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read HostelBlock $block
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HostelBed> $beds
 */
class HostelRoom extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_log_hostel_rooms';

    use SoftDeletes;

    protected $fillable = [
        'block_id',
        'room_number',
        'capacity',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<HostelBlock, $this>
     */
    public function block(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HostelBlock::class, 'block_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<HostelBed, $this>
     */
    public function beds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HostelBed::class, 'room_id');
    }
}
