<?php

namespace Modules\School\Models\Institution;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\School\Database\Factories\SchoolUnitFactory;
use Modules\School\Models\Academic\Department;

/**
 * @property string $id
 * @property string $school_id
 * @property string $level
 * @property string $name
 * @property string $type
 * @property string|null $npsn
 * @property string|null $accreditation
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $is_active
 * @property string|null $domain
 * @property string|null $subdomain
 * @property-read School $school
 * @property-read Collection<int, Department> $departments
 */
class SchoolUnit extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<SchoolUnitFactory> */
    use HasFactory;

    protected static function newFactory(): SchoolUnitFactory
    {
        return SchoolUnitFactory::new();
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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany<Department, $this>
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
