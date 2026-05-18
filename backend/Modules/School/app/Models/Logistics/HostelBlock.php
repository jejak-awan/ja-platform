<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $name
 * @property string|null $description
 * @property string $gender
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, HostelRoom> $rooms
 */
class HostelBlock extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_log_hostel_blocks';

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'gender',
    ];

    /**
     * @return HasMany<HostelRoom, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(HostelRoom::class, 'block_id');
    }
}
