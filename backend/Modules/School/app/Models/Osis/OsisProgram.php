<?php

namespace Modules\School\Models\Osis;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $name
 * @property string|null $description
 * @property Carbon $planned_date
 * @property string $status
 * @property string $estimated_budget
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read Collection<int, OsisFinance> $finances
 */
class OsisProgram extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<Factory<static>> */
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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany<OsisFinance, $this>
     */
    public function finances(): HasMany
    {
        return $this->hasMany(OsisFinance::class, 'program_id');
    }
}
