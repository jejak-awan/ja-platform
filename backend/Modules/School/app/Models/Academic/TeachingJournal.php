<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolLevel;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property int $schedule_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $topic
 * @property string|null $materials
 * @property array<int, int>|null $absent_students
 * @property string|null $notes
 * @property string|null $evidence_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolLevel $level
 * @property-read Schedule $schedule
 */
class TeachingJournal extends Model
{
    protected $table = 'sch_acad_teaching_journals';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'schedule_id',
        'date',
        'topic',
        'materials',
        'absent_students',
        'notes',
        'evidence_path',
    ];

    protected $casts = [
        'date' => 'date',
        'absent_students' => 'json',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SchoolLevel, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class, 'school_level_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Schedule, $this>
     */
    public function schedule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
