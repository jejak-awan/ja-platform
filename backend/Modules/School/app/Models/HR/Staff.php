<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\ScopedByWorkspace;
use Modules\School\Traits\ScopedBySchool;
use Modules\School\Models\Institution\School;

/**
 * @property int $id
 * @property int $school_id
 * @property int $user_id
 * @property string $nuptk
 * @property string $nik
 * @property string $full_name
 * @property string|null $place_of_birth
 * @property string|null $date_of_birth
 * @property string $gender
 * @property string $religion
 * @property string $employment_status
 * @property string $ptk_type
 * @property string|null $sk_pengangkatan
 * @property string|null $tmt_pengangkatan
 * @property string|null $sk_penugasan
 * @property string|null $tmt_penugasan
 * @property string|null $last_education
 * @property string|null $major
 * @property bool $certification_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\Core\Models\User $user
 */
class Staff extends Model
{
    protected $table = 'sch_hr_staff';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, SoftDeletes, ScopedBySchool, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'user_id',
        'nuptk',
        'nik',
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'religion',
        'employment_status',
        'ptk_type',
        'sk_pengangkatan',
        'tmt_pengangkatan',
        'sk_penugasan',
        'tmt_penugasan',
        'last_education',
        'major',
        'certification_status',
        'is_shared',
        'metadata',
    ];

    protected $casts = [
        'certification_status' => 'boolean',
        'metadata' => 'array',
        'date_of_birth' => 'date',
        'tmt_pengangkatan' => 'date',
        'tmt_penugasan' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\Core\Models\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class);
    }
}
