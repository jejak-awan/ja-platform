<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $field_group_id
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $label
 * @property string|null $description
 * @property string|null $default_value
 * @property array<string, mixed>|null $options
 * @property array<string, mixed>|null $validation_rules
 * @property bool $is_required
 * @property bool $is_searchable
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read FieldGroup|null $fieldGroup
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ContentCustomField> $contentValues
 */
class CustomField extends Model
{
    protected $fillable = [
        'field_group_id',
        'name',
        'slug',
        'type',
        'label',
        'description',
        'default_value',
        'options',
        'validation_rules',
        'is_required',
        'is_searchable',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
    ];

    /**
     * @return BelongsTo<FieldGroup, $this>
     */
    public function fieldGroup(): BelongsTo
    {
        return $this->belongsTo(FieldGroup::class);
    }

    /**
     * @return HasMany<ContentCustomField, $this>
     */
    public function contentValues(): HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    /**
     * @param  int|string  $contentId
     * @return mixed
     */
    public function getValueForContent($contentId)
    {
        /** @var \Modules\Cms\Models\ContentCustomField|null $value */
        $value = $this->contentValues()->where('content_id', $contentId)->first();

        return $value ? $value->value : $this->default_value;
    }
}
