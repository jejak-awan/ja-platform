<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Logistics\Room;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $academic_year_id
 * @property string $semester_id
 * @property string $study_group_id
 * @property string $subject_id
 * @property string $staff_id
 * @property string|null $room_id
 * @property string $day
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read AcademicYear $academicYear
 * @property-read Semester $semester
 * @property-read StudyGroup $studyGroup
 * @property-read Subject $subject
 * @property-read Staff $staff
 * @property-read Room|null $room
 */
class Schedule extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_acad_schedules';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'academic_year_id',
        'semester_id',
        'study_group_id',
        'subject_id',
        'staff_id',
        'room_id',
        'day',
        'start_time',
        'end_time',
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
     * @return BelongsTo<SchoolUnit, $this>
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return BelongsTo<StudyGroup, $this>
     */
    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return HasMany<TeachingJournal, $this>
     */
    public function journals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class);
    }
}
