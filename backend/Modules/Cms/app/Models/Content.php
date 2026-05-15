<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Models\AnalyticsVisit;
use Modules\Cms\Models\Tag;
use Modules\System\Models\User;
use Modules\System\Traits\CoreLogsActivity;

class Content extends Model
{
    protected $table = 'cms_contents';

    use HasFactory, CoreLogsActivity, SoftDeletes, ScopedByWorkspace;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'intro', 'body', 'featured_image',
        'status', 'type', 'author_id', 'workspace_id', 'category_id',
        'published_at', 'meta', 'meta_title', 'meta_description',
        'meta_keywords', 'og_image'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
