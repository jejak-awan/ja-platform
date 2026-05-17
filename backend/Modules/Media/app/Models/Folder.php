<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Traits\ScopedByWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property int|null $workspace_id
 * @property string $name
 * @property string $slug
 * @property string|null $parent_id
 * @property int $sort_order
 * @property int|null $author_id
 * @property bool $is_shared
 * @property string $module
 */
class Folder extends Model
{
    use HasFactory, SoftDeletes, ScopedByWorkspace, HasUuids;

    protected static function newFactory(): \Modules\Media\Database\Factories\FolderFactory
    {
        return \Modules\Media\Database\Factories\FolderFactory::new();
    }

    protected $table = 'srv_media_folders';

    public bool $isSharedScoped = true;

    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'parent_id',
        'sort_order',
        'author_id',
        'is_shared',
        'module',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    protected $appends = ['is_trashed'];

    /**
     * @return BelongsTo<Folder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * @return HasMany<Folder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<File, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class, 'folder_id');
    }

    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' / ', $path);
    }

    public function getIsTrashedAttribute(): bool
    {
        return $this->trashed();
    }
}
