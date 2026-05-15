<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Usage extends Model
{
    use HasUuids;

    protected $table = 'srv_media_usages';

    protected $fillable = [
        'file_id',
        'model_type',
        'model_id',
        'field_name',
    ];

    /**
     * @return BelongsTo<File, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
