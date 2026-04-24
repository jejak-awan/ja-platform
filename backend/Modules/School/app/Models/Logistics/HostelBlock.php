<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string|null $description
 * @property string $gender
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HostelRoom> $rooms
 */
class HostelBlock extends Model
{
    protected $table = 'sch_log_hostel_blocks';

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'gender',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<HostelRoom, $this>
     */
    public function rooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HostelRoom::class, 'block_id');
    }
}
