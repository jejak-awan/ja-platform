<?php

namespace Modules\School\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\School\Traits\ScopedByLevel;
use Modules\School\Traits\ScopedBySchool;
use Modules\School\Traits\HasVerificationHash;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolLevel;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Lms\ExamResult;
use Modules\School\Models\Operations\UksVisit;
use Modules\School\Models\Finance\StudentBill;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property int|null $user_id
 * @property string $status
 * @property int|null $department_id
 * @property string|null $nisn
 * @property string|null $nis
 * @property string|null $nik
 * @property string $full_name
 * @property string|null $place_of_birth
 * @property string|null $date_of_birth
 * @property string $gender
 * @property string $religion
 * @property string|null $address
 * @property string|null $rt
 * @property string|null $rw
 * @property string|null $dusun
 * @property string|null $desa_kelurahan
 * @property string|null $kecamatan
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $father_name
 * @property string|null $father_nik
 * @property int|null $father_birth_year
 * @property string|null $father_education
 * @property string|null $father_occupation
 * @property string|null $father_income
 * @property string|null $mother_name
 * @property string|null $mother_nik
 * @property int|null $mother_birth_year
 * @property string|null $mother_education
 * @property string|null $mother_occupation
 * @property string|null $mother_income
 * @property string|null $guardian_name
 * @property string|null $guardian_nik
 * @property int|null $guardian_birth_year
 * @property string|null $guardian_education
 * @property string|null $guardian_occupation
 * @property string|null $guardian_income
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read School $school
 * @property-read SchoolLevel $level
 * @property-read Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudyGroup> $studyGroups
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendance> $attendances
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Violation> $violations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Achievement> $achievements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ExamResult> $examResults
 * @property-read \Illuminate\Database\Eloquent\Collection<int, UksVisit> $uksVisits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentBill> $bills
 * @property-read \Modules\Core\Models\User|null $user
 */
class Student extends Model
{
    protected $table = 'sch_std_students';

    /** @use HasFactory<\Modules\School\Database\Factories\StudentFactory> */
    use HasFactory, SoftDeletes, ScopedBySchool, ScopedByLevel, HasVerificationHash;

    protected static function newFactory(): \Modules\School\Database\Factories\StudentFactory
    {
        return \Modules\School\Database\Factories\StudentFactory::new();
    }

    protected $fillable = [
        'school_id',
        'school_level_id',
        'user_id',
        'status',
        'department_id',
        'nisn',
        'nis',
        'nik',
        'full_name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'religion',
        'address',
        'rt',
        'rw',
        'dusun',
        'desa_kelurahan',
        'kecamatan',
        'phone',
        'email',
        'father_name',
        'father_nik',
        'father_birth_year',
        'father_education',
        'father_occupation',
        'father_income',
        'mother_name',
        'mother_nik',
        'mother_birth_year',
        'mother_education',
        'mother_occupation',
        'mother_income',
        'guardian_name',
        'guardian_nik',
        'guardian_birth_year',
        'guardian_education',
        'guardian_occupation',
        'guardian_income',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Department, $this>
     */
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<StudyGroup, $this>
     */
    public function studyGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(StudyGroup::class, 'sch_acad_study_group_members');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Attendance, $this>
     */
    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Violation, $this>
     */
    public function violations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Achievement, $this>
     */
    public function achievements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ExamResult, $this>
     */
    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExamResult::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany<UksVisit, $this>
     */
    public function uksVisits(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(UksVisit::class, 'patient');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<StudentBill, $this>
     */
    public function bills(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentBill::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\Core\Models\User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\School\Models\Academic\Grade, $this>
     */
    public function grades(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\School\Models\Academic\Grade::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\Modules\School\Models\Admission\Enrollment, $this>
     */
    public function activeEnrollment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\School\Models\Admission\Enrollment::class)->latest();
    }
}
