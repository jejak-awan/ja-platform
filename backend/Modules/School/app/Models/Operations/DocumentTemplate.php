<?php

namespace Modules\School\Models\Operations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\School\Models\Institution\School;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $school_id
 * @property string $name
 * @property string $type
 * @property string $content
 * @property string|null $styles
 * @property array<string, string>|null $placeholders
 * @property bool $is_active
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DocumentTemplate extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    use ScopedByWorkspace;

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
     * @return BelongsTo<School, $this>
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
