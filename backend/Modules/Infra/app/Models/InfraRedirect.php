<?php
namespace Modules\Infra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\System\Traits\ScopedByWorkspace;

class InfraRedirect extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;

    protected $table = 'infra_redirects';

    protected $fillable = [
        'workspace_id',
        'from_domain',
        'to_domain',
        'target_path',
        'status_code',
        'keep_path',
        'is_active',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'keep_path' => 'boolean',
        'is_active' => 'boolean',
    ];
}
