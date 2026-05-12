<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Cms\Models\Content;
use Modules\Core\Http\Controllers\Api\BaseApiController;

use Modules\Cms\Services\SeoService;

class SeoController extends BaseApiController
{
    protected SeoService $seoService;

    public function __construct(SeoService $seoService)
    {
        $this->seoService = $seoService;
    }

    public function generateSitemap(): \Illuminate\Http\JsonResponse
    {
        // Sitemap is generated on-the-fly by SitemapController
        return $this->success([
            'url' => url('/sitemap.xml'),
        ], 'Sitemap is available at /sitemap.xml');
    }

    public function getRobotsTxt(): \Illuminate\Http\JsonResponse
    {
        $path = public_path('robots.txt');

        if (File::exists($path)) {
            $content = File::get($path);
        } else {
            $content = "User-agent: *\nAllow: /\n\nSitemap: ".url('/sitemap.xml');
        }

        return $this->success([
            'content' => $content,
        ], 'Robots.txt retrieved successfully');
    }

    public function updateRobotsTxt(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $content = is_string($validated['content']) ? $validated['content'] : '';
        $path = public_path('robots.txt');
        File::put($path, $content);

        return $this->success([
            'content' => $content,
        ], 'Robots.txt updated successfully');
    }

    /**
     * Analyze content SEO using the SeoService.
     */
    public function analyzeContent(Content $content): \Illuminate\Http\JsonResponse
    {
        $analysis = $this->seoService->analyze($content);

        return $this->success($analysis, 'Content SEO analysis completed');
    }

    /**
     * Generate Schema markup using the SeoService.
     */
    public function generateSchema(Content $content): \Illuminate\Http\JsonResponse
    {
        $schemaType = $content->type === 'post' ? 'BlogPosting' : 'Article';
        $schema = $this->seoService->generateSchema($content, $schemaType);

        return $this->success($schema, 'Schema generated successfully');
    }
}
