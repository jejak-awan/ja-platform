<?php

namespace Modules\School\Models\Lms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Academic\StudyGroup;

/**
 * @property int $id
 * @property int $exam_id
 * @property int $study_group_id
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property bool $is_active
 * @property string|null $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Exam $exam
 * @property-read \Modules\School\Models\Academic\StudyGroup $studyGroup
 */
class ExamSession extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $table = 'sch_cbt_sessions';

    protected $fillable = [
        'exam_id',
        'study_group_id',
        'start_time',
        'end_time',
        'is_active',
        'token',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Exam, $this>
     */
    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\StudyGroup, $this>
     */
    public function studyGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\StudyGroup::class, 'study_group_id');
    }
}
