<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\Theme;
use Modules\Cms\Services\ThemeService;
use Modules\Core\Http\Controllers\Api\BaseApiController;
use Modules\Core\Models\Setting;

class SettingController extends BaseApiController
{
    /**
     * Get all CMS settings.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $cmsGroups = ['general', 'seo', 'media', 'comments', 'ai', 'analytics'];
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

    /**
     * Test storage connection dynamically
     */
    public function testStorage(Request $request): \Illuminate\Http\JsonResponse
    {
        $driverRaw = $request->input('driver');
        $driver = is_string($driverRaw) ? $driverRaw : '';
        $configRaw = $request->input('config'); // Array of settings formData
        /** @var array<string, mixed> $config */
        $config = is_array($configRaw) ? $configRaw : [];

        if (! $driver || empty($config)) {
            return $this->error('Driver and configuration are required', 400);
        }

        // Map config keys to Laravel filesystem config
        $diskConfig = ['driver' => $driver];

        if ($driver === 's3') {
            $awsKeyRaw = $config['aws_access_key_id'] ?? '';
            $awsSecretRaw = $config['aws_secret_access_key'] ?? '';
            $awsRegionRaw = $config['aws_default_region'] ?? 'us-east-1';
            $awsBucketRaw = $config['aws_bucket'] ?? '';
            $awsEndpointRaw = $config['aws_endpoint'] ?? '';

            $diskConfig = array_merge($diskConfig, [
                'key' => is_scalar($awsKeyRaw) ? (string) $awsKeyRaw : '',
                'secret' => is_scalar($awsSecretRaw) ? (string) $awsSecretRaw : '',
                'region' => is_scalar($awsRegionRaw) ? (string) $awsRegionRaw : 'us-east-1',
                'bucket' => is_scalar($awsBucketRaw) ? (string) $awsBucketRaw : '',
                'endpoint' => is_scalar($awsEndpointRaw) ? (string) $awsEndpointRaw : '',
                'use_path_style_endpoint' => ! empty($config['aws_endpoint']),
            ]);
        } elseif ($driver === 'google') {
            $gClientIdRaw = $config['google_client_id'] ?? '';
            $gClientSecretRaw = $config['google_client_secret'] ?? '';
            $gRefreshTokenRaw = $config['google_refresh_token'] ?? '';
            $gFolderIdRaw = $config['google_folder_id'] ?? '/';

            $diskConfig = array_merge($diskConfig, [
                'clientId' => is_scalar($gClientIdRaw) ? (string) $gClientIdRaw : '',
                'clientSecret' => is_scalar($gClientSecretRaw) ? (string) $gClientSecretRaw : '',
                'refreshToken' => is_scalar($gRefreshTokenRaw) ? (string) $gRefreshTokenRaw : '',
                'folderId' => is_scalar($gFolderIdRaw) ? (string) $gFolderIdRaw : '/',
            ]);
        } elseif ($driver === 'ftp') {
            $ftpHostRaw = $config['ftp_host'] ?? '';
            $ftpUsernameRaw = $config['ftp_username'] ?? '';
            $ftpPasswordRaw = $config['ftp_password'] ?? '';
            $ftpPortRaw = $config['ftp_port'] ?? 21;
            $ftpRootRaw = $config['ftp_root'] ?? '';

            $diskConfig = array_merge($diskConfig, [
                'host' => is_scalar($ftpHostRaw) ? (string) $ftpHostRaw : '',
                'username' => is_scalar($ftpUsernameRaw) ? (string) $ftpUsernameRaw : '',
                'password' => is_scalar($ftpPasswordRaw) ? (string) $ftpPasswordRaw : '',
                'port' => is_numeric($ftpPortRaw) ? (int) $ftpPortRaw : 21,
                'root' => is_scalar($ftpRootRaw) ? (string) $ftpRootRaw : '',
                'ssl' => (bool) ($config['ftp_ssl'] ?? false),
            ]);
        } elseif ($driver === 'dropbox') {
            $dbTokenRaw = $config['dropbox_authorization_token'] ?? '';
            $diskConfig = array_merge($diskConfig, [
                'authorizationToken' => is_scalar($dbTokenRaw) ? (string) $dbTokenRaw : '',
            ]);
        }

        // Set dynamic config
        \Illuminate\Support\Facades\Config::set("filesystems.disks.test_{$driver}", $diskConfig);

        try {
            // Attempt to list contents
            \Illuminate\Support\Facades\Storage::disk("test_{$driver}")->listContents('/');

            return $this->success(null, 'Connection successful! Storage is accessible.');
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            // Helpful messages for missing drivers
            if (str_contains($message, 'Driver [google] is not supported')) {
                $message = 'Google Drive adapter is missing. Please run: composer require spatie/flysystem-google-drive';
            }
            if (str_contains($message, 'Driver [s3] is not supported')) {
                $message = 'AWS S3 adapter is missing. Please run: composer require league/flysystem-aws-s3-v3';
            }
            if (str_contains($message, 'Driver [dropbox] is not supported')) {
                $message = 'Dropbox adapter is missing. Please run: composer require spatie/flysystem-dropbox';
            }
            if (str_contains($message, 'Driver [ftp] is not supported')) {
                $message = 'FTP adapter is missing. Please run: composer require league/flysystem-ftp';
            }

            return $this->error('Connection failed: '.$message, 500);
        }
    }
}
