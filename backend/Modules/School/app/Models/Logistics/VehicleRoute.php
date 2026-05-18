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
 * @property string $start_location
 * @property string $end_location
 * @property array<int, string>|null $stops
 * @property string $fee
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, TransportRegistration> $registrations
 */
class VehicleRoute extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_log_transport_routes';

    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'start_location',
        'end_location',
        'stops',
        'fee',
    ];

    protected $casts = [
        'stops' => 'array',
        'fee' => 'decimal:2',
    ];

    /**
     * @return HasMany<TransportRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(TransportRegistration::class, 'route_id');
    }
}
