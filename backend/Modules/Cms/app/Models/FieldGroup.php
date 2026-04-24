<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $applies_to
 * @property array<string, mixed>|null $conditions
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomField> $fields
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomField> $activeFields
 */
class FieldGroup extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'applies_to',
        'conditions',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<CustomField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CustomField, $this>
     */
    public function activeFields(): HasMany
    {
        return $this->hasMany(CustomField::class)->where('is_active', true)->orderBy('sort_order');
    }
}
