<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use HasFactory, SoftDeletes;

    protected $table = 'lay_menu_items';

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'target_id',
        'target_type',
        'icon',
        'css_class',
        'sort_order',
        'open_in_new_tab',
        'metadata',
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Morph to the target model (Content, Category, etc.)
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }
}
