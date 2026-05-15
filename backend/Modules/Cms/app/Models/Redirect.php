<?php

namespace Modules\Cms\Models;

use Modules\System\Traits\ScopedByWorkspace;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $from_url
 * @property string $to_url
 * @property string $type
 * @property bool $is_active
 * @property int $hits
 * @property \Illuminate\Support\Carbon|null $last_hit_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Redirect extends Model
{
    protected $table = 'redirects';

    use ScopedByWorkspace;
    protected $fillable = [
        'from_url',
        'to_url',
        'type',
        'is_active',
        'hits',
        'last_hit_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }
}
