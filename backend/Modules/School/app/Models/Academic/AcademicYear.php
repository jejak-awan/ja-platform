<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $year
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read Collection<int, Semester> $semesters
 */
class AcademicYear extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_acad_years';

    /** @use HasFactory<Factory<static>> */
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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany<Semester, $this>
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * @return HasOne<Semester, $this>
     */
    public function activeSemester(): HasOne
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }
}
