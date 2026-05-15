<?php

declare(strict_types=1);

namespace Modules\Security\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * File Integrity Baseline model.
 *
 * @property int $id
 * @property string $file_path
 * @property string $hash
 * @property int $file_size
 * @property string $status
 * @property \Illuminate\Support\Carbon $checked_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class FileIntegrityBaseline extends Model
{
    protected $table = 'sec_file_integrity_baselines';



    protected $fillable = [
        'file_path',
        'hash',
        'file_size',
        'status',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'file_size' => 'integer',
    ];
}
