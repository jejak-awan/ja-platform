<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $room_id
 * @property string $bed_number
 * @property bool $is_available
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read HostelRoom $room
 * @property-read HostelAllocation|null $allocation
 */
class HostelBed extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_log_hostel_beds';

    protected $fillable = [
        'room_id',
        'bed_number',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * @return BelongsTo<HostelRoom, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }

    /**
     * @return HasOne<HostelAllocation, $this>
     */
    public function allocation(): HasOne
    {
        return $this->hasOne(HostelAllocation::class, 'bed_id')->where([
            ['status', '=', 'active'],
        ]);
    }
}
