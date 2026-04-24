<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'language_code',
        'field',
        'value',
    ];

    /**
     * Get the parent translatable model.
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
