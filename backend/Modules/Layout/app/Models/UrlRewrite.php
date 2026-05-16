<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\System\Traits\ScopedByWorkspace;

class UrlRewrite extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'lay_url_rewrites';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'module_scope',
        'workspace_id',
        'hits',
        'last_hit_at',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
