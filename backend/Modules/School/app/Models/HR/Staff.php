<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\School\Traits\ScopedBySchool;
use Modules\System\Models\User;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $user_id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read User $user
 */
class Staff extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_hr_staff';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedBySchool, ScopedByWorkspace, SoftDeletes;

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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
