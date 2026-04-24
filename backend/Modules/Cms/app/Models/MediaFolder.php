<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\User;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int|null $parent_id
 * @property int $sort_order
 * @property int|null $author_id
 * @property bool $is_shared
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User|null $author
 * @property-read MediaFolder|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MediaFolder> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Media> $media
 * @property-read string $full_path
 */
class MediaFolder extends Model
{
    /** @use HasFactory<\Modules\Cms\Database\Factories\MediaFolderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\MediaFolderFactory
    {
        return \Modules\Cms\Database\Factories\MediaFolderFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'sort_order',
        'author_id',
        'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<MediaFolder, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'parent_id');
    }

    /**
     * @return HasMany<MediaFolder, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MediaFolder::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_id');
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
}
