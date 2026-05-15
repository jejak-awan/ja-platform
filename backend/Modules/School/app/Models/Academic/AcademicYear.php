<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\System\Traits\ScopedByWorkspace;
use Modules\School\Models\Institution\School;

/**
 * @property int $id
 * @property int $school_id
 * @property string $year
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Semester> $semesters
 */
class AcademicYear extends Model
{
    protected $table = 'sch_acad_years';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'year',
        'is_active',
    ];

    protected $casts = [
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Semester, $this>
     */
    public function semesters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Semester, $this>
     */
    public function activeSemester(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }
}
