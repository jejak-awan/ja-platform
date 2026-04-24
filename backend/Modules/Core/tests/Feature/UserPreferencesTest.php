<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\User;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    // use RefreshDatabase;

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_get_preferences_when_authenticated(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/profile/preferences');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'dark_mode',
                    'locale',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'dark_mode' => 'system', // default
                    'locale' => 'en', // default
                ],
            ]);
    }

    public function test_preferences_rejected_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/v1/profile/preferences');

        $response->assertUnauthorized();
    }

    public function test_can_update_dark_mode_preference(): void
    {
        $response = $this->withToken($this->token)
            ->putJson('/api/v1/profile/preferences', [
                'dark_mode' => 'dark',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'dark_mode' => 'dark',
                ],
            ]);

        // Verify persisted in database
        $this->user->refresh();
        $this->assertEquals('dark', $this->user->getPreference('dark_mode'));
    }

    public function test_can_update_locale_preference(): void
    {
        $response = $this->withToken($this->token)
            ->putJson('/api/v1/profile/preferences', [
                'locale' => 'id',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'locale' => 'id',
                ],
            ]);

        // Verify persisted in database
        $this->user->refresh();
        $this->assertEquals('id', $this->user->getPreference('locale'));
    }

    public function test_can_update_multiple_preferences(): void
    {
        $response = $this->withToken($this->token)
            ->putJson('/api/v1/profile/preferences', [
                'dark_mode' => 'light',
                'locale' => 'id',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'dark_mode' => 'light',
                    'locale' => 'id',
                ],
            ]);

        // Verify persisted in database
        $this->user->refresh();
        $this->assertEquals('light', $this->user->getPreference('dark_mode'));
        $this->assertEquals('id', $this->user->getPreference('locale'));
    }

    public function test_invalid_dark_mode_value_rejected(): void
    {
        $response = $this->withToken($this->token)
            ->putJson('/api/v1/profile/preferences', [
                'dark_mode' => 'invalid_value',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['dark_mode']);
    }

    public function test_preferences_persist_across_requests(): void
    {
        // Set preferences
        $this->withToken($this->token)
            ->putJson('/api/v1/profile/preferences', [
                'dark_mode' => 'dark',
                'locale' => 'id',
            ]);

        // Get preferences in new request
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/profile/preferences');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'dark_mode' => 'dark',
                    'locale' => 'id',
                ],
            ]);
    }
}
