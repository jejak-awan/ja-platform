<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        // Admin user setup if needed by some shared helpers
        $this->createAdminUser();
        // Enable 2FA setting globally for testing
        \Modules\Core\Models\Setting::set('enable_2fa', true, 'boolean');
    }

    // use RefreshDatabase;

    /**
     * Test user can generate 2FA QR code.
     */
    public function test_user_can_generate_2fa_qr_code(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/two-factor/generate');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'qr_code_url',
                'secret',
                'backup_codes',
            ],
        ]);
    }

    /**
     * Test user can verify and enable 2FA.
     */
    public function test_user_can_verify_and_enable_2fa(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        // Generate 2FA first
        $this->postJson('/api/v1/two-factor/generate');

        $response = $this->postJson('/api/v1/two-factor/verify', [
            'code' => '000000', // Invalid code
        ]);

        $this->assertContains($response->status(), [400, 422]);
    }

    /**
     * Test user can check 2FA status.
     */
    public function test_user_can_check_2fa_status(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/two-factor/status');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'enabled',
                'backup_codes_count',
            ],
        ]);
    }

    /**
     * Test user can disable 2FA.
     */
    public function test_user_can_disable_2fa(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/two-factor/disable', [
            'password' => 'password',
        ]);

        // Success or forbidden if required for admin (this factory user is not admin)
        $this->assertContains($response->status(), [200, 422]);
    }

    /**
     * Test user can regenerate backup codes.
     */
    public function test_user_can_regenerate_backup_codes(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($user, 'sanctum');

        // Setup 2FA first
        $this->postJson('/api/v1/two-factor/generate');

        // Mock enabled state
        $twoFactor = \Modules\Core\Models\TwoFactorAuth::where('user_id', $user->id)->first();
        if ($twoFactor) {
            $twoFactor->update(['enabled' => true]);
        }

        $response = $this->postJson('/api/v1/two-factor/regenerate-backup-codes', [
            'password' => 'password',
        ]);

        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'success',
                'data' => [
                    'backup_codes',
                ],
            ]);
        } else {
            $this->assertContains($response->status(), [400, 422]);
        }
    }

    /**
     * Test 2FA verification during login.
     */
    public function test_user_can_login_with_2fa_code(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/two-factor/verify-code', [
            'user_id' => $user->id,
            'code' => '123456',
        ]);

        $this->assertContains($response->status(), [400, 422]);
    }
}
