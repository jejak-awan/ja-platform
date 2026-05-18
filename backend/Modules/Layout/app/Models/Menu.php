<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Traits\ScopedByWorkspace;

class Menu extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use HasFactory, ScopedByWorkspace, SoftDeletes;

    protected $table = 'lay_menus';

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'module_scope',
        'workspace_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function parentItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public static function getByLocation(string $location): ?self
    {
        return self::where('location', $location)
            ->where('is_active', true)
            ->first();
    }
}
