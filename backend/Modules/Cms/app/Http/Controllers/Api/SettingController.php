<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\Theme;
use Modules\Cms\Services\ThemeService;
use Modules\System\Http\Controllers\BaseApiController;
use Modules\System\Models\Setting;

class SettingController extends BaseApiController
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view settings')->only(['index', 'getGroup']);
        $this->middleware('permission:manage settings')->only(['bulkUpdate']);
    }
    /**
     * Get all CMS settings.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $cmsGroups = ['general', 'seo', 'comments', 'analytics'];
        $settings = Setting::whereIn('group', $cmsGroups)
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        return $this->success($settings, 'CMS settings retrieved successfully');
    }

    /**
     * Get settings for a specific group.
     */
    public function getGroup(string $group): \Illuminate\Http\JsonResponse
    {
        $settings = Setting::getGroup($group);

        return $this->success($settings, 'CMS settings retrieved successfully');
    }

    /**
     * Bulk update CMS settings.
     */
    public function bulkUpdate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'sometimes|in:string,integer,boolean,json,text,password,number,datetime,image,media',
            'settings.*.group' => 'sometimes|string',
        ]);

        $settings = is_array($validated['settings']) ? $validated['settings'] : [];
        foreach ($settings as $settingData) {
            if (is_array($settingData) && isset($settingData['key'])) {
                $sKeyRaw = $settingData['key'];
                $sKey = is_scalar($sKeyRaw) ? (string) $sKeyRaw : '';
                $sValue = $settingData['value'] ?? null;
                $sTypeRaw = $settingData['type'] ?? 'string';
                $sType = is_scalar($sTypeRaw) ? (string) $sTypeRaw : 'string';
                $sGroupRaw = $settingData['group'] ?? 'general';
                $sGroup = is_scalar($sGroupRaw) ? (string) $sGroupRaw : 'general';

                Setting::set($sKey, $sValue, $sType, $sGroup);

                // Sync site_logo to active theme's brand_logo
                if ($sKey === 'site_logo') {
                    $theme = Theme::where('is_active', true)->where('type', 'frontend')->first();
                    if ($theme) {
                        $themeSettings = is_array($theme->settings) ? $theme->settings : [];
                        $themeSettings['brand_logo'] = $sValue;
                        $theme->update(['settings' => $themeSettings]);

                        // Clear theme cache using the service
                        app(ThemeService::class)->clearThemeCache($theme);
                    }
                }
            }
        }

        return $this->success(null, 'CMS settings updated successfully');
    }
}
