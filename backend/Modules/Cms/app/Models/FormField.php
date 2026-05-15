<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $form_id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property string|null $placeholder
 * @property string|null $help_text
 * @property array<string, mixed>|null $options
 * @property array<string, mixed>|null $validation_rules
 * @property bool $is_required
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Form $form
 */
class FormField extends Model
{
    use ScopedByWorkspace;
    protected $fillable = [
        'form_id',
        'name',
        'label',
        'type',
        'placeholder',
        'help_text',
        'options',
        'validation_rules',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return array<int, string>
     */
    public function getValidationRules(): array
    {
        $raw = is_array($this->validation_rules) ? $this->validation_rules : [];
        /** @var array<int, string> $rules */
        $rules = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $rules[] = $item;
            }
        }

        if ($this->is_required) {
            $rules[] = 'required';
        }

        // Add type-specific rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'url':
                $rules[] = \Modules\System\Rules\SafeUrl::class;
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'file':
                $rules[] = 'file';
                $rules[] = 'max:10240';
                break;
            case 'image':
                $rules[] = 'image';
                $rules[] = 'max:5120';
                break;
        }

        return $rules;
    }
}
