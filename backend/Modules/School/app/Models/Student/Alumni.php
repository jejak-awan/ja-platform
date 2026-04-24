<?php

namespace Modules\School\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $student_id
 * @property string $graduation_year
 * @property string|null $current_activity
 * @property string|null $institution_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Student $student
 */
class Alumni extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $table = 'sch_std_alumni';

    protected $fillable = [
        'student_id',
        'graduation_year',
        'current_activity',
        'institution_name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
