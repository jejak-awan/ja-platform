<?php

namespace Modules\School\Models\Academic;

use Modules\Core\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Student\Student;

/**
 * @property int $id
 * @property int $student_id
 * @property int $academic_year_id
 * @property int $semester_id
 * @property string $date
 * @property string $status
 * @property string|null $notes
 * @property string|null $attachment_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Student\Student $student
 * @property-read AcademicYear $academicYear
 * @property-read Semester $semester
 */
class Attendance extends Model
{
    protected $table = 'sch_acad_attendances';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByWorkspace;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'date',
        'status',
        'notes',
        'attachment_path',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class);
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
}
