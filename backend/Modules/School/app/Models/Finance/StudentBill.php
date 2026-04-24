<?php

namespace Modules\School\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\ScopedByLevel;
use Modules\School\Traits\ScopedBySchool;

/**
 * @property int $id
 * @property int $school_id
 * @property int $school_level_id
 * @property int $student_id
 * @property int $fee_type_id
 * @property int $academic_year_id
 * @property int $semester_id
 * @property int|null $month
 * @property string $amount
 * @property string $paid_amount
 * @property \Illuminate\Support\Carbon $due_date
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read float $balance
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read \Modules\School\Models\Student\Student $student
 * @property-read FeeType $feeType
 * @property-read \Modules\School\Models\Academic\AcademicYear $academicYear
 * @property-read \Modules\School\Models\Academic\Semester $semester
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PaymentTransaction> $transactions
 */
class StudentBill extends Model
{
    protected $table = 'sch_fin_bills';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, ScopedBySchool, ScopedByLevel;

    protected $fillable = [
        'school_id',
        'school_level_id',
        'student_id',
        'fee_type_id',
        'academic_year_id',
        'semester_id',
        'month',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<FeeType, $this>
     */
    public function feeType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\AcademicYear, $this>
     */
    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\AcademicYear::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Academic\Semester, $this>
     */
    public function semester(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Academic\Semester::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<PaymentTransaction, $this>
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->amount - (float) $this->paid_amount;
    }
}
