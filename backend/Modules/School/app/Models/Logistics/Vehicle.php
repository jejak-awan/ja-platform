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
 * @property string $plate_number
 * @property string $model
 * @property int $capacity
 * @property string|null $driver_name
 * @property string|null $driver_phone
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, TransportRegistration> $registrations
 */
class Vehicle extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_log_vehicles';

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'plate_number',
        'model',
        'capacity',
        'driver_name',
        'driver_phone',
        'status',
    ];

    /**
     * @return HasMany<TransportRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(TransportRegistration::class);
    }
}
