<?php

namespace Modules\System\Models;
 
 use Modules\Cms\Models\Content;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $content_id
 * @property int $custom_field_id
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Cms\Models\Content $content
 * @property-read \Modules\Cms\Models\CustomField $customField
 */
class ContentCustomField extends Model
{
    protected $table = 'sys_content_custom_fields';

    use ScopedByWorkspace;
    protected $fillable = [
        'content_id',
        'custom_field_id',
        'value',
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<CustomField, $this>
     */
    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
