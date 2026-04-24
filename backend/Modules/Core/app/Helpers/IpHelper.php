<?php

namespace Modules\Core\Helpers;

use Illuminate\Http\Request;

class IpHelper
{
    /**
     * Get the real client IP address from the request.
     * This handles various proxy/CDN scenarios.
     */
    public static function getClientIp(?Request $request = null): string
    {
        $request = $request ?? request();

        // Check if TrustProxies middleware already set the real IP
        if ($request->attributes->has('real_client_ip')) {
            /** @var string $realIp */
            $realIp = $request->attributes->get('real_client_ip');

            return $realIp;
        }

        // Try to get from various headers (fallback if middleware not loaded)
        $headers = [
            'CF-Connecting-IP',      // Cloudflare
            'X-Real-IP',             // Nginx
            'True-Client-IP',        // Akamai / Cloudflare Enterprise
            'X-Forwarded-For',       // Standard proxy header
        ];

        foreach ($headers as $header) {
            if ($request->hasHeader($header)) {
                $value = $request->header($header);

                // X-Forwarded-For can contain multiple IPs
                if ($header === 'X-Forwarded-For') {
                    $ips = array_map('trim', explode(',', (string) $value));
                    $ip = $ips[0];
                } else {
                    $ip = (string) $value;
                }

                if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Fallback to Laravel's default
        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Check if an IP is a private/internal IP.
     * These should generally not be blocked as they might be server IPs.
     */
    public static function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false && filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Check if an IP is localhost.
     */
    public static function isLocalhost(string $ip): bool
    {
        $localhostIps = ['127.0.0.1', '::1', 'localhost'];

        return in_array($ip, $localhostIps);
    }

    /**
     * Get all proxy-related headers for debugging.
     *
     * @return array<string, mixed>
     */
    public static function getProxyHeaders(?Request $request = null): array
    {
        $request = $request ?? request();

        return [
            'REMOTE_ADDR' => $request->server->get('REMOTE_ADDR'),
            'X-Forwarded-For' => $request->header('X-Forwarded-For'),
            'X-Real-IP' => $request->header('X-Real-IP'),
            'CF-Connecting-IP' => $request->header('CF-Connecting-IP'),
            'True-Client-IP' => $request->header('True-Client-IP'),
            'X-Forwarded-Proto' => $request->header('X-Forwarded-Proto'),
            'X-Forwarded-Host' => $request->header('X-Forwarded-Host'),
        ];
    }
}
