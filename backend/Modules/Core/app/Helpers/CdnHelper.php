<?php

namespace Modules\Core\Helpers;

/**
 * CDN Helper Class
 * Provides methods for CDN URL generation and image optimization.
 * Supports multiple CDN providers and automatic URL generation.
 */
class CdnHelper
{
    /**
     * Check if CDN is enabled
     */
    public static function isEnabled(): bool
    {
        return config('cdn.enabled', false) && ! empty(config('cdn.url'));
    }

    /**
     * Generate CDN URL for a given path
     */
    public static function url(string $path, ?string $domain = null): string
    {
        if (! self::isEnabled()) {
            // Return relative path if CDN is disabled
            return '/'.ltrim($path, '/');
        }

        /** @var string $cdnUrl */
        $cdnUrl = $domain ? config("cdn.domains.{$domain}", config('cdn.url')) : config('cdn.url');
        /** @var string $pathPrefix */
        $pathPrefix = config('cdn.path_prefix', '/storage');

        // Remove leading slash from path if present
        $path = ltrim($path, '/');

        // Build CDN URL
        $url = rtrim($cdnUrl, '/').'/'.ltrim($pathPrefix, '/').'/'.$path;

        return $url;
    }

    /**
     * Generate CDN URL for media file
     */
    public static function mediaUrl(string $path): string
    {
        return self::url($path);
    }

    /**
     * Generate CDN URL for thumbnail
     */
    public static function thumbnailUrl(string $path): string
    {
        return self::url($path);
    }

    /**
     * Get image optimization parameters for CDN
     *
     * @return array{width: int, height: int, quality: int, format: string}
     */
    public static function getImageParams(?int $width = null, ?int $height = null, ?int $quality = null): array
    {
        /** @var int $maxWidth */
        $maxWidth = config('cdn.image.max_width', 1920);
        /** @var int $maxHeight */
        $maxHeight = config('cdn.image.max_height', 1080);
        /** @var int $qualityConf */
        $qualityConf = config('cdn.image.quality', 85);
        /** @var string $format */
        $format = config('cdn.image.format', 'auto');

        return [
            'width' => (int) ($width ?? $maxWidth),
            'height' => (int) ($height ?? $maxHeight),
            'quality' => (int) ($quality ?? $qualityConf),
            'format' => $format,
        ];
    }

    /**
     * Generate optimized image URL with CDN parameters
     */
    public static function optimizedImageUrl(string $path, ?int $width = null, ?int $height = null, ?int $quality = null): string
    {
        $url = self::url($path);

        // Add query parameters for image optimization (CDN-specific)
        $params = self::getImageParams($width, $height, $quality);
        $queryParams = [];

        if ($params['width']) {
            $queryParams['w'] = $params['width'];
        }
        if ($params['height']) {
            $queryParams['h'] = $params['height'];
        }
        if ($params['quality']) {
            $queryParams['q'] = $params['quality'];
        }
        if ($params['format'] !== 'auto') {
            $queryParams['f'] = $params['format'];
        }

        if (! empty($queryParams)) {
            $url .= '?'.http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Get cache control header for file type
     */
    public static function getCacheControl(string $mimeType): string
    {
        /** @var array<string, string> $config */
        $config = config('cdn.cache_control', []);

        if (str_starts_with($mimeType, 'image/')) {
            return $config['images'] ?? $config['default'] ?? 'public, max-age=31536000';
        }

        if (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return $config['documents'] ?? $config['default'] ?? 'public, max-age=2592000';
        }

        return $config['default'] ?? 'public, max-age=86400';
    }
}
