<?php

namespace Modules\School\Models\Osis;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $table = 'sch_osis_programs';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'budget',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
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
