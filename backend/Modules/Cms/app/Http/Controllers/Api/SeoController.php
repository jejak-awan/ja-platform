<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Cms\Models\Content;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class SeoController extends BaseApiController
{
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

    public function analyzeContent(Content $content): \Illuminate\Http\JsonResponse
    {
        $score = 0;
        $maxScore = 100;
        $issues = [];
        $suggestions = [];

        // Check title
        if ($content->title) {
            $titleLength = strlen((string) $content->title);
            if ($titleLength >= 30 && $titleLength <= 60) {
                $score += 20;
            } else {
                $issues[] = 'Title length should be between 30-60 characters';
                $suggestions[] = 'Optimize title length for better SEO';
            }
        } else {
            $issues[] = 'Title is missing';
        }

        // Check meta title
        if ($content->meta_title) {
            $metaTitleLength = strlen((string) $content->meta_title);
            if ($metaTitleLength >= 30 && $metaTitleLength <= 60) {
                $score += 15;
            } else {
                $issues[] = 'Meta title length should be between 30-60 characters';
            }
        } else {
            $suggestions[] = 'Add meta title for better SEO';
        }

        // Check meta description
        if ($content->meta_description) {
            $metaDescLength = strlen((string) $content->meta_description);
            if ($metaDescLength >= 120 && $metaDescLength <= 160) {
                $score += 15;
            } else {
                $issues[] = 'Meta description length should be between 120-160 characters';
            }
        } else {
            $suggestions[] = 'Add meta description for better SEO';
        }

        // Check excerpt
        if ($content->excerpt) {
            $score += 10;
        } else {
            $suggestions[] = 'Add excerpt for better content preview';
        }

        // Check featured image
        if ($content->featured_image) {
            $score += 10;
        } else {
            $suggestions[] = 'Add featured image for better social sharing';
        }

        // Check OG image
        if ($content->og_image) {
            $score += 10;
        } else {
            $suggestions[] = 'Add OG image for better social media preview';
        }

        // Check body length
        if ($content->body) {
            $bodyLength = strlen(strip_tags((string) $content->body));
            if ($bodyLength >= 300) {
                $score += 15;
            } else {
                $issues[] = 'Content body is too short (minimum 300 characters recommended)';
            }
        }

        // Check slug
        if ($content->slug) {
            $slugLength = strlen((string) $content->slug);
            if ($slugLength <= 100) {
                $score += 5;
            } else {
                $issues[] = 'Slug is too long';
            }
        }

        // Check keywords
        if ($content->meta_keywords) {
            $keywords = explode(',', (string) $content->meta_keywords);
            if (count($keywords) >= 3 && count($keywords) <= 10) {
                $score += 10;
            } else {
                $suggestions[] = 'Add 3-10 relevant keywords';
            }
        } else {
            $suggestions[] = 'Add meta keywords for better SEO';
        }

        // Clamp score to maxScore
        $score = min($score, $maxScore);

        return $this->success([
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => round(($score / $maxScore) * 100, 2),
            'grade' => $this->getGrade($score, $maxScore),
            'issues' => $issues,
            'suggestions' => $suggestions,
        ], 'Content SEO analysis completed');
    }

    protected function getGrade(int $score, int $maxScore): string
    {
        $percentage = ($score / $maxScore) * 100;

        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B';
        }
        if ($percentage >= 60) {
            return 'C';
        }
        if ($percentage >= 50) {
            return 'D';
        }

        return 'F';
    }

    public function generateSchema(Content $content): \Illuminate\Http\JsonResponse
    {
        $publishedAtRaw = $content->published_at ?? $content->created_at;
        $publishedAt = $publishedAtRaw instanceof \Illuminate\Support\Carbon ? $publishedAtRaw->toIso8601String() : now()->toIso8601String();

        $updatedAtRaw = $content->updated_at;
        $updatedAt = $updatedAtRaw instanceof \Illuminate\Support\Carbon ? $updatedAtRaw->toIso8601String() : now()->toIso8601String();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $content->type === 'post' ? 'BlogPosting' : 'Article',
            'headline' => $content->title,
            'description' => $content->meta_description ?? $content->excerpt,
            'datePublished' => $publishedAt,
            'dateModified' => $updatedAt,
            'author' => [
                '@type' => 'Person',
                'name' => $content->author->name ?? 'Unknown',
            ],
        ];

        if ($content->featured_image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => url((string) $content->featured_image),
            ];
        }

        if ($content->category) {
            $schema['articleSection'] = $content->category->name;
        }

        return $this->success($schema, 'Schema generated successfully');
    }
}
