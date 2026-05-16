<?php

namespace Modules\Layout\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\System\Traits\ScopedByWorkspace;

class Widget extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use HasFactory, SoftDeletes, ScopedByWorkspace;

    protected $table = 'lay_widgets';

    protected $fillable = [
        'name',
        'type',
        'location',
        'settings',
        'module_scope',
        'workspace_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
