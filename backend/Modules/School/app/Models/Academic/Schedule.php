<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Logistics\Room;
use Modules\School\Traits\ScopedByUnit;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_unit_id
 * @property int $academic_year_id
 * @property int $semester_id
 * @property int $study_group_id
 * @property int $subject_id
 * @property int $staff_id
 * @property int|null $room_id
 * @property string $day
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read AcademicYear $academicYear
 * @property-read Semester $semester
 * @property-read StudyGroup $studyGroup
 * @property-read Subject $subject
 * @property-read Staff $staff
 * @property-read \Modules\School\Models\Logistics\Room|null $room
 */
class Schedule extends Model
{
    protected $table = 'sch_acad_schedules';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByUnit;

    protected $fillable = [
        'school_id',
        'school_unit_id',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<SchoolUnit, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SchoolUnit::class, 'school_unit_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Semester, $this>
     */
    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<StudyGroup, $this>
     */
    public function studyGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Subject, $this>
     */
    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Staff, $this>
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Logistics\Room, $this>
     */
    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Logistics\Room::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<TeachingJournal, $this>
     */
    public function journals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeachingJournal::class);
    }
}
