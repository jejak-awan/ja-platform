<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $original_path
 * @property string $trash_path
 * @property string $disk
 * @property string $name
 * @property string $type
 * @property int $size
 * @property string $extension
 * @property string $mime_type
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Core\Models\User|null $deletedByUser
 * @property-read string $formatted_size
 */
class DeletedFile extends Model
{
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

    /**
     * Get the user who deleted this file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function deletedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope for files only
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeFiles($query)
    {
        return $query->where('type', 'file');
    }

    /**
     * Scope for folders only
     *
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeFolders($query)
    {
        return $query->where('type', 'folder');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (! $this->size || $this->size <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($this->size, 1024));

        // Safety check for units index
        if (! isset($units[$i])) {
            $i = count($units) - 1;
        }

        $val = round($this->size / pow(1024, $i), 2);

        return $val.' '.$units[$i];
    }
}
