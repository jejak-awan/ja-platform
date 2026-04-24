<?php

namespace Modules\Core\Tests\Unit\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Setting;
use Modules\Core\Services\GeoIpService;
use Tests\TestCase;

class GeoIpServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Setting::truncate();
    }

    public function test_get_location_local_ip()
    {
        $service = new GeoIpService;
        $location = $service->getLocation('127.0.0.1');

        $this->assertNull($location['country']);
        $this->assertNull($location['country_code']);
        $this->assertNull($location['city']);

        // Check another local
        $location2 = $service->getLocation('192.168.1.100');
        $this->assertNull($location2['country']);

        // Check invalid IP
        $location3 = $service->getLocation('invalid.ip');
        $this->assertNull($location3['country']);
    }

    public function test_get_location_external_ip_success()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'success',
                'country' => 'United States',
                'countryCode' => 'US',
                'city' => 'New York',
            ], 200),
        ]);

        $service = new GeoIpService;
        $location = $service->getLocation('8.8.8.8');

        $this->assertEquals('United States', $location['country']);
        $this->assertEquals('US', $location['country_code']);
        $this->assertEquals('New York', $location['city']);

        // Check caching
        $cached = Cache::get('geoip:8.8.8.8');
        $this->assertEquals('US', $cached['country_code']);

        // Test get from cache
        Http::fake([
            'ip-api.com/*' => Http::response([], 500), // Should not be hit
        ]);
        $locationCached = $service->getLocation('8.8.8.8');
        $this->assertEquals('US', $locationCached['country_code']);
    }

    public function test_get_location_external_ip_api_failure()
    {
        Http::fake([
            'ip-api.com/*' => Http::response([
                'status' => 'fail',
                'message' => 'invalid query',
            ], 200),
        ]);

        $service = new GeoIpService;
        $location = $service->getLocation('8.8.8.8');

        $this->assertNull($location['country']);
    }

    public function test_get_location_external_ip_timeout_exception()
    {
        Http::fake(function () {
            throw new \Exception('Connection timeout');
        });

        Log::shouldReceive('channel')->with('security')->andReturnSelf();
        Log::shouldReceive('warning')->once();

        $service = new GeoIpService;
        $location = $service->getLocation('8.8.8.8');

        $this->assertNull($location['country']);
    }

    public function test_is_country_allowed()
    {
        $service = new GeoIpService;

        // Empty allowed list
        Setting::set('shield_allowed_countries', []);
        $this->assertTrue($service->isCountryAllowed('8.8.8.8'));

        // Local IP bypass
        Setting::set('shield_allowed_countries', ['US', 'CA'], 'json');
        $this->assertTrue($service->isCountryAllowed('127.0.0.1')); // Returns null country, defaults to true

        // External IP allowed
        Http::fake([
            '*ip-api.com/json/8.8.8.8*' => Http::response([
                'status' => 'success',
                'country' => 'United States',
                'countryCode' => 'US',
                'city' => 'New York',
            ], 200),
            '*ip-api.com/json/9.9.9.9*' => Http::response([
                'status' => 'success',
                'country' => 'Canada',
                'countryCode' => 'CA',
                'city' => 'Toronto',
            ], 200),
            '*ip-api.com/json/1.1.1.1*' => Http::response([
                'status' => 'success',
                'country' => 'Australia',
                'countryCode' => 'AU',
                'city' => 'Sydney',
            ], 200),
        ]);

        $this->assertTrue($service->isCountryAllowed('8.8.8.8'));
        $this->assertTrue($service->isCountryAllowed('9.9.9.9'));

        // External IP blocked
        $this->assertFalse($service->isCountryAllowed('1.1.1.1'));
    }

    public function test_clear_cache()
    {
        Cache::put('geoip:8.8.8.8', ['country' => 'US'], 3600);

        $service = new GeoIpService;
        $service->clearCache('8.8.8.8');

        $this->assertNull(Cache::get('geoip:8.8.8.8'));
    }
}
