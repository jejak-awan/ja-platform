<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $room_id
 * @property string $bed_number
 * @property bool $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read HostelRoom $room
 * @property-read HostelAllocation|null $allocation
 */
class HostelBed extends Model
{
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<HostelRoom, $this>
     */
    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<HostelAllocation, $this>
     */
    public function allocation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(HostelAllocation::class, 'bed_id')->where([
            ['status', '=', 'active']
        ]);
    }
}

