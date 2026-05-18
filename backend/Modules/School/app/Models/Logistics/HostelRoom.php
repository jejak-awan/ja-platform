<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $block_id
 * @property string $room_number
 * @property int $capacity
 * @property array<string, mixed>|null $features
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read HostelBlock $block
 * @property-read Collection<int, HostelBed> $beds
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
     * @return BelongsTo<HostelBlock, $this>
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(HostelBlock::class, 'block_id');
    }

    /**
     * @return HasMany<HostelBed, $this>
     */
    public function beds(): HasMany
    {
        return $this->hasMany(HostelBed::class, 'room_id');
    }
}
