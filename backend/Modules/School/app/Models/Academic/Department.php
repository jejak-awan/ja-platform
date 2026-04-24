<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\SchoolLevel;

/**
 * @property int $id
 * @property int $school_level_id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read SchoolLevel $level
 */
class Department extends Model
{
    protected $table = 'sch_acad_departments';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'school_level_id',
        'name',
        'code',
        'description',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SchoolLevel, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class, 'school_level_id');
    }
}
