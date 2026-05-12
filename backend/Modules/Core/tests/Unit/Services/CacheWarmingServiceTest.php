<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Models\Media;
use Modules\Core\Models\Tag;
use Modules\Core\Models\Language;
use Modules\Core\Services\CacheWarmingService;
use Tests\TestCase;

class CacheWarmingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_warm_all_core_data()
    {
        Tag::forceCreate(['name' => 'Tag', 'slug' => 'tag']);
        
        Media::forceCreate([
            'name' => 'M', 'file_name' => 'm.jpg', 'disk' => 'public', 'path' => 'm.jpg',
            'size' => 100, 'is_shared' => true,
        ]);

        Language::forceCreate([
            'name' => 'English', 'code' => 'en', 'is_active' => true, 'is_default' => true, 'sort_order' => 1,
        ]);

        $service = new CacheWarmingService;
        $results = $service->warmAll();

        $this->assertIsArray($results);
        $this->assertGreaterThan(0, $results['tags']);
        $this->assertGreaterThan(0, $results['media']);
        $this->assertGreaterThan(0, $results['languages']);
        $this->assertGreaterThan(0, $results['statistics']);
    }

    public function test_registry_warmer()
    {
        $service = new CacheWarmingService;
        CacheWarmingService::registerWarmer('test_module', function() {
            return 42;
        });

        $results = $service->warmAll();
        $this->assertEquals(42, $results['test_module']);
    }

    public function test_warm_by_type()
    {
        $service = new CacheWarmingService;
        $this->assertGreaterThanOrEqual(0, $service->warmByType('tags'));
        $this->assertGreaterThanOrEqual(0, $service->warmByType('media'));
    }

    public function test_get_statistics_and_count_keys()
    {
        $service = new CacheWarmingService;

        // Test non-redis failure return 0
        Config::set('cache.default', 'file');
        $stats = $service->getStatistics();
        $this->assertArrayHasKey('tags_cached', $stats);

        // Test Redis path
        Config::set('cache.default', 'redis');
        Config::set('cache.prefix', 'test');

        $redisMock = \Mockery::mock();
        $redisMock->shouldReceive('keys')->andReturn(['key1', 'key2']);
        Redis::shouldReceive('connection')->andReturn($redisMock);

        $stats2 = $service->getStatistics();
        $this->assertEquals(2, $stats2['tags_cached']);
        $this->assertEquals(2, $stats2['media_cached']);
    }
}
