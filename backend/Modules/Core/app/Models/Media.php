<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\ScopedByWorkspace;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Helpers\CdnHelper;
use Modules\Core\Models\User;

/**
 * @property int $id
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
 * @property int|null $folder_id
 * @property int|null $author_id
 * @property bool $is_shared
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Core\Models\User $author
 * @property-read MediaFolder|null $folder
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, MediaUsage> $usages
 * @property-read array<int, string> $tag_names
 * @property-read string|null $url
 * @property-read string|null $thumbnail_url
 * @property-read int $usage_count
 */
class Media extends Model
{
    protected $table = 'core_media';


    /** @use HasFactory<\Modules\Core\Database\Factories\MediaFactory> */
    use HasFactory, SoftDeletes, ScopedByWorkspace;


    
    /**
     * Enable shared record visibility in unit scoping.
     */
    public bool $isSharedScoped = true;


    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\MediaFactory
    {
        return \Modules\Core\Database\Factories\MediaFactory::new();
    }

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected $casts = [
        'size' => 'integer',
        'is_shared' => 'boolean',
    ];

    protected $appends = ['url', 'thumbnail_url', 'tag_names'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<MediaFolder, $this>
     */
    public function folder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<MediaUsage, $this>
     */
    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Tag, $this>
     */
    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'core_media_tag');
    }

    /**
     * @return array<int, string>
     */
    public function getTagNamesAttribute(): array
    {
        /** @var array<int, string> $names */
        $names = $this->tags->pluck('name')->toArray();

        return $names;
    }

    public function getUsageCountAttribute(): int
    {
        return $this->usages()->count();
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        // Use CDN if enabled
        if (CdnHelper::isEnabled()) {
            return CdnHelper::mediaUrl($this->path);
        }

        // For public disk, use relative path to avoid localhost URL issues
        // This ensures URLs work regardless of APP_URL configuration
        if ($this->disk === 'public') {
            return '/storage/'.ltrim($this->path, '/');
        }

        // For other disks, use Storage URL
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

        // Check if thumbnail file actually exists
        if (Storage::disk($this->disk)->exists($thumbnailPath)) {
            // Use CDN if enabled
            if (CdnHelper::isEnabled()) {
                return CdnHelper::thumbnailUrl($thumbnailPath);
            }

            // For public disk, use relative path
            if ($this->disk === 'public') {
                return '/storage/'.ltrim($thumbnailPath, '/');
            }

            return Storage::disk($this->disk)->url($thumbnailPath);
        }

        // If no thumbnail exists, return original URL (with CDN if enabled)
        return $this->url;
    }
}
