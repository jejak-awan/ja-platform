<?php

namespace Modules\School\Models\Institution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $school_id
 * @property string $level
 * @property string $name
 * @property string $type
 * @property string|null $npsn
 * @property string|null $accreditation
 * @property array<string, mixed>|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\School\Models\Academic\Department> $departments
 */
class SchoolLevel extends Model
{
    /** @use HasFactory<\Modules\School\Database\Factories\SchoolLevelFactory> */
    use HasFactory;

    protected static function newFactory(): \Modules\School\Database\Factories\SchoolLevelFactory
    {
        return \Modules\School\Database\Factories\SchoolLevelFactory::new();
    }

    protected $table = 'sch_ins_levels';

    protected $fillable = [
        'school_id',
        'level',
        'name',
        'type',
        'npsn',
        'accreditation',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
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
