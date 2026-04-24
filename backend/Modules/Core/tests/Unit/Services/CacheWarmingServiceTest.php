<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Modules\Cms\Models\Category;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Media;
use Modules\Cms\Models\Tag;
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

    public function test_warm_all_populated_db()
    {
        $user = \Modules\Core\Models\User::forceCreate([
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'xxx',
        ]);

        Content::forceCreate([
            'title' => 'T', 'slug' => 's', 'type' => 'post', 'status' => 'published',
            'views' => 10, 'share_count' => 0, 'edit_count' => 0, 'author_id' => $user->id,
            'is_featured' => false, 'comment_status' => 'open', 'published_at' => now(),
        ]);

        Category::forceCreate([
            'name' => 'Cat', 'slug' => 'cat', 'is_active' => true, 'sort_order' => 1,
        ]);

        Tag::forceCreate([
            'name' => 'Tag', 'slug' => 'tag',
        ]);

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
        // Counters returned from warmAll() indicate items processed
        $this->assertGreaterThan(0, $results['content']);
        $this->assertGreaterThan(0, $results['categories']);
        $this->assertGreaterThan(0, $results['tags']);
        $this->assertGreaterThan(0, $results['media']);
        $this->assertGreaterThan(0, $results['languages']);
        $this->assertGreaterThan(0, $results['statistics']);
    }

    public function test_warm_by_type()
    {
        $service = new CacheWarmingService;
        $this->assertEquals(3, $service->warmByType('content'));
        $this->assertEquals(2, $service->warmByType('categories'));
        $this->assertEquals(2, $service->warmByType('tags'));
        $this->assertEquals(0, $service->warmByType('media'));
        $this->assertEquals(1, $service->warmByType('languages'));
        $this->assertEquals(1, $service->warmByType('statistics'));
        $this->assertEquals(0, $service->warmByType('unknown'));
    }

    public function test_private_cache_content()
    {
        $service = new CacheWarmingService;
        $method = new \ReflectionMethod(CacheWarmingService::class, 'cacheContent');
        $method->setAccessible(true);

        $content = new Content;
        $content->id = 55;
        $content->slug = 'hello-world';

        // This will attempt to cache the model directly without hitting DB
        $method->invoke($service, $content);

        $this->assertTrue(Cache::has('content:55'));
        $this->assertTrue(Cache::has('content:slug:hello-world'));
    }

    public function test_private_cache_content_list()
    {
        $service = new CacheWarmingService;
        $method = new \ReflectionMethod(CacheWarmingService::class, 'cacheContentList');
        $method->setAccessible(true);

        $method->invoke($service, 'published', 1, 10);
        $this->assertTrue(Cache::has('contents:list:published:page:1:per_page:10'));

        $method->invoke($service, 'draft', 1, 10);
        $this->assertTrue(Cache::has('contents:list:draft:page:1:per_page:10'));
    }

    public function test_exceptions_in_warming()
    {
        Cache::shouldReceive('put')->andThrow(new \Exception('Mocked Cache Fail'));
        Cache::shouldReceive('remember')->andThrow(new \Exception('Mocked Cache Fail'));

        $service = new CacheWarmingService;
        $this->assertEquals(0, $service->warmCategories());
        $this->assertEquals(0, $service->warmTags());
        $this->assertEquals(0, $service->warmMedia());
        $this->assertEquals(0, $service->warmLanguages());
        $this->assertEquals(0, $service->warmStatistics());
        // For content, it fails inside pagination and returns 0 since count increment is after
        $this->assertEquals(0, $service->warmContent());
    }

    public function test_get_statistics_and_count_keys()
    {
        $service = new CacheWarmingService;

        // Test non-redis failure return 0
        Config::set('cache.default', 'file');
        $stats = $service->getStatistics();
        $this->assertEquals(0, $stats['content_cached']);

        // Test Redis path
        Config::set('cache.default', 'redis');
        Config::set('cache.prefix', 'myref');

        $redisMock = \Mockery::mock();
        $redisMock->shouldReceive('keys')->andReturn(['key1', 'key2']);
        Redis::shouldReceive('connection')->andReturn($redisMock);

        $stats2 = $service->getStatistics();
        $this->assertEquals(2, $stats2['content_cached']);
        $this->assertEquals(2, $stats2['categories_cached']);
        $this->assertEquals(2, $stats2['tags_cached']);
        $this->assertEquals(2, $stats2['media_cached']);
    }

    public function test_count_cache_keys_exception()
    {
        $service = new CacheWarmingService;
        Config::set('cache.default', 'redis');

        Redis::shouldReceive('connection')->andThrow(new \Exception('Redis Failure'));

        $stats = $service->getStatistics();
        $this->assertEquals(0, $stats['content_cached']);
    }

    public function test_warm_media_exception()
    {
        \Illuminate\Support\Facades\Schema::rename('media', 'media_bak');
        try {
            $service = new CacheWarmingService;
            $this->assertEquals(0, $service->warmMedia());
        } finally {
            \Illuminate\Support\Facades\Schema::rename('media_bak', 'media');
        }
    }
}
