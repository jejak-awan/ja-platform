<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $subscribed_at
 * @property \Illuminate\Support\Carbon|null $unsubscribed_at
 * @property string|null $source
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class NewsletterSubscriber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'name',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'source',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeSubscribed($query)
    {
        return $query->where('status', 'subscribed');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
