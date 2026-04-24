<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\User;

/**
 * @property int $id
 * @property int $content_id
 * @property int|null $author_id
 * @property string $title
 * @property string $body
 * @property array<string, mixed>|null $meta
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Content $content
 * @property-read User|null $author
 * @property-read User|null $user
 */
class ContentRevision extends Model
{
    /** @use HasFactory<\Modules\Cms\Database\Factories\ContentRevisionFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Cms\Database\Factories\ContentRevisionFactory
    {
        return \Modules\Cms\Database\Factories\ContentRevisionFactory::new();
    }

    protected $fillable = [
        'content_id',
        'author_id',
        'title',
        'body',
        'meta',
        'reason',
    ];

    protected $casts = [
        'meta' => 'array',
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Alias for backward compatibility if needed, or just remove
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
