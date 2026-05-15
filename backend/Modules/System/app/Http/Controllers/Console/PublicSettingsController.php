<?php

namespace Modules\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\System\Models\Setting;

/**
 * Controller for public settings (no auth required)
 * Only exposes non-sensitive settings that the frontend needs before login
 */
class PublicSettingsController extends BaseApiController
{
    /**
     * Get public settings for the frontend
     */
    public function index(Request $request): JsonResponse
    {
        $payload = [
            'enable_registration' => (bool) Setting::get('enable_registration', true),
            'require_email_verification' => (bool) Setting::get('require_email_verification', true),
            'site_name' => Setting::get('site_name', 'JA-Platform'),
            'site_description' => Setting::get('site_description', ''),
            'site_url' => Setting::get('site_url', config('app.url')),
            'admin_email' => Setting::get('admin_email', ''),
            'site_version' => config('app.version'),
            'site_logo' => Setting::get('site_logo', '/logo.png'),
            'site_favicon' => Setting::get('site_favicon', '/favicon.ico'),
            
            // App Branding (Core)
            'app_name' => Setting::get('app_name', 'Janari App'),
            'app_logo' => Setting::get('app_logo', ''),
            'app_favicon' => Setting::get('app_favicon', ''),
            'app_license_tier' => Setting::get('license_type') ?: Setting::get('app_license_tier', 'basic'),

            // Contact Info
            'contact_email' => Setting::get('contact_email', 'hello@janari.com'),
            'contact_phone' => Setting::get('contact_phone', ''),
            'contact_address' => Setting::get('contact_address', ''),

            // Social Links
            'social_twitter' => Setting::get('social_twitter', ''),
            'social_github' => Setting::get('social_github', ''),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_instagram' => Setting::get('social_instagram', ''),

            // Maintenance Mode
            'maintenance_mode' => (bool) Setting::get('maintenance_mode', false),
            'maintenance_title' => Setting::get('maintenance_title', 'Under Maintenance'),
            'maintenance_message' => Setting::get('maintenance_message', ''),
            'maintenance_countdown_enabled' => (bool) Setting::get('maintenance_countdown_enabled', false),
            'maintenance_end_time' => Setting::get('maintenance_end_time', ''),
        ];

        $etagSource = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $etag = '"'.sha1(is_string($etagSource) ? $etagSource : '').'"';
        $ifNoneMatch = $request->headers->get('If-None-Match');

        if (is_string($ifNoneMatch) && trim($ifNoneMatch) === $etag) {
            return response()->json(null, 304, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=60, stale-while-revalidate=300',
                'Vary' => 'Accept-Encoding',
            ]);
        }

        $response = $this->success($payload, 'Public settings retrieved successfully');
        $response->headers->set('ETag', $etag);
        $response->headers->set('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}
