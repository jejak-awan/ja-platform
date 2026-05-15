<?php

namespace Modules\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\System\Helpers\IpHelper;

class SearchQuery extends Model
{
    protected $table = 'cms_search_queries';



    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'ip_address',
        'user_agent',
        'filters',
        'searched_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'searched_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  string  $query
     * @param  int  $resultsCount
     * @param  array<string, mixed>  $filters
     */
    public static function log($query, $resultsCount, $filters = []): self
    {
        return self::create([
            'query' => $query,
            'results_count' => $resultsCount,
            'user_id' => Auth::id(),
            'ip_address' => IpHelper::getClientIp(request()),
            'user_agent' => request()->userAgent(),
            'filters' => $filters,
            'searched_at' => now(),
        ]);
    }

    /**
     * @param  int|null  $limit
     * @param  int|null  $days
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getPopularQueries($limit = 10, $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('searched_at', '>=', now()->subDays($days ?? 30))
            ->select('query', DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit ?? 10)
            ->get();
    }

    /**
     * @param  int|null  $limit
     * @param  int|null  $days
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getNoResultsQueries($limit = 10, $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('searched_at', '>=', now()->subDays($days ?? 30))
            ->where('results_count', 0)
            ->select('query', DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit ?? 10)
            ->get();
    }
}
