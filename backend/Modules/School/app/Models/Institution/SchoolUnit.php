<?php

namespace Modules\School\Models\Institution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $school_id
 * @property string $level
 * @property string $name
 * @property string $type
 * @property string|null $npsn
 * @property string|null $accreditation
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_active
 * @property string|null $domain
 * @property string|null $subdomain
 * @property-read School $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Academic\Department> $departments
 */
class SchoolUnit extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** @use HasFactory<\Modules\School\Database\Factories\SchoolUnitFactory> */
    use HasFactory;

    protected static function newFactory(): \Modules\School\Database\Factories\SchoolUnitFactory
    {
        return \Modules\School\Database\Factories\SchoolUnitFactory::new();
    }

    protected $table = 'sch_ins_levels';

    protected $fillable = [
        'school_id',
        'level',
        'name',
        'type',
        'npsn',
        'domain',
        'subdomain',
        'kurikulum',
        'accreditation',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Academic\Department, $this>
     */
    public function departments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Academic\Department::class);
    }
}
