<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $school_asset_id
 * @property int $reported_by
 * @property Carbon $date_reported
 * @property string $issue_description
 * @property string $priority
 * @property string $status
 * @property string|null $resolution_notes
 * @property Carbon|null $date_resolved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read SchoolAsset $asset
 * @property-read Staff $reporter
 */
class MaintenanceTicket extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_log_maintenance_tickets';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'school_asset_id',
        'reported_by',
        'date_reported',
        'issue_description',
        'priority',
        'status',
        'resolution_notes',
        'date_resolved',
    ];

    protected $casts = [
        'date_reported' => 'date',
        'date_resolved' => 'date',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<SchoolUnit, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return BelongsTo<SchoolAsset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(SchoolAsset::class, 'school_asset_id');
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reported_by');
    }
}
