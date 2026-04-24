<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Media;
use Modules\Core\Models\AnalyticsVisit;
use Modules\Core\Models\User;

/**
 * @OA\Tag(name="Dashboard")
 */
class DashboardController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/admin",
     *     summary="Get admin dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function admin(Request $request): \Illuminate\Http\JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? max(1, min(366, (int) $daysRaw)) : 30;

        $data = [
            'stats' => [
                'contents' => $this->getContentStats(),
                'media' => $this->getMediaStats(),
                'users' => $this->getUserStats(),
            ],
            'charts' => [
                'contentByStatus' => $this->getContentByStatus(),
                'mediaByType' => $this->getMediaByType(),
                'contentTraffic' => $this->getSiteTrafficSeries($days),
                'userActivity' => $this->getUserActivity($days),
            ],
        ];

        return $this->success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/creator",
     *     summary="Get creator dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer", default=30)),
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function creator(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = (int) $user->id;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $cacheKey = "dashboard_creator_data_{$userId}_{$days}";

        $data = Cache::remember($cacheKey, 300, function () use ($userId, $days) {
            return [
                'stats' => [
                    'myContents' => $this->getMyContentStats($userId),
                    'myMedia' => $this->getMyMediaStats($userId),
                ],
                'charts' => [
                    'myContentByStatus' => $this->getMyContentByStatus($userId),
                    'contentTraffic' => $this->getMyContentTraffic($userId, $days),
                ],
                'topContent' => $this->getMyTopContent($userId),
            ];
        });

        return $this->success($data);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/viewer",
     *     summary="Get viewer dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function viewer(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = Cache::remember('dashboard_viewer_data', 600, function () {
            return Content::where('status', 'published')
                ->latest()
                ->take(5)
                ->select('id', 'title', 'slug', 'created_at')
                ->get();
        });

        return $this->success($data);
    }

    // Helper methods
    /**
     * @return array{total: int, published: int, draft: int, pending: int}
     */
    private function getContentStats(): array
    {
        return [
            'total' => Content::count(),
            'published' => Content::where('status', 'published')->count(),
            'draft' => Content::where('status', 'draft')->count(),
            'pending' => Content::where('status', 'pending')->count(),
        ];
    }

    /**
     * @return array{total: int, images: int, videos: int, documents: int}
     */
    private function getMediaStats(): array
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%')->count(),
        ];
    }

    /**
     * @return array{total: int, active: int}
     */
    private function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Content>
     */
    private function getContentByStatus(): \Illuminate\Support\Collection
    {
        return Content::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Media>
     */
    private function getMediaByType(): \Illuminate\Support\Collection
    {
        $driver = DB::connection()->getDriverName();
        $typeExpr = match ($driver) {
            'pgsql' => "split_part(mime_type, '/', 1)",
            'sqlite' => "CASE WHEN mime_type LIKE '%/%' THEN SUBSTR(mime_type, 1, INSTR(mime_type, '/') - 1) ELSE mime_type END",
            default => 'SUBSTRING_INDEX(mime_type, \'/\', 1)',
        };

        return Media::select(
            DB::raw("{$typeExpr} as type"),
            DB::raw('count(*) as count')
        )
            ->groupBy(DB::raw($typeExpr))
            ->get();
    }

    /**
     * Daily site page views from analytics_visits (aligned with dashboard time filter).
     *
     * @return array<int, array{period: string, visits: int}>
     */
    private function getSiteTrafficSeries(int $days): array
    {
        $days = max(1, min(366, $days));
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $start = now()->copy()->subDays($i)->startOfDay();
            $end = now()->copy()->subDays($i)->endOfDay();
            $period = $start->toDateString();
            $visits = (int) AnalyticsVisit::query()
                ->whereBetween('visited_at', [$start, $end])
                ->count();
            $series[] = ['period' => $period, 'visits' => $visits];
        }

        return $series;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\User>
     */
    private function getUserActivity(int $days = 30): \Illuminate\Support\Collection
    {
        $days = max(1, min(366, $days));
        $driver = DB::connection()->getDriverName();
        $dateExpr = match ($driver) {
            'pgsql' => 'DATE(created_at)',
            'sqlite' => "DATE(created_at)",
            default => 'DATE(created_at)',
        };

        return User::query()
            ->select(DB::raw("{$dateExpr} as date"), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->copy()->subDays($days)->startOfDay())
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('date')
            ->get();
    }

    /**
     * @return array{total: int, published: int, draft: int, pending: int}
     */
    private function getMyContentStats(int $userId): array
    {
        return [
            'total' => (int) Content::where('author_id', $userId)->count(),
            'published' => (int) Content::where('author_id', $userId)->where('status', 'published')->count(),
            'draft' => (int) Content::where('author_id', $userId)->where('status', 'draft')->count(),
            'pending' => (int) Content::where('author_id', $userId)->where('status', 'pending')->count(),
        ];
    }

    /**
     * @return array{total: int, size: float|int}
     */
    private function getMyMediaStats(int $userId): array
    {
        return [
            'total' => Media::where('author_id', $userId)->count(),
            'size' => (float) Media::where('author_id', $userId)->sum('size'),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Content>
     */
    private function getMyContentByStatus(int $userId): \Illuminate\Support\Collection
    {
        return Content::where('author_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Cms\Models\Content>
     */
    private function getMyTopContent(int $userId): \Illuminate\Support\Collection
    {
        return Content::where('author_id', $userId)
            ->orderBy('views', 'desc')
            ->take(5)
            ->select('id', 'title', 'slug', 'views', 'status', 'created_at', 'type')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\AnalyticsVisit>|array<empty, empty>
     */
    private function getMyContentTraffic(int $userId, int $days = 30): \Illuminate\Support\Collection|array
    {
        $slugs = Content::where('author_id', $userId)->pluck('slug')->toArray();

        if (empty($slugs)) {
            return [];
        }

        // Optimize: Use whereIn if URLs are simple slugs, or optimized LIKE if they are paths.
        // For CMS, URLs usually contain the slug at the end or as a segment.
        // We'll use a more efficient approach by limiting the strings we search for.
        $exactUrls = array_map(function ($s) {
            $sStr = is_scalar($s) ? (string) $s : '';

            return "/{$sStr}";
        }, $slugs);

        return AnalyticsVisit::where(function ($query) use ($exactUrls, $slugs) {
            $query->whereIn('url', $exactUrls);
            foreach ($slugs as $slug) {
                $slugStr = is_scalar($slug) ? (string) $slug : '';
                // Keep the LIKE for paths like /articles/slug-name
                $query->orWhere('url', 'like', "%/{$slugStr}%");
            }
        })
            ->where('visited_at', '>=', now()->subDays($days))
            ->select(DB::raw('CAST(visited_at AS date) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('CAST(visited_at AS date)'))
            ->orderBy('date')
            ->get();
    }
}
