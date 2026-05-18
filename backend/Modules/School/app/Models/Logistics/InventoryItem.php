<?php

namespace Modules\School\Models\Logistics;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property int $category_id
 * @property string $school_id
 * @property string $sku
 * @property string $name
 * @property string|null $description
 * @property string $unit
 * @property int $quantity_on_hand
 * @property int $minimum_stock
 * @property string|null $cost_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read InventoryCategory $category
 * @property-read Collection<int, InventoryTransaction> $transactions
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
     * @return BelongsTo<InventoryCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    /**
     * @return HasMany<InventoryTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }
}
