<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    protected $table = 'sys_translations';


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
