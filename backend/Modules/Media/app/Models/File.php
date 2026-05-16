<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\System\Traits\ScopedByWorkspace;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property int|null $workspace_id
 * @property string $module
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property string $path
 * @property string|null $thumbnail_path
 * @property int $size
 * @property string|null $alt
 * @property string|null $description
 * @property string|null $caption
 * @property string|null $folder_id
 * @property int|null $author_id
 * @property bool $is_shared
 */
class File extends Model
{
    use HasFactory, SoftDeletes, ScopedByWorkspace, HasUuids;

    protected static function newFactory(): \Modules\Media\Database\Factories\FileFactory
    {
        return \Modules\Media\Database\Factories\FileFactory::new();
    }

    protected $table = 'srv_media_files';

    public bool $isSharedScoped = true;

    protected $fillable = [
        'workspace_id',
        'module',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'thumbnail_path',
        'size',
        'alt',
        'description',
        'caption',
        'folder_id',
        'author_id',
        'is_shared',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_shared' => 'boolean',
    ];

    protected $appends = ['url', 'thumbnail_url'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Modules\Media\Models\Folder, $this>
     */
    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Media\Models\Usage, $this>
     */
    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Usage::class, 'file_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Modules\Library\Models\Tag, $this>
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(\Modules\Library\Models\Tag::class, 'taggable', 'lib_taggables');
    }

    /**
     * @return array<int, string>
     */
    public function getTagNamesAttribute(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        // For public disk, use relative path to avoid localhost URL issues
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($this->path, '/');
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->path || ! str_starts_with((string) $this->mime_type, 'image/')) {
            return null;
        }

        // Check if thumbnail exists
        $fileName = pathinfo($this->path, PATHINFO_FILENAME);
        $extension = pathinfo($this->path, PATHINFO_EXTENSION);

        // For SVG files, thumbnail is saved as PNG
        $isSvg = $this->mime_type === 'image/svg+xml' || str_ends_with($this->path, '.svg');
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;

        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            if ($this->disk === 'public') {
                return '/storage/'.ltrim($thumbnailPath, '/');
            }
            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        return $this->url;
    }
}
