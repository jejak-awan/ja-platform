<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\QueryPerformanceService;
use Tests\TestCase;

class QueryPerformanceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_enable_and_get_query_log()
    {
        DB::shouldReceive('enableQueryLog')->once();
        DB::shouldReceive('getQueryLog')->once()->andReturn([
            ['query' => 'select * from users', 'bindings' => [], 'time' => 1.5],
        ]);

        $service = new QueryPerformanceService;
        $service->enableQueryLog();

        $log = $service->getQueryLog();
        $this->assertCount(1, $log);
        $this->assertEquals('select * from users', $log[0]['query']);
    }

    public function test_analyze_queries()
    {
        $queries = [
            ['query' => 'select * from users where id = ?', 'bindings' => [1], 'time' => 50.0],
            ['query' => 'select * from users where id = ?', 'bindings' => [1], 'time' => 120.0], // slow query + duplicate
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [1], 'time' => 10.0],
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [2], 'time' => 10.0],
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [3], 'time' => 10.0],
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [4], 'time' => 10.0],
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [5], 'time' => 10.0],
            ['query' => 'select * from posts where user_id = ?', 'bindings' => [6], 'time' => 10.0], // N+1 trigger (6 times)
        ];

        $service = new QueryPerformanceService;
        $analysis = $service->analyzeQueries($queries);

        $this->assertEquals(8, $analysis['total_queries']);
        $this->assertEquals(230.0, $analysis['total_time']);
        $this->assertCount(1, $analysis['slow_queries']);
        $this->assertCount(2, $analysis['duplicate_queries']); // the N+1 queries also count as duplicate strings
        $this->assertCount(1, $analysis['n_plus_one_candidates']);

        $this->assertEquals('posts', $analysis['n_plus_one_candidates'][0]['pattern']);
    }

    public function test_extract_query_pattern_fallback()
    {
        // To cover substr fallback for pattern extraction
        $service = new QueryPerformanceService;

        // Expose protected static method for testing
        $method = new \ReflectionMethod(QueryPerformanceService::class, 'extractQueryPattern');
        $method->setAccessible(true);

        $pattern = $method->invoke($service, 'UPDATE users SET name = ? WHERE id = ?');
        $this->assertEquals('UPDATE users SET name = * WHERE id = *', $pattern);
    }

    public function test_log_slow_queries()
    {
        DB::shouldReceive('getQueryLog')->once()->andReturn([
            ['query' => 'select * from users', 'bindings' => [], 'time' => 150.0],
            ['query' => 'select * from posts', 'bindings' => [], 'time' => 50.0],
        ]);

        Log::shouldReceive('warning')->once()->withArgs(function ($message, $context) {
            return $message === 'Slow queries detected' && $context['count'] === 1;
        });

        $service = new QueryPerformanceService;
        $service->logSlowQueries(100.0);
    }

    public function test_get_performance_stats()
    {
        DB::shouldReceive('getQueryLog')->once()->andReturn([
            ['query' => 'select * from users', 'bindings' => [], 'time' => 150.0],
            ['query' => 'select * from posts', 'bindings' => [], 'time' => 50.0],
        ]);

        $service = new QueryPerformanceService;
        $stats = $service->getPerformanceStats();

        $this->assertEquals(2, $stats['total_queries']);
        $this->assertEquals(200.0, $stats['total_time']);
        $this->assertEquals(100.0, $stats['average_time']);
        $this->assertEquals(1, $stats['slow_queries_count']);
        $this->assertEquals(150.0, $stats['max_time']);
        $this->assertEquals(50.0, $stats['min_time']);
    }

    public function test_get_performance_stats_empty()
    {
        DB::shouldReceive('getQueryLog')->once()->andReturn([]);

        $service = new QueryPerformanceService;
        $stats = $service->getPerformanceStats();

        $this->assertEquals(0, $stats['total_queries']);
        $this->assertEquals(0.0, $stats['average_time']);
        $this->assertEquals(0, $stats['slow_queries_count']);
    }

    public function test_cache_metrics()
    {
        $service = new QueryPerformanceService;

        $metrics = ['total_queries' => 10];
        $service->cacheMetrics('test_key', $metrics, 5);

        $this->assertEquals($metrics, $service->getCachedMetrics('test_key'));
        $this->assertEquals($metrics, Cache::get('query_performance:test_key'));
    }
}
