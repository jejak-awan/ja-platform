<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\School\Models\Student\Student;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property int $vacancy_id
 * @property int|null $student_id
 * @property string|null $resume_path
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read JobVacancy $vacancy
 * @property-read Student|null $student
 */
class JobApplication extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'sch_hr_job_applications';

    use SoftDeletes;

    protected $fillable = [
        'vacancy_id',
        'student_id',
        'resume_path',
        'status',
        'notes',
    ];

    /**
     * @return BelongsTo<JobVacancy, $this>
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class, 'vacancy_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
