<?php

namespace Modules\School\Models\HR;

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
 * @property string $start_time
 * @property string $end_time
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read Collection<int, Staff> $staff
 */
class StaffShift extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_hr_shifts';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'name',
        'start_time',
        'end_time',
        'description',
    ];

    /**
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return HasMany<Staff, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
