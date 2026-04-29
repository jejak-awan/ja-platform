<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Model;
use Modules\School\Models\Institution\School;

/**
 * @property int $id
 * @property int $school_id
 * @property string $name
 * @property string $type
 * @property string $content
 * @property string|null $styles
 * @property array<string, string>|null $placeholders
 * @property bool $is_active
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class DocumentTemplate extends Model
{
    protected $table = 'sch_doc_templates';

    protected $fillable = [
        'school_id',
        'name',
        'type',
        'content',
        'styles',
        'placeholders',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<School, $this>
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
