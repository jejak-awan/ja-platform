<?php

declare(strict_types=1);

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DeletedFile extends Model
{
    use HasUuids;

    protected $table = 'srv_media_deleted_files';

    protected $fillable = [
        'original_path',
        'trash_path',
        'disk',
        'name',
        'type',
        'size',
        'extension',
        'mime_type',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'size' => 'integer',
    ];
}
