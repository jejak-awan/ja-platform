<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $school_id
 * @property string $plate_number
 * @property string $model
 * @property int $capacity
 * @property string|null $driver_name
 * @property string|null $driver_phone
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TransportRegistration> $registrations
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TransportRegistration, $this>
     */
    public function registrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransportRegistration::class);
    }
}
