<?php

namespace Modules\School\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\ScopedByUnit;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_unit_id
 * @property int $student_id
 * @property int $staff_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $type
 * @property string $problem
 * @property string|null $solution
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Institution\SchoolUnit $level
 * @property-read Student $student
 * @property-read \Modules\School\Models\HR\Staff $counselor
 */
class CounselingRecord extends Model
{
    protected $table = 'sch_std_counseling_records';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedByUnit;

    protected $fillable = [
        'school_id',
        'school_unit_id',
        'student_id',
        'staff_id',
        'date',
        'type',
        'problem',
        'solution',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\SchoolUnit, $this>
     */
    public function level(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\SchoolUnit::class, 'school_unit_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\HR\Staff, $this>
     */
    public function counselor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\HR\Staff::class, 'staff_id');
    }
}
