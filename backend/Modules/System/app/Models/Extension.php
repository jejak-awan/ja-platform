<?php

declare(strict_types=1);

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $slug
 * @property string $type
 * @property string $name
 * @property string $version
 * @property string $database_version
 * @property string $status
 * @property bool $is_core
 * @property string|null $author
 * @property string $license
 * @property array|null $requirements
 * @property array|null $settings
 */
class Extension extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'sys_extensions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'slug',
        'type',
        'name',
        'version',
        'database_version',
        'status',
        'is_core',
        'author',
        'license',
        'requirements',
        'settings',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'requirements' => 'array',
        'settings' => 'array',
    ];
}
