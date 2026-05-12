<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Modules\Core\Services\CacheService;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_clear_all()
    {
        Cache::spy();
        
        $service = new CacheService;
        $service->clearAll();

        Cache::shouldHaveReceived('flush')->once();
    }


    public function test_clear_tag_caches()
    {
        Cache::shouldReceive('forget')->with('tags_all')->once();
        Cache::shouldReceive('forget')->with('tags_statistics')->once();

        $service = new CacheService;
        $service->clearTagCaches();
    }

    public function test_clear_media_caches()
    {
        Cache::shouldReceive('forget')->with('media_list')->once();

        $service = new CacheService;
        $service->clearMediaCaches();
    }

    public function test_clear_user_caches()
    {
        // Without ID, it shouldn't forget anything
        Cache::shouldReceive('forget')->never();

        $service = new CacheService;
        $service->clearUserCaches();

        // With ID
        Cache::shouldReceive('forget')->with('user_1')->once();
        Cache::shouldReceive('forget')->with('user_activity_1')->once();

        $service->clearUserCaches(1);
    }


    public function test_warm_up()
    {
        $service = new CacheService;
        $service->warmUp();

        $this->assertTrue(true);
    }

    public function test_get_key()
    {
        $service = new CacheService;
        $this->assertEquals('prefix:key', $service->getKey('prefix', 'key'));
    }

    public function test_is_redis_available_false()
    {
        $service = new CacheService;
        Config::set('cache.default', 'file');
        $this->assertFalse($service->isRedisAvailable());
    }

    public function test_is_redis_available_exception()
    {
        $service = new CacheService;
        Config::set('cache.default', 'redis');
        Redis::shouldReceive('connection->ping')->once()->andThrow(new \Exception('Redis down'));
        $this->assertFalse($service->isRedisAvailable());
    }

    public function test_is_redis_available_true()
    {
        $service = new CacheService;
        Config::set('cache.default', 'redis');
        Redis::shouldReceive('connection->ping')->once()->andReturn(true);
        $this->assertTrue($service->isRedisAvailable());
    }

    public function test_invalidate_by_pattern_not_available()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->once()->andReturn(false);
        $this->assertEquals(0, $service->invalidateByPattern('test*'));
    }

    public function test_invalidate_by_pattern_exception()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->once()->andReturn(true);
        Config::set('cache.prefix', 'pref');

        // Force connection() to throw to hit line 143/145
        Redis::shouldReceive('connection')->once()->andThrow(new \Exception('Fail'));
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->once();

        $this->assertEquals(0, $service->invalidateByPattern('test*'));
    }

    public function test_invalidate_by_pattern_empty()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->once()->andReturn(true);
        Config::set('cache.prefix', 'pref');
        Redis::shouldReceive('connection->keys')->with('pref:test*')->once()->andReturn([]);
        $this->assertEquals(0, $service->invalidateByPattern('test*'));
    }

    public function test_invalidate_by_pattern_success()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->once()->andReturn(true);
        Config::set('cache.prefix', 'pref');
        $redisMock = \Mockery::mock();
        $redisMock->shouldReceive('keys')->with('pref:test*')->once()->andReturn(['key1', 'key2']);
        $redisMock->shouldReceive('del')->with(['key1', 'key2'])->once()->andReturn(2);
        Redis::shouldReceive('connection')->once()->andReturn($redisMock);

        $this->assertEquals(2, $service->invalidateByPattern('test*'));
    }

    public function test_get_preferred_store()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();

        $service->shouldReceive('isRedisAvailable')->andReturn(true);
        $this->assertEquals('redis', $service->getPreferredStore());

        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->andReturn(false);
        $this->assertEquals('file', $service->getPreferredStore());
    }

    public function test_get_cache_driver_info()
    {
        Config::set('cache.default', 'array');
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->andReturn(false);

        $info = $service->getCacheDriverInfo();
        $this->assertEquals('array', $info['configured_driver']);
        $this->assertFalse($info['redis_available']);
        $this->assertEquals('file', $info['effective_driver']);
        $this->assertStringContainsString('consider enabling Redis', $info['recommendation']);

        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('isRedisAvailable')->andReturn(true);

        $info2 = $service->getCacheDriverInfo();
        $this->assertTrue($info2['redis_available']);
        $this->assertEquals('redis', $info2['effective_driver']);
        $this->assertStringContainsString('Redis is active', $info2['recommendation']);
    }

    public function test_smart_remember()
    {
        $service = \Mockery::mock(\Modules\Core\Services\CacheService::class)->makePartial();
        $service->shouldReceive('getPreferredStore')->andReturn('array');

        $result = $service->smartRemember('test_key', 60, function () {
            return 'value';
        });

        $this->assertEquals('value', $result);
        $this->assertEquals('value', Cache::store('array')->get('test_key'));
    }
}
