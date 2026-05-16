<?php

namespace Modules\School\Models\Logistics;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $category_id
 * @property int $school_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property string $unit
 * @property int $quantity_on_hand
 * @property int $minimum_stock
 * @property string|null $cost_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read InventoryCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InventoryTransaction> $transactions
 */
class InventoryItem extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_log_inventory_items';

    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'school_id',
        'sku',
        'name',
        'description',
        'unit',
        'quantity_on_hand',
        'minimum_stock',
        'cost_price',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<InventoryCategory, $this>
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<InventoryTransaction, $this>
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }
}
