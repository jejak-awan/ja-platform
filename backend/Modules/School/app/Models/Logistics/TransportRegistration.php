<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $student_id
 * @property string $vehicle_id
 * @property int $route_id
 * @property string $pickup_point
 * @property string $status
 * @property string $start_date
 * @property string|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Student $student
 * @property-read Vehicle $vehicle
 * @property-read VehicleRoute $route
 */
class TransportRegistration extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

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
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return BelongsTo<VehicleRoute, $this>
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(VehicleRoute::class, 'route_id');
    }
}
