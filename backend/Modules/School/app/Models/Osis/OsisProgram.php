<?php

namespace Modules\School\Models\Osis;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\School;

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $planned_date
 * @property string $status
 * @property string $estimated_budget
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OsisFinance> $finances
 */
class OsisProgram extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $table = 'sch_osis_programs';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'planned_date',
        'status',
        'estimated_budget',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'estimated_budget' => 'decimal:2',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OsisFinance, $this>
     */
    public function finances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OsisFinance::class, 'program_id');
    }
}
