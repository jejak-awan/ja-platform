<?php

namespace Modules\School\Models\Logistics;

use Modules\Core\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $student_id
 * @property int $vehicle_id
 * @property int $route_id
 * @property string $pickup_point
 * @property string $status
 * @property string $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\School\Models\Student\Student $student
 * @property-read Vehicle $vehicle
 * @property-read VehicleRoute $route
 */
class TransportRegistration extends Model
{
    use ScopedByWorkspace;
    protected $table = 'sch_log_transport_registrations';

    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'vehicle_id',
        'route_id',
        'pickup_point',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Vehicle, $this>
     */
    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<VehicleRoute, $this>
     */
    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(VehicleRoute::class, 'route_id');
    }
}
