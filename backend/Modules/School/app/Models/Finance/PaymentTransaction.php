<?php

namespace Modules\School\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\School\Traits\HasVerificationHash;

/**
 * @property int $id
 * @property int $school_id
 * @property int $student_bill_id
 * @property string $amount
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string $payment_method
 * @property string|null $reference_number
 * @property string|null $verification_hash
 * @property string|null $qr_path
 * @property string $status
 * @property string|null $notes
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\School\Models\Institution\School $school
 * @property-read StudentBill $bill
 * @property-read \Modules\Core\Models\User|null $verifier
 */
class PaymentTransaction extends Model
{
    protected $table = 'sch_fin_transactions';

    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory<static>> */
    use HasFactory, HasVerificationHash;

    protected $fillable = [
        'school_id',
        'student_bill_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'verification_hash',
        'qr_path',
        'status',
        'notes',
        'verified_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Institution\School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Institution\School::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<StudentBill, $this>
     */
    public function bill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\Core\Models\User, $this>
     */
    public function verifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class, 'verified_by');
    }
}
