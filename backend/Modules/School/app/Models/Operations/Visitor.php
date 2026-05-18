<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
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
 * @property Carbon|null $check_in
 * @property Carbon|null $check_out
 * @property string|null $photo_path
 * @property string|null $id_card_photo_path
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
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
}
