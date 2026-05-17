<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Modules\System\Models\User;


/**
 * @property string $id
 * @property string $session_id
 * @property string|null $user_id
 * @property string $event_type
 * @property string $event_name
 * @property string|null $event_category
 * @property array<string, mixed>|null $event_data
 * @property string|null $url
 * @property string|null $content_id
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\System\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Model|null $content
 * @method \Illuminate\Database\Eloquent\Relations\BelongsTo<\Illuminate\Database\Eloquent\Model, $this> content()
 */
class AnalyticsEvent extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'srv_analytics_events';


    /** @use HasFactory<\Modules\System\Database\Factories\AnalyticsEventFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Analytics\Database\Factories\AnalyticsEventFactory
    {
        return \Modules\Analytics\Database\Factories\AnalyticsEventFactory::new();
    }


    protected $fillable = [
        'workspace_id',
        'session_id',
        'user_id',
        'event_type',
        'event_name',
        'event_category',
        'event_data',
        'url',
        'content_id',
        'ip_address',
        'occurred_at',
    ];

    protected $casts = [
        'event_data' => 'array',
        'occurred_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    /**
     * @param  array<string, mixed>  $data
     * @param  int|string|null  $contentId
     */
    public static function track(string $eventType, string $eventName, array $data = [], $contentId = null): self
    {
        $ip = \Modules\System\Helpers\IpHelper::getClientIp(request());

        return self::create([
            'session_id' => session()->getId(),
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'event_name' => $eventName,
            'event_category' => $data['category'] ?? null,
            'event_data' => $data,
            'url' => request()->fullUrl(),
            'content_id' => $contentId,
            'ip_address' => $ip,
            'occurred_at' => now(),
        ]);
    }
}
