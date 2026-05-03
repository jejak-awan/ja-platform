<?php

namespace Modules\School\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $school_id
 * @property int $staff_id
 * @property string $date
 * @property string $status
 * @property string|null $check_in
 * @property string|null $check_out
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Staff $staff
 */
class StaffAttendance extends Model
{
    protected $table = 'sch_hr_attendances';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'school_id',
        'staff_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'notes',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Staff, $this>
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
