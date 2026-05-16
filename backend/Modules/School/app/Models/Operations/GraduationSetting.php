<?php

declare(strict_types=1);

namespace Modules\School\Models\Operations;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property int $id
 * @property int $graduation_year
 * @property bool $is_open
 * @property \Illuminate\Support\Carbon|null $announcement_date
 * @property array<string, mixed>|null $subjects
 * @property array<string, mixed>|null $config
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GraduationSetting extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use ScopedByWorkspace;
    protected $table = 'sch_grad_settings';

    protected $fillable = [
        'workspace_id',
        'graduation_year',
        'is_open',
        'announcement_date',
        'subjects',
        'config',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'is_open' => 'boolean',
        'announcement_date' => 'datetime',
        'subjects' => 'json',
        'config' => 'json',
    ];
}
