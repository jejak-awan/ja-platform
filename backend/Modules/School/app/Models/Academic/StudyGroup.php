<?php

namespace Modules\School\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\HR\Staff;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property int $id
 * @property int $school_id
 * @property int $workspace_id
 * @property int|null $department_id
 * @property int $academic_year_id
 * @property int|null $homeroom_teacher_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read Department|null $department
 * @property-read AcademicYear $academicYear
 * @property-read Staff|null $homeroomTeacher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students
 */
class StudyGroup extends Model
{
    protected $table = 'sch_acad_study_groups';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
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
        return $this->belongsTo(SchoolUnit::class, 'workspace_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Department, $this>
     */
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Staff, $this>
     */
    public function homeroomTeacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class, 'homeroom_teacher_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Student, $this>
     */
    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'sch_acad_study_group_members');
    }
}
