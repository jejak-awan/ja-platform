<?php

declare(strict_types=1);

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * Modules\System\Models\DynamicRecord
 *
 * @property string $id
 * @property string|null $workspace_id
 * @property string $content_type_id
 * @property array<string, mixed>|null $data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DynamicRecord extends Model
{
    use HasUuids, ScopedByWorkspace;

    protected $table = 'sys_dynamic_records';

    protected $fillable = [
        'content_type_id',
        'data',
        'workspace_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the associated content type.
     *
     * @return BelongsTo<ContentType, $this>
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }
}
