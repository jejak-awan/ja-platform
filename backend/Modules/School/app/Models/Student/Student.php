<?php

namespace Modules\School\Models\Student;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Database\Factories\StudentFactory;
use Modules\School\Models\Academic\Attendance;
use Modules\School\Models\Academic\Department;
use Modules\School\Models\Academic\Grade;
use Modules\School\Models\Academic\StudyGroup;
use Modules\School\Models\Admission\Enrollment;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Operations\UksVisit;
use Modules\School\Traits\HasVerificationHash;
use Modules\School\Traits\ScopedBySchool;
use Modules\System\Models\User;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string|null $user_id
 * @property string $status
 * @property string|null $department_id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read Department|null $department
 * @property-read Collection<int, StudyGroup> $studyGroups
 * @property-read Collection<int, Attendance> $attendances
 * @property-read Collection<int, Violation> $violations
 * @property-read Collection<int, Achievement> $achievements
 * @property-read Collection<int, UksVisit> $uksVisits
 * @property-read User|null $user
 */
class Student extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_std_students';

    /** @use HasFactory<StudentFactory> */
    use HasFactory, HasVerificationHash, ScopedBySchool, ScopedByWorkspace, SoftDeletes;

    protected $casts = [
        'date_of_birth' => 'date',
        'metadata' => 'array',
    ];

    protected static function newFactory(): StudentFactory
    {
        return StudentFactory::new();
    }

    protected $fillable = [
        'school_id',
        'workspace_id',
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
        'is_shared',
        'metadata',
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
     * @return BelongsToMany<StudyGroup, $this>
     */
    public function studyGroups(): BelongsToMany
    {
        return $this->belongsToMany(StudyGroup::class, 'sch_acad_study_group_members');
    }

    /**
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * @return HasMany<Violation, $this>
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    /**
     * @return HasMany<Achievement, $this>
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /**
     * @return MorphMany<UksVisit, $this>
     */
    public function uksVisits(): MorphMany
    {
        return $this->morphMany(UksVisit::class, 'patient');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Grade, $this>
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * @return HasOne<Enrollment, $this>
     */
    public function activeEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class)->latest();
    }
}
