<?php

namespace Modules\Cms\Tests\Unit\Services;

use Modules\Cms\Services\ThemeHooksService;
use Tests\TestCase;

class ThemeHooksServiceTest extends TestCase
{
    protected ThemeHooksService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ThemeHooksService;
    }

    public function test_add_and_do_action()
    {
        $flag = false;
        $this->service->addAction('test.action', function () use (&$flag) {
            $flag = true;
        });

        $this->service->doAction('test.action');
        $this->assertTrue($flag);
    }

    public function test_action_priority()
    {
        $output = [];
        $this->service->addAction('test.priority', function () use (&$output) {
            $output[] = 'second';
        }, 20);

        $this->service->addAction('test.priority', function () use (&$output) {
            $output[] = 'first';
        }, 10);

        $this->service->doAction('test.priority');
        $this->assertEquals(['first', 'second'], $output);
    }

    public function test_do_action_with_args()
    {
        $received = null;
        $this->service->addAction('test.args', function ($arg) use (&$received) {
            $received = $arg;
        });

        $this->service->doAction('test.args', 'hello');
        $this->assertEquals('hello', $received);
    }

    public function test_do_action_no_registered()
    {
        // Should not throw
        $this->service->doAction('non.existent');
        $this->assertFalse($this->service->hasAction('non.existent'));
    }

    public function test_add_and_apply_filter()
    {
        $this->service->addFilter('test.filter', function ($value) {
            return $value.' modified';
        });

        $result = $this->service->applyFilter('test.filter', 'original');
        $this->assertEquals('original modified', $result);
    }

    public function test_filter_priority()
    {
        $this->service->addFilter('test.priority', function ($value) {
            return $value.' 1';
        }, 20);

        $this->service->addFilter('test.priority', function ($value) {
            return $value.' 2';
        }, 10);

        $result = $this->service->applyFilter('test.priority', 'start');
        // 10 runs first (start 2), then 20 (start 2 1)
        $this->assertEquals('start 2 1', $result);
    }

    public function test_apply_filter_no_registered()
    {
        $result = $this->service->applyFilter('non.existent', 'original');
        $this->assertEquals('original', $result);
    }

    public function test_remove_action()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };

        $this->service->addAction('test.remove', $callback);
        $this->service->doAction('test.remove');
        $this->assertEquals(1, $count);

        $this->service->removeAction('test.remove', $callback);
        $this->service->doAction('test.remove');
        $this->assertEquals(1, $count);
    }

    public function test_remove_action_non_existent_hook()
    {
        $this->service->removeAction('non.existent', function () {});
        $this->assertFalse($this->service->hasAction('non.existent'));
    }

    public function test_remove_filter()
    {
        $callback = function ($val) {
            return $val.'x';
        };

        $this->service->addFilter('test.remove', $callback);
        $this->assertEquals('ax', $this->service->applyFilter('test.remove', 'a'));

        $this->service->removeFilter('test.remove', $callback);
        $this->assertEquals('a', $this->service->applyFilter('test.remove', 'a'));
    }

    public function test_remove_filter_non_existent_hook()
    {
        $this->service->removeFilter('non.existent', function () {});
        $this->assertFalse($this->service->hasFilter('non.existent'));
    }

    public function test_has_action_filter()
    {
        $this->service->addAction('act', function () {});
        $this->service->addFilter('filt', function ($v) {
            return $v;
        });

        $this->assertTrue($this->service->hasAction('act'));
        $this->assertFalse($this->service->hasAction('non'));

        $this->assertTrue($this->service->hasFilter('filt'));
        $this->assertFalse($this->service->hasFilter('non'));
    }

    public function test_get_registered_hooks()
    {
        $this->service->addAction('act1', function () {});
        $this->service->addFilter('filt1', function ($v) {
            return $v;
        });

        $hooks = $this->service->getRegisteredHooks();
        $this->assertContains('act1', $hooks['actions']);
        $this->assertContains('filt1', $hooks['filters']);
    }
}
