<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\AnalyticsVisit;
use Modules\Core\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property string|null $intro
 * @property string|null $body
 * @property string|null $featured_image
 * @property string|null $featured_image_title
 * @property string|null $featured_image_caption
 * @property string|null $featured_image_position
 * @property bool $is_featured
 * @property string $status
 * @property string $type
 * @property int $author_id
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $views
 * @property array<string, mixed>|null $meta
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $og_image
 * @property int|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property bool $comment_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $visits_count
 * @property array<string, mixed>|null $lock_status
 * @property-read \Modules\Core\Models\User $author
 * @property-read \Modules\Cms\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Tag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Comment> $comments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\ContentRevision> $revisions
 * @property-read \Modules\Core\Models\User|null $lockedBy
 */
class Content extends Model
{
    /** @use HasFactory<\Modules\Cms\Database\Factories\ContentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'type', 'category_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\ContentFactory
    {
        return \Modules\Cms\Database\Factories\ContentFactory::new();
    }

    protected static function booted()
    {
        static::deleting(function ($content) {
            // When soft deleting, rename the slug to free it up for reuse
            if ($content->isForceDeleting()) {
                return;
            }

            $timestamp = now()->timestamp;
            $newSlug = $content->slug.'__trashed__'.$timestamp;

            // Ensure even the trashed slug is unique (rare but possible collision)
            while (\Modules\Cms\Models\Content::withTrashed()->where('slug', $newSlug)->exists()) {
                $newSlug = $content->slug.'__trashed__'.$timestamp.'_'.rand(100, 999);
            }

            $content->slug = $newSlug;
            $content->save();
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'intro',
        'body',
        'featured_image',
        'featured_image_title',
        'featured_image_caption',
        'featured_image_position',
        'is_featured',
        'status',
        'type',
        'author_id',
        'category_id',
        'published_at',
        'views',
        'meta',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'locked_by',
        'locked_at',
        'comment_status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'content_tag');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Cms\Models\Comment, $this>
     */
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Cms\Models\Comment, $this>
     */
    public function allComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Cms\Models\ContentRevision, $this>
     */
    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContentRevision::class)->orderBy('created_at', 'desc');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\Modules\Cms\Models\ContentRevision, $this>
     */
    public function latestRevision(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentRevision::class)->latestOfMany();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Cms\Models\ContentCustomField, $this>
     */
    public function customFields(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ContentCustomField::class);
    }

    /**
     * Get a custom field value.
     */
    public function getCustomFieldValue(string $fieldSlug): mixed
    {
        $field = \Modules\Cms\Models\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return null;
        }

        $value = $this->customFields()->where('custom_field_id', $field->id)->first();

        return $value ? $value->value : $field->getAttribute('default_value');
    }

    /**
     * Set a custom field value.
     */
    public function setCustomFieldValue(string $fieldSlug, mixed $value): bool
    {
        $field = \Modules\Cms\Models\CustomField::where('slug', $fieldSlug)->first();
        if (! $field) {
            return false;
        }

        $this->customFields()->updateOrCreate(
            ['custom_field_id' => $field->id],
            ['value' => $value]
        );

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<\Modules\Core\Models\AnalyticsVisit>
     */
    public function analyticsVisits(): \Illuminate\Database\Eloquent\Builder
    {
        // Match visits by URL slug - using where clause
        return AnalyticsVisit::where('url', 'like', '%'.$this->slug.'%');
    }

    /**
     * @return MorphMany<\Modules\Cms\Models\MenuItem, $this>
     */
    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'target');
    }
}
