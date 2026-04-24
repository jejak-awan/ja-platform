<?php

namespace Modules\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Core\Models\SlowQuery;

class SlowQueryController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = SlowQuery::with('user');

        if ($request->filled('route')) {
            $route = is_string($request->input('route')) ? $request->input('route') : '';
            $query->where('route', 'like', "%{$route}%");
        }

        if ($request->filled('min_duration')) {
            $query->where('duration', '>=', $request->input('min_duration'));
        }

        if ($request->filled('date_from')) {
            $dateFrom = is_string($request->input('date_from')) ? $request->input('date_from') : null;
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = is_string($request->input('date_to')) ? $request->input('date_to') : null;
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;

        $queries = $query->latest()->paginate($perPage);

        return $this->paginated($queries, 'Slow queries retrieved');
    }

    public function statistics(): \Illuminate\Http\JsonResponse
    {
        /** @var mixed $avg */
        $avg = SlowQuery::avg('duration');
        /** @var mixed $max */
        $max = SlowQuery::max('duration');

        $stats = [
            'total' => SlowQuery::count(),
            'avg_duration' => is_numeric($avg) ? (int) $avg : 0,
            'max_duration' => is_numeric($max) ? (int) $max : 0,
            'today' => SlowQuery::whereDate('created_at', today())->count(),
        ];

        return $this->success($stats, 'Statistics retrieved');
    }
}
