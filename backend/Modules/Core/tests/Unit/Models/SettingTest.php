<?php

namespace Modules\Core\Tests\Unit\Models;

use Modules\Core\Models\Setting;
use Tests\TestCase;

class SettingTest extends TestCase
{
    public function test_setting_set_and_get()
    {
        $setting = Setting::set('test_key', 'test_value', 'string');
        $this->assertEquals('test_value', Setting::get('test_key'));

        // Test array/json
        Setting::set('test_arr', ['a' => 1], 'json');
        $this->assertEquals(['a' => 1], Setting::get('test_arr'));

        // Test integer
        Setting::set('test_int', 42, 'integer');
        $this->assertEquals(42, Setting::get('test_int'));

        // Test integer invalid
        Setting::set('test_int_inv', 'not-a-number', 'integer');
        $this->assertEquals(0, Setting::get('test_int_inv'));

        // Test boolean
        Setting::set('test_bool', 'true', 'boolean');
        $this->assertTrue(Setting::get('test_bool'));

        // Test boolean false
        Setting::set('test_bool_f', 'false', 'boolean');
        $this->assertFalse(Setting::get('test_bool_f'));

        // Test get default
        $this->assertEquals('default_val', Setting::get('unknown_key', 'default_val'));
    }

    public function test_get_group()
    {
        Setting::set('g_key1', 'val1', 'string', 'my_group');
        Setting::set('g_key2', 123, 'integer', 'my_group');
        Setting::set('other_key', 'val', 'string', 'other_group');

        $group = Setting::getGroup('my_group');
        $this->assertCount(2, $group);
        $this->assertEquals('val1', $group['g_key1']);
        $this->assertEquals(123, $group['g_key2']);
    }

    public function test_cast_value_edge_cases()
    {
        // Expose protected static method for testing
        $method = new \ReflectionMethod(Setting::class, 'castValue');
        $method->setAccessible(true);

        $this->assertEquals(0, $method->invoke(null, 'NaN', 'integer'));
        $this->assertEquals(true, $method->invoke(null, 'yes', 'boolean'));
        $this->assertEquals(['a' => 'b'], $method->invoke(null, '{"a":"b"}', 'json'));
        $this->assertEquals(['already' => 'array'], $method->invoke(null, ['already' => 'array'], 'json'));
        $this->assertEquals('raw_text', $method->invoke(null, 'raw_text', 'text'));
        $this->assertEquals('raw_text', $method->invoke(null, 'raw_text', 'unknown'));
    }
}
