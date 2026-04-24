<?php

namespace Modules\Cms\Services;

use Modules\Cms\Models\Content;
use Modules\Core\Models\Setting;

class DynamicTagService
{
    /**
     * Resolve all dynamic tags in blocks array
     *
     * @param  array<int, array<string, mixed>>  $blocks  The blocks array with potential dynamic tags
     * @param  Content|null  $content  The content context for post/page tags
     * @param  array<string, mixed>|null  $loopItem  Loop item context if in a loop
     * @return array<int, array<string, mixed>> Blocks with resolved tags
     */
    public function resolveBlocks(array $blocks, ?Content $content = null, ?array $loopItem = null): array
    {
        return $this->processBlocks($blocks, $content, $loopItem);
    }

    /**
     * Recursively process blocks and resolve dynamic tags
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>|null  $loopItem
     * @return array<int, array<string, mixed>>
     */
    protected function processBlocks(array $blocks, ?Content $content, ?array $loopItem): array
    {
        foreach ($blocks as &$block) {
            // Process settings
            if (isset($block['settings']) && is_array($block['settings'])) {
                foreach ($block['settings'] as $key => &$value) {
                    if (is_string($value) && str_starts_with($value, '@dynamic:')) {
                        $tag = str_replace('@dynamic:', '', $value);
                        $value = $this->resolveTag($tag, $content, $loopItem);
                    }
                }
            }

            // Process children recursively
            if (isset($block['children']) && is_array($block['children'])) {
                /** @var array<int, array<string, mixed>> $children */
                $children = $block['children'];
                $block['children'] = $this->processBlocks($children, $content, $loopItem);
            }
        }

        return $blocks;
    }

    /**
     * Resolve a single dynamic tag to its value
     *
     * @param  string  $tag  The tag like "{{post_title}}"
     * @param  Content|null  $content  Content context
     * @param  array<string, mixed>|null  $loopItem  Loop item context
     * @return string The resolved value
     */
    public function resolveTag(string $tag, ?Content $content = null, ?array $loopItem = null): string
    {
        // Strip {{ and }}
        $key = trim(str_replace(['{{', '}}'], '', $tag));

        // 1. Post/Page tags
        if ($content && str_starts_with($key, 'post_')) {
            $author = $content->author;
            $resolved = match ($key) {
                'post_title' => (string) $content->title,
                'post_excerpt' => (string) $content->excerpt,
                'post_content' => (string) $content->body,
                'post_date' => ($content->published_at ?? ($content->created_at ?? now()))->format('M d, Y'),
                'post_author' => (string) $author->name,
                'post_author_avatar' => is_scalar($avatar = $author->getAttribute('avatar')) ? (string) $avatar : '',
                'post_featured_image' => is_scalar($featuredImage = $content->getAttribute('featured_image')) ? (string) $featuredImage : '',
                'post_url' => url('/'.$content->slug),
                'post_category' => $content->category ? (string) $content->category->name : '',
                'post_tags' => $content->tags->pluck('name')->join(', '),
                default => ''
            };

            return is_scalar($resolved) ? (string) $resolved : '';
        }

        // 2. Loop item tags
        if ($loopItem && str_starts_with($key, 'loop_')) {
            $resolvedValue = match ($key) {
                'loop_title' => is_scalar($loopItem['title'] ?? null) ? (string) $loopItem['title'] : '',
                'loop_excerpt' => is_scalar($loopItem['excerpt'] ?? null) ? (string) $loopItem['excerpt'] : '',
                'loop_date' => is_scalar($loopItem['date'] ?? null) ? (string) $loopItem['date'] : '',
                'loop_author' => is_scalar($loopItem['author'] ?? null) ? (string) $loopItem['author'] : '',
                'loop_thumbnail' => is_scalar($thumbRaw = ($loopItem['thumbnail'] ?? ($loopItem['featured_image'] ?? null))) ? (string) $thumbRaw : '',
                'loop_url' => is_scalar($loopItem['url'] ?? null) ? (string) $loopItem['url'] : '',
                'loop_category' => is_scalar($loopItem['category'] ?? null) ? (string) $loopItem['category'] : '',
                'loop_index' => is_scalar($loopItem['index'] ?? null) ? (string) $loopItem['index'] : '0',
                default => ''
            };

            return (string) $resolvedValue;
        }

        // 3. Site tags
        if (str_starts_with($key, 'site_') || str_starts_with($key, 'current_')) {
            /** @var mixed $appName */
            $appName = config('app.name') ?? 'Laravel';
            $appNameStr = is_scalar($appName) ? (string) $appName : 'Laravel';

            $resolvedValue = match ($key) {
                'site_title' => is_scalar($siteTitle = Setting::get('site_title', $appNameStr)) ? (string) $siteTitle : $appNameStr,
                'site_tagline' => is_scalar($siteTagline = Setting::get('site_tagline', '')) ? (string) $siteTagline : '',
                'site_logo' => is_scalar($siteLogo = Setting::get('site_logo', '')) ? (string) $siteLogo : '',
                'current_date' => now()->format('M d, Y'),
                'current_year' => (string) now()->year,
                default => ''
            };

            return (string) $resolvedValue;
        }

        // 4. Archive tags
        if (str_starts_with($key, 'archive_')) {
            return match ($key) {
                'archive_title' => 'Archive',
                'archive_description' => '',
                'archive_count' => '0',
                default => ''
            };
        }

        // 5. User tags
        if (str_starts_with($key, 'user_')) {
            $user = auth()->user();
            if (! $user instanceof \Modules\Core\Models\User) {
                return '';
            }

            $resolvedValue = match ($key) {
                'user_name' => (string) $user->name,
                'user_email' => (string) $user->email,
                'user_avatar' => is_scalar($avatar = $user->getAttribute('avatar')) ? (string) $avatar : '',
                default => ''
            };

            return (string) $resolvedValue;
        }

        return '';
    }
}
