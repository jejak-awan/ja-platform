<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Modules\Cms\Models\Content;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string $event_type
 * @property string $event_name
 * @property string|null $event_category
 * @property array<string, mixed>|null $event_data
 * @property string|null $url
 * @property int|null $content_id
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $occurred_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Core\Models\User|null $user
 * @property-read \Modules\Cms\Models\Content|null $content
 */
class AnalyticsEvent extends Model
{
    /** @use HasFactory<\Modules\Core\Database\Factories\AnalyticsEventFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\AnalyticsEventFactory
    {
        return \Modules\Core\Database\Factories\AnalyticsEventFactory::new();
    }

    protected $table = 'analytics_events';

    protected $fillable = [
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
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  int|string|null  $contentId
     */
    public static function track(string $eventType, string $eventName, array $data = [], $contentId = null): self
    {
        /** @var string $ip */
        $ip = \Modules\Core\Helpers\IpHelper::getClientIp(request());

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
