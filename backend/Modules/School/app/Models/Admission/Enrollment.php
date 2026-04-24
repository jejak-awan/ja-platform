<?php

namespace Modules\School\Models\Admission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property int $academic_year_id
 * @property string $registration_number
 * @property string $full_name
 * @property string $gender
 * @property string|null $place_of_birth
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $nisn
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $previous_school
 * @property string $status
 * @property int|null $admitted_student_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolLevel $level
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EnrollmentDocument> $documents
 * @property-read \Modules\School\Models\Student\Student|null $admittedStudent
 */
class Enrollment extends Model
{
    protected $table = 'sch_adm_enrollments';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'academic_year_id',
        'registration_number',
        'full_name',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'nisn',
        'phone',
        'email',
        'previous_school',
        'status',
        'admitted_student_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<EnrollmentDocument, $this>
     */
    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EnrollmentDocument::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function admittedStudent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class, 'admitted_student_id');
    }
}
