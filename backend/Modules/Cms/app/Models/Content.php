<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Cms\Database\Factories\ContentFactory;
use Modules\Layout\Models\MenuItem;
use Modules\Library\Models\Category;
use Modules\Library\Models\Tag;
use Modules\System\Models\User;
use Modules\System\Traits\CoreLogsActivity;
use Modules\System\Traits\ScopedByWorkspace;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $intro
 * @property string|null $body
 * @property string|null $featured_image
 * @property string $status
 * @property string $type
 * @property string $author_id
 * @property string $workspace_id
 * @property string|null $category_id
 * @property Carbon|null $published_at
 * @property array|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property bool $is_featured
 * @property int $views
 * @property string $comment_status
 * @property string|null $locked_by
 * @property Carbon|null $locked_at
 */
class Content extends Model
{
    protected $table = 'cms_contents';

    use CoreLogsActivity, HasFactory, HasUuids, ScopedByWorkspace, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ContentFactory
    {
        return ContentFactory::new();
    }

    protected $fillable = [
        'title', 'slug', 'excerpt', 'intro', 'body', 'featured_image',
        'status', 'type', 'author_id', 'workspace_id', 'category_id',
        'published_at', 'meta', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image', 'is_featured', 'views', 'comment_status', 'locked_by', 'locked_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
        'is_featured' => 'boolean',
        'views' => 'integer',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'lib_taggables');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'target');
    }

    /**
     * Get custom field value by slug
     */
    public function getCustomFieldValue(string $slug): ?string
    {
        $field = $this->customFields()
            ->whereHas('customField', function ($q) use ($slug): void {
                $q->where('key', $slug);
            })
            ->first();

        return $field?->value;
    }
}
