<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string|null $department_id
 * @property string $academic_year_id
 * @property int|null $homeroom_teacher_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read Department|null $department
 * @property-read AcademicYear $academicYear
 * @property-read Staff|null $homeroomTeacher
 * @property-read Collection<int, Student> $students
 */
class StudyGroup extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_acad_study_groups';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
        'department_id',
        'academic_year_id',
        'homeroom_teacher_id',
        'name',
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
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'homeroom_teacher_id');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'sch_acad_study_group_members');
    }
}
