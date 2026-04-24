<?php

namespace Modules\School\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property string $category
 * @property string $amount
 * @property \Illuminate\Support\Carbon $date
 * @property string|null $description
 * @property string|null $attachment_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolLevel $level
 */
class Expense extends Model
{
    protected $table = 'sch_fin_expenses';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'category',
        'amount',
        'date',
        'description',
        'attachment_path',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolLevel, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolLevel::class, 'school_level_id');
    }
}
