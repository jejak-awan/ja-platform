<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $item_id
 * @property int|null $student_id
 * @property int $school_id
 * @property string $type
 * @property int $quantity
 * @property string|null $reference_number
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read InventoryItem $item
 * @property-read \Modules\School\Models\Student\Student|null $student
 */
class InventoryTransaction extends Model
{
    protected $table = 'sch_log_inventory_transactions';

    protected $fillable = [
        'item_id',
        'student_id',
        'school_id',
        'type',
        'quantity',
        'reference_number',
        'notes',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<InventoryItem, $this>
     */
    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\School\Models\Student\Student, $this>
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\Modules\School\Models\Student\Student::class, 'student_id');
    }
}
