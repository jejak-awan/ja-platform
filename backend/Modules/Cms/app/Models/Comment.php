<?php

namespace Modules\Cms\Models;

use Modules\Core\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int $content_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string $body
 * @property string $status
 * @property int|null $locked_by
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Cms\Models\Content $content
 * @property-read \Modules\Core\Models\User|null $user
 * @property-read Comment|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $replies
 * @property-read \Modules\Core\Models\User|null $lockedBy
 */
class Comment extends Model
{
    /** @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Modules\Cms\Database\Factories\CommentFactory> */
    use \Illuminate\Database\Eloquent\Factories\HasFactory, LogsActivity, SoftDeletes, ScopedByWorkspace;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\CommentFactory
    {
        return \Modules\Cms\Database\Factories\CommentFactory::new();
    }

    protected $fillable = [
        'content_id',
        'user_id',
        'parent_id',
        'body',
        'status',
        'name',
        'email',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'body'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}
