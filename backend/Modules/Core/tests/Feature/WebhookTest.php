<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Core\Models\User;
use Modules\Core\Models\Webhook;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();
    }

    // use RefreshDatabase;

    protected User $admin;

    /**
     * Test admin can list all webhooks.
     */
    public function test_admin_can_list_webhooks(): void
    {
        Webhook::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/webhooks');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'url',
                    'event',
                    'is_active',
                ],
            ],
        ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test admin can filter webhooks by event.
     */
    public function test_admin_can_filter_webhooks_by_event(): void
    {
        Webhook::factory()->count(3)->create(['event' => 'content.created']);
        Webhook::factory()->count(2)->create(['event' => 'content.updated']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/webhooks?event=content.created');

        TestHelpers::assertApiSuccess($response);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test admin can filter webhooks by active status.
     */
    public function test_admin_can_filter_webhooks_by_active_status(): void
    {
        Webhook::factory()->count(3)->create(['is_active' => true]);
        Webhook::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/webhooks?is_active=1');

        TestHelpers::assertApiSuccess($response);
        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Test admin can create a webhook.
     */
    public function test_admin_can_create_webhook(): void
    {
        $webhookData = [
            'name' => 'Content Created Webhook',
            'url' => 'https://example.com/webhook',
            'event' => 'content.created',
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer token123',
            ],
            'payload_template' => [
                'event' => '{{event}}',
                'data' => '{{data}}',
            ],
            'timeout' => 30,
            'max_retries' => 3,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/webhooks', $webhookData);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonFragment([
            'name' => 'Content Created Webhook',
            'url' => 'https://example.com/webhook',
            'event' => 'content.created',
        ]);

        $this->assertDatabaseHas('webhooks', [
            'name' => 'Content Created Webhook',
            'url' => 'https://example.com/webhook',
            'event' => 'content.created',
        ]);
    }

    /**
     * Test webhook creation requires name, url, and event.
     */
    public function test_webhook_creation_requires_required_fields(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/webhooks', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name', 'url', 'event']);
    }

    /**
     * Test webhook URL must be valid.
     */
    public function test_webhook_url_must_be_valid(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/core/webhooks', [
                'name' => 'Test Webhook',
                'url' => 'invalid-url',
                'event' => 'content.created',
            ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['url']);
    }

    /**
     * Test admin can get a single webhook.
     */
    public function test_admin_can_get_single_webhook(): void
    {
        $webhook = Webhook::factory()->create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/core/webhooks/{$webhook->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
        ]);
    }

    /**
     * Test admin can update a webhook.
     */
    public function test_admin_can_update_webhook(): void
    {
        $webhook = Webhook::factory()->create();

        $updateData = [
            'name' => 'Updated Webhook',
            'url' => 'https://updated.com/webhook',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/core/webhooks/{$webhook->id}", $updateData);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment([
            'name' => 'Updated Webhook',
            'url' => 'https://updated.com/webhook',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('webhooks', [
            'id' => $webhook->id,
            'name' => 'Updated Webhook',
            'url' => 'https://updated.com/webhook',
        ]);
    }

    /**
     * Test admin can delete a webhook.
     */
    public function test_admin_can_delete_webhook(): void
    {
        $webhook = Webhook::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/core/webhooks/{$webhook->id}");

        TestHelpers::assertApiSuccess($response);

        $this->assertDatabaseMissing('webhooks', [
            'id' => $webhook->id,
        ]);
    }

    /**
     * Test admin can test webhook delivery.
     */
    public function test_admin_can_test_webhook_delivery(): void
    {
        Http::fake([
            'example.com/*' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/core/webhooks/{$webhook->id}/test");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'success' => true,
            ],
        ]);
    }

    /**
     * Test admin can get webhook statistics.
     */
    public function test_admin_can_get_webhook_statistics(): void
    {
        Webhook::factory()->count(5)->create(['is_active' => true]);
        Webhook::factory()->count(3)->create(['is_active' => false]);

        Webhook::factory()->create([
            'is_active' => false,
            'success_count' => 10,
            'failure_count' => 2,
            'last_triggered_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/core/webhooks/statistics');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'total',
                'active',
                'total_success',
                'total_failures',
                'recent_webhooks',
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals(9, $data['total']);
        $this->assertEquals(5, $data['active']);
    }

    /**
     * Test webhook trigger increments success count.
     */
    public function test_webhook_trigger_increments_success_count(): void
    {
        Http::fake([
            'example.com/*' => Http::response(['success' => true], 200),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'success_count' => 0,
        ]);

        $result = $webhook->trigger(['test' => 'data']);

        $this->assertTrue($result);
        $this->assertEquals(1, $webhook->fresh()->success_count);
        $this->assertNotNull($webhook->fresh()->last_triggered_at);
    }

    /**
     * Test webhook trigger increments failure count on error.
     */
    public function test_webhook_trigger_increments_failure_count_on_error(): void
    {
        Http::fake([
            'example.com/*' => Http::response([], 500),
        ]);

        $webhook = Webhook::factory()->create([
            'url' => 'https://example.com/webhook',
            'is_active' => true,
            'failure_count' => 0,
        ]);

        $result = $webhook->trigger(['test' => 'data']);

        $this->assertFalse($result);
        $this->assertEquals(1, $webhook->fresh()->failure_count);
    }

    /**
     * Test inactive webhook does not trigger.
     */
    public function test_inactive_webhook_does_not_trigger(): void
    {
        Http::fake();

        $webhook = Webhook::factory()->create([
            'is_active' => false,
        ]);

        $result = $webhook->trigger(['test' => 'data']);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    /**
     * Test unauthenticated user cannot access webhooks.
     */
    public function test_unauthenticated_user_cannot_access_webhooks(): void
    {
        $response = $this->getJson('/api/v1/admin/core/webhooks');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/admin/core/webhooks', []);
        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot access webhooks.
     */
    public function test_user_without_permission_cannot_access_webhooks(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/core/webhooks');

        $response->assertStatus(403);
    }
}
