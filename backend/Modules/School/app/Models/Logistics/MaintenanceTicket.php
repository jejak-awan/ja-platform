<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\System\Traits\ScopedByWorkspace;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\HR\Staff;

/**
 * @property int $id
 * @property int $school_id
 * @property int $workspace_id
 * @property int $school_asset_id
 * @property int $reported_by
 * @property \Illuminate\Support\Carbon $date_reported
 * @property string $issue_description
 * @property string $priority
 * @property string $status
 * @property string|null $resolution_notes
 * @property \Illuminate\Support\Carbon|null $date_resolved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SchoolUnit, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SchoolAsset, $this>
     */
    public function asset(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolAsset::class, 'school_asset_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Staff, $this>
     */
    public function reporter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reported_by');
    }
}
