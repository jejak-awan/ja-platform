<?php

declare(strict_types=1);

namespace Modules\System\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * Modules\System\Models\ContentType
 *
 * @property string $id
 * @property string|null $workspace_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<mixed>|null $fields
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentType extends Model
{
    use HasUuids, ScopedByWorkspace;

    protected $table = 'sys_content_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'fields',
        'is_active',
        'workspace_id',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get records created for this content type.
     *
     * @return HasMany<DynamicRecord, $this>
     */
    public function records(): HasMany
    {
        return $this->hasMany(DynamicRecord::class, 'content_type_id');
    }
}
