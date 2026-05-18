<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Library\Database\Factories\TagFactory;
use Modules\System\Models\User;
use Modules\System\Traits\ScopedByWorkspace;

class Tag extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TagFactory
    {
        return TagFactory::new();
    }

    use HasFactory, ScopedByWorkspace, SoftDeletes;

    protected $table = 'lib_tags';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'workspace_id',
        'author_id',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'usage_count' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function taggables(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'taggable', 'lib_taggables');
    }
}
