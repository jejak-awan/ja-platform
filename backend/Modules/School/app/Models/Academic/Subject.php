<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property string $code
 * @property string $name
 * @property string|null $group
 * @property int|null $kkm
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 */
class Subject extends Model
{
    protected $table = 'sch_acad_subjects';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'group',
        'kkm',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }
}
