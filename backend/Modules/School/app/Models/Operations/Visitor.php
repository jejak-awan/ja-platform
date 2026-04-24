<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
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
 * @property-read \Modules\School\Models\Institution\SchoolLevel $level
 */
class Visitor extends Model
{
    protected $table = 'sch_ops_visitors';

    use ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolLevel, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolLevel::class, 'school_level_id');
    }
}
