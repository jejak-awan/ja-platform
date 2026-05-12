<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\User;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int|null $author_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Core\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model> $contents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Media> $media
 * @property int $contents_count
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Illuminate\Database\Eloquent\Model, $this> contents()
 */
class Tag extends Model
{
    /** @use HasFactory<\Modules\Core\Database\Factories\TagFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\TagFactory
    {
        return \Modules\Core\Database\Factories\TagFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'author_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }



    /**
     * @return BelongsToMany<Media, $this>
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_tag');
    }
}
