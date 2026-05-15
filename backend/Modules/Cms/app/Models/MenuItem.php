<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $url
 * @property string $type
 * @property int|null $target_id
 * @property string|null $target_type
 * @property string|null $icon
 * @property string|null $css_class
 * @property string|null $description
 * @property string|null $badge
 * @property string|null $badge_color
 * @property string|null $image
 * @property string|null $image_size
 * @property string|null $mega_menu_layout
 * @property int|null $mega_menu_column
 * @property bool $mega_menu_show_dividers
 * @property int $sort_order
 * @property bool $open_in_new_tab
 * @property bool $is_active
 * @property bool $hide_label
 * @property string|null $heading
 * @property bool $show_heading_line
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Cms\Models\Menu $menu
 * @property-read \Modules\Cms\Models\MenuItem|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Cms\Models\MenuItem[] $children
 * @property-read \Modules\Cms\Models\Content|\Modules\Cms\Models\Category|null $target
 */
class MenuItem extends Model
{
    use ScopedByWorkspace;
    use SoftDeletes;

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
        'description',
        'badge',
        'badge_color',
        'image',
        'image_size',
        'mega_menu_layout',
        'mega_menu_column',
        'mega_menu_show_dividers',
        'sort_order',
        'open_in_new_tab',
        'is_active',
        'hide_label',
        'heading',
        'show_heading_line',
    ];

    /**
     * Allowed models for polymorphic relationship
     */
    const ALLOWED_TARGET_TYPES = [
        'Modules\Cms\Models\Content',
        'Modules\Cms\Models\Category',
        // Add other allowed models here
    ];

    protected $casts = [
        'open_in_new_tab' => 'boolean',
        'is_active' => 'boolean',
        'hide_label' => 'boolean',
    ];

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    public function getUrlAttribute(?string $value): ?string
    {
        // If type is page, generate URL from target (frontend uses /:slug)
        if ($this->type === 'page' && $this->target) {
            /** @var Category|Content $target */
            $target = $this->target;

            return '/'.$target->slug;
        }

        // If type is post, generate URL from target (frontend uses /blog/:slug)
        if ($this->type === 'post' && $this->target) {
            /** @var Category|Content $target */
            $target = $this->target;

            return '/blog/'.$target->slug;
        }

        // If type is category, generate URL from target
        if ($this->type === 'category' && $this->target) {
            /** @var Category|Content $target */
            $target = $this->target;

            return '/category/'.$target->slug;
        }

        return $value;
    }
}
