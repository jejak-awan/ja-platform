<?php

namespace Modules\Core\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Core\Models\Webhook;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creation()
    {
        $factory = Webhook::factory();
        $this->assertInstanceOf(\Modules\Core\Database\Factories\WebhookFactory::class, $factory);
    }

    public function test_trigger_success()
    {
        \Illuminate\Support\Carbon::setTestNow('2026-01-01 10:00:00');
        Http::fake();

        $webhook = Webhook::factory()->create([
            'url' => 'http://example.com/webhook',
            'method' => 'POST',
            'is_active' => true,
            'headers' => ['X-Secret' => 'abc'],
        ]);

        $result = $webhook->trigger(['id' => 1]);

        $this->assertTrue($result);
        $this->assertEquals(1, $webhook->fresh()->success_count);
        $this->assertNotNull($webhook->fresh()->last_triggered_at);

        Http::assertSent(function ($request) use ($webhook) {
            return $request->url() === $webhook->url &&
                   $request->method() === 'POST' &&
                   $request->header('X-Secret')[0] === 'abc';
        });

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_trigger_inactive()
    {
        $webhook = Webhook::factory()->create(['is_active' => false]);
        $this->assertFalse($webhook->trigger([]));
    }

    public function test_trigger_failure_with_retries()
    {
        Http::fake([
            'example.com/*' => Http::response(['error' => 'server error'], 500),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'http://example.com/fail',
            'max_retries' => 3,
            'retry_count' => 0,
            'is_active' => true,
        ]);

        $result = $webhook->trigger([]);
        $this->assertFalse($result);

        $webhook = $webhook->fresh();
        // If this fails, we want to know what it IS
        $this->assertEquals(1, $webhook->failure_count, "Failure count was {$webhook->failure_count} instead of 1");
        $this->assertEquals(1, $webhook->retry_count, "Retry count was {$webhook->retry_count} instead of 1");

        // Trigger again to test max retries limit
        $webhook->update(['retry_count' => 3]);
        $webhook->trigger([]);
        $this->assertEquals(2, $webhook->fresh()->failure_count);
        $this->assertEquals(3, $webhook->fresh()->retry_count); // Not incremented further
    }

    public function test_trigger_exception()
    {
        Http::fake(function () {
            throw new \Exception('Connection Error');
        });

        $webhook = Webhook::factory()->create([
            'url' => 'http://example.com/exception',
            'max_retries' => 1,
            'retry_count' => 0,
            'is_active' => true,
        ]);

        $result = $webhook->trigger([]);
        $this->assertFalse($result);
        $this->assertEquals(1, $webhook->fresh()->failure_count);
        $this->assertEquals(1, $webhook->fresh()->retry_count);
    }

    public function test_build_payload_with_template()
    {
        $webhook = Webhook::factory()->create([
            'payload_template' => [
                'full_data' => '{data}',
                'fixed' => 'value',
                'raw_obj' => ['nested' => 1],
            ],
        ]);

        // We use Reflection to test buildPayload directly or trigger it
        $method = new \ReflectionMethod(Webhook::class, 'buildPayload');
        $method->setAccessible(true);

        $data = ['foo' => 'bar'];
        $payload = $method->invoke($webhook, $data);

        $this->assertEquals(json_encode($data), $payload['full_data']);
        $this->assertEquals('value', $payload['fixed']);
        $this->assertEquals(['nested' => 1], $payload['raw_obj']);
    }

    public function test_build_payload_empty_template()
    {
        $webhook = Webhook::factory()->create(['payload_template' => []]);
        $method = new \ReflectionMethod(Webhook::class, 'buildPayload');
        $method->setAccessible(true);

        $data = ['a' => 'b'];
        $payload = $method->invoke($webhook, $data);
        $this->assertEquals('b', $payload['data']['a']);
        $this->assertEquals($webhook->event, $payload['event']);
    }

    public function test_resolve_template_non_string()
    {
        $webhook = new Webhook;
        $method = new \ReflectionMethod(Webhook::class, 'resolveTemplate');
        $method->setAccessible(true);

        $this->assertEquals(123, $method->invoke($webhook, 123, []));
    }

    public function test_trigger_for_event_static()
    {
        Http::fake();

        Webhook::factory()->create(['event' => 'user.created', 'is_active' => true]);
        Webhook::factory()->create(['event' => 'user.created', 'is_active' => false]);
        Webhook::factory()->create(['event' => 'other.event', 'is_active' => true]);

        Webhook::triggerForEvent('user.created', ['id' => 123]);

        Http::assertSentCount(1);
    }
}
