<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $institution
 * @property string|null $purpose
 * @property string|null $target_person
 * @property \Illuminate\Support\Carbon|null $check_in
 * @property \Illuminate\Support\Carbon|null $check_out
 * @property string|null $photo_path
 * @property string|null $id_card_photo_path
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolUnit $level
 */
class Visitor extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'sch_ops_visitors';

    use ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'name',
        'phone',
        'institution',
        'purpose',
        'target_person',
        'check_in',
        'check_out',
        'photo_path',
        'id_card_photo_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolUnit, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolUnit::class, 'workspace_id');
    }
}
