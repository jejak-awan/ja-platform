<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class CheckLicenseGate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip health checks, install screen, and custom billing alert page to avoid redirect loops
        if ($request->is('up') || $request->is('install*') || $request->is('billing-alert')) {
            return $next($request);
        }

        $licenseKey = config('app.license_key') ?? env('LICENSE_KEY');
        $serverUrl = env('LICENSING_SERVER_URL', 'http://127.0.0.1:8081');

        if (!$licenseKey) {
            return $this->lockScreen('License key is missing. Please configure LICENSE_KEY in your system environment.');
        }

        $cacheKey = 'license_active_status_' . md5($licenseKey);
        $status = Cache::get($cacheKey);

        if ($status === 'active') {
            return $next($request);
        }

        if ($status === 'suspended') {
            return $this->lockScreen('Your system subscription is currently suspended or inactive. Please contact administrator.');
        }

        // Cache missing or expired. Let's make an outbound handshake call to the central billing plane
        try {
            // In production we would map to request()->getHost(), but for our local demo we fallback to seeded domain
            $domain = $request->getHost();
            if ($domain === 'localhost' || $domain === '127.0.0.1' || str_starts_with($domain, '192.168.')) {
                $domain = 'academy.jejakawan.com';
            }

            $response = Http::timeout(5)->post("{$serverUrl}/api/v1/license/verify", [
                'domain' => $domain,
                'license_key' => $licenseKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Handshake passed, cache the verified status for 24 hours
                Cache::put($cacheKey, 'active', 86400);
                return $next($request);
            }

            // Licensing server explicitly returned a failed response (suspended/expired/invalid key)
            $message = $response->json('message') ?? 'Subscription validation failed.';
            Cache::put($cacheKey, 'suspended', 3600); // Cache suspend for 1 hour to avoid overloading billing server
            return $this->lockScreen($message);

        } catch (\Exception $e) {
            // Resiliency: If billing server has a network hiccup or timeout, we gracefully allow the school application
            // to operate for now, but we will retry next time.
            \Illuminate\Support\Facades\Log::warning('Licensing Server connection timeout or error: ' . $e->getMessage());
            return $next($request);
        }
    }

    /**
     * Render lock / billing screen
     */
    private function lockScreen(string $message): Response
    {
        if (request()->is('api/*') || request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'locked' => true,
            ], 403);
        }

        // Return a beautiful, premium glassmorphism billing alert view inline
        $html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Terkunci - Jejakawan Billing</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            overflow: hidden;
        }
        .container {
            max-width: 500px;
            width: 100%;
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            animation: fadeIn 0.8s ease-out;
        }
        .icon {
            font-size: 64px;
            color: #ef4444;
            margin-bottom: 24px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #fca5a5, #f87171);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p.message {
            font-size: 16px;
            color: #cbd5e1;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .details {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 16px;
            font-family: monospace;
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 30px;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.6);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.08); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>Sistem Terkunci</h1>
        <p class="message">Akses ke aplikasi ditangguhkan karena lisensi berlangganan tidak valid atau telah berakhir.</p>
        <div class="details">
            Keterangan: {$message}
        </div>
        <a href="mailto:support@jejakawan.com" class="btn">Hubungi Dukungan Penjualan</a>
    </div>
</body>
</html>
HTML;

        return response($html, 403);
    }
}
