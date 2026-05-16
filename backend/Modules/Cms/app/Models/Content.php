<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Library\Models\Tag;
use Modules\Layout\Models\MenuItem;
use Modules\System\Models\User;
use Modules\Library\Models\Category;
use Modules\System\Traits\CoreLogsActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property array|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property bool $is_featured
 * @property int $views
 * @property string $comment_status
 * @property string|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 */
class Content extends Model
{
    protected $table = 'cms_contents';

    use HasFactory, CoreLogsActivity, SoftDeletes, ScopedByWorkspace, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\ContentFactory
    {
        return \Modules\Cms\Database\Factories\ContentFactory::new();
    }

    protected $fillable = [
        'title', 'slug', 'excerpt', 'intro', 'body', 'featured_image',
        'status', 'type', 'author_id', 'workspace_id', 'category_id',
        'published_at', 'meta', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image', 'is_featured', 'views', 'comment_status', 'locked_by', 'locked_at'
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

    public function tags(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'lib_taggables');
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function allComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    public function customFields(): \Illuminate\Database\Eloquent\Relations\HasMany
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
