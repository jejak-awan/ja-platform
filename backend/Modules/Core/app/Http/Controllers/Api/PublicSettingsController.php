<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Models\Setting;

/**
 * Controller for public settings (no auth required)
 * Only exposes non-sensitive settings that the frontend needs before login
 */
class PublicSettingsController extends BaseApiController
{
    /**
     * Get public settings for the frontend
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'enable_registration' => (bool) Setting::get('enable_registration', true),
            'require_email_verification' => (bool) Setting::get('require_email_verification', true),
            'site_name' => Setting::get('site_name', 'JA-Edu'),
            'site_description' => Setting::get('site_description', ''),
            'site_url' => Setting::get('site_url', config('app.url')),
            'admin_email' => Setting::get('admin_email', ''),
            'site_version' => config('app.version'),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', '/favicon.svg'),

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
        ], 'Public settings retrieved successfully');
    }
}
