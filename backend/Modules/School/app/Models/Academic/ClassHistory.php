<?php

namespace Modules\School\Models\Academic;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property int $study_group_id
 * @property int $academic_year_id
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Student\Student $student
 * @property-read StudyGroup $studyGroup
 * @property-read AcademicYear $academicYear
 */
class ClassHistory extends Model
{
    use ScopedByWorkspace;
    protected $table = 'sch_acad_class_histories';

    protected $fillable = [
        'student_id',
        'study_group_id',
        'academic_year_id',
        'status',
        'notes',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<StudyGroup, $this>
     */
    public function studyGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
