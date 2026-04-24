<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Helpers\IpHelper;

/**
 * @property int $id
 * @property string $session_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $device_type
 * @property string|null $browser
 * @property string|null $os
 * @property string|null $country
 * @property string|null $city
 * @property int $page_views
 * @property int $duration
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Core\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\AnalyticsVisit> $visits
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\AnalyticsEvent> $events
 */
class AnalyticsSession extends Model
{
    /** @use HasFactory<\Modules\Core\Database\Factories\AnalyticsSessionFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\AnalyticsSessionFactory
    {
        return \Modules\Core\Database\Factories\AnalyticsSessionFactory::new();
    }

    protected $table = 'analytics_sessions';

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'page_views',
        'duration',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'page_views' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class);
    }

    /**
     * @return HasMany<\Modules\Core\Models\AnalyticsVisit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\AnalyticsVisit::class, 'session_id', 'session_id');
    }

    /**
     * @return HasMany<\Modules\Core\Models\AnalyticsEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\AnalyticsEvent::class, 'session_id', 'session_id');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $sessionId
     */
    public static function start(\Illuminate\Http\Request $request, ?string $sessionId = null): self
    {
        $sessionId = $sessionId ?? session()->getId();

        $userAgent = $request->userAgent();
        $deviceInfo = self::parseUserAgent($userAgent);
        $clientIp = IpHelper::getClientIp($request);
        $location = self::getLocation($clientIp);

        return self::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => Auth::id(),
                'ip_address' => $clientIp,
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'country' => $location['country'],
                'city' => $location['city'],
                'started_at' => now(),
            ]
        );
    }

    public function end(): void
    {
        $this->update([
            'ended_at' => now(),
            'duration' => $this->started_at?->diffInSeconds(now()) ?? 0,
            'page_views' => $this->visits()->count(),
        ]);
    }

    public function incrementPageViews(): void
    {
        $this->increment('page_views');
    }

    /**
     * @return array{device_type: string, browser: string, os: string}
     */
    protected static function parseUserAgent(?string $userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'unknown';
        $os = 'unknown';

        if (! $userAgent) {
            return [
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
            ];
        }

        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        if (preg_match('/chrome/i', $userAgent)) {
            $browser = 'chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            $browser = 'edge';
        }

        if (preg_match('/windows/i', $userAgent)) {
            $os = 'windows';
        } elseif (preg_match('/mac|os x/i', $userAgent)) {
            $os = 'macos';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'android';
        } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            $os = 'ios';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * @return array{country: string|null, city: string|null}
     */
    public static function getLocation(?string $ipAddress): array
    {
        if (! $ipAddress) {
            return ['country' => null, 'city' => null];
        }

        $geoService = app(\Modules\Core\Services\GeoIpService::class);

        return $geoService->getLocation($ipAddress);
    }
}
