<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SlowQuery extends Model
{
    protected $fillable = [
        'query',
        'bindings',
        'duration',
        'route',
        'user_id',

    ];

    protected $casts = [
        'bindings' => 'array',
        'duration' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @param  int|float|null  $threshold
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeSlow($query, $threshold = 1000)
    {
        return $query->where('duration', '>=', $threshold ?? 1000);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @param  string  $route
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeByRoute($query, $route)
    {
        return $query->where('route', $route);
    }
}
