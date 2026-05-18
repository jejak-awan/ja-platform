<?php

namespace Modules\School\Models\Admission;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\School\Models\Academic\AcademicYear;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Institution\SchoolUnit;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $workspace_id
 * @property string $academic_year_id
 * @property string $registration_number
 * @property string $full_name
 * @property string $gender
 * @property string|null $place_of_birth
 * @property Carbon|null $date_of_birth
 * @property string|null $nisn
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $previous_school
 * @property string $status
 * @property int|null $admitted_student_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read School $school
 * @property-read SchoolUnit $level
 * @property-read AcademicYear $academicYear
 * @property-read Collection<int, EnrollmentDocument> $documents
 * @property-read Student|null $admittedStudent
 */
class Enrollment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sch_adm_enrollments';

    /** @use HasFactory<Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'school_id',
        'workspace_id',
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
     * @return HasMany<EnrollmentDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EnrollmentDocument::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function admittedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'admitted_student_id');
    }
}
