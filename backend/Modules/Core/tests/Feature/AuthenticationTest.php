<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    // use RefreshDatabase;

    /**
     * Test successful login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $email = 'login_valid_'.uniqid().'@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        TestHelpers::assertApiSuccess($response);
        // Login now uses session-based auth, no token is returned
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'user' => [
                    'email' => $email,
                ],
            ],
        ]);
    }

    /**
     * Test login fails with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $email = 'login_inv_'.uniqid().'@example.com';
        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexistent_'.uniqid().'@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test login fails with unverified email.
     */
    public function test_user_cannot_login_with_unverified_email(): void
    {
        $email = 'unverified_'.uniqid().'@example.com';
        $user = User::factory()->unverified()->create([
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Please verify your email address before logging in.',
        ]);
    }

    /**
     * Test login validation requires email and password.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test successful user registration.
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $userData = TestHelpers::getUserData();

        $response = $this->postJson('/api/v1/register', $userData);

        TestHelpers::assertApiSuccess($response, 201);
        // Registration returns user and token
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }

    /**
     * Test registration validation requires all fields.
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/register', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test registration fails with duplicate email.
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test registration requires password confirmation.
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Test User',
            'email' => 'reg_'.uniqid().'@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test unauthenticated user cannot logout.
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated user can get their profile.
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/user');

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Test unauthenticated user cannot get profile.
     */
    public function test_unauthenticated_user_cannot_get_profile(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    /**
     * Test user can request password reset.
     */
    public function test_user_can_request_password_reset(): void
    {
        $email = 'reset_'.uniqid().'@example.com';
        $user = User::factory()->create([
            'email' => $email,
        ]);

        Notification::fake();

        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => $email,
        ]);

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test password reset requires valid email.
     */
    public function test_password_reset_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'invalid-email',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user can reset password with valid token.
     */
    public function test_user_can_reset_password_with_valid_token(): void
    {
        $email = 'reset_valid_'.uniqid().'@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('old-password'),
        ]);

        // Request password reset first to get token
        Notification::fake();
        $this->postJson('/api/v1/forgot-password', [
            'email' => $email,
        ]);

        // Get the token from the database
        $passwordReset = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        // We need to get the plain token, but it's hashed in DB
        // For testing, we'll create a token manually
        $token = \Illuminate\Support\Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => $email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        TestHelpers::assertApiSuccess($response);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));
    }

    /**
     * Test password reset fails with invalid token.
     */
    public function test_password_reset_fails_with_invalid_token(): void
    {
        $email = 'reset_'.uniqid().'@example.com';
        $user = User::factory()->create([
            'email' => $email,
        ]);

        $response = $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => $email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test password reset requires all fields.
     */
    public function test_password_reset_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/reset-password', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    /**
     * Test login is rate limited.
     */
    public function test_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response->assertStatus(429);
    }

    /**
     * Test login with missing captcha when enabled.
     */
    public function test_login_requires_captcha_when_enabled(): void
    {
        \Modules\Core\Models\Setting::set('enable_captcha', true);
        \Modules\Core\Models\Setting::set('captcha_on_login', true);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['captcha_token', 'captcha_answer']);

        // Reset
        \Modules\Core\Models\Setting::whereIn('key', ['enable_captcha', 'captcha_on_login'])->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test login fails with invalid captcha.
     */
    public function test_login_fails_with_invalid_captcha(): void
    {
        \Modules\Core\Models\Setting::set('enable_captcha', true);
        \Modules\Core\Models\Setting::set('captcha_on_login', true);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'captcha_token' => 'invalid-token',
            'captcha_answer' => 'invalid-answer',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['captcha']);

        // Reset
        \Modules\Core\Models\Setting::whereIn('key', ['enable_captcha', 'captcha_on_login'])->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test login succeeds with valid captcha.
     */
    public function test_login_succeeds_with_valid_captcha(): void
    {
        \Modules\Core\Models\Setting::set('enable_captcha', true);
        \Modules\Core\Models\Setting::set('captcha_on_login', true);

        $email = 'captcha_valid_'.uniqid().'@example.com';
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        \Illuminate\Support\Facades\Cache::put('captcha:valid-token', [
            'method' => 'slider',
            'target' => 50,
        ], 300);

        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => 'password',
            'captcha_token' => 'valid-token',
            'captcha_answer' => '50',
        ]);

        TestHelpers::assertApiSuccess($response);

        // Reset
        \Modules\Core\Models\Setting::whereIn('key', ['enable_captcha', 'captcha_on_login'])->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test register fails with invalid captcha.
     */
    public function test_register_fails_with_invalid_captcha(): void
    {
        \Modules\Core\Models\Setting::set('enable_captcha', true);
        \Modules\Core\Models\Setting::set('captcha_on_register', true);

        $userData = TestHelpers::getUserData();
        $userData['captcha_token'] = 'invalid-token';
        $userData['captcha_answer'] = 'invalid-answer';

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['captcha']);

        \Modules\Core\Models\Setting::whereIn('key', ['enable_captcha', 'captcha_on_register'])->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test login fails with 429 when IP is blocked.
     */
    public function test_login_fails_when_ip_blocked(): void
    {
        $ipAddress = '1.2.3.6';
        
        // Use service to ensure correct cache key and format
        $securityService = new \Modules\Core\Services\SecurityService();
        $securityService->blockIpTemporarily($ipAddress);

        $email = 'blocked_'.uniqid().'@example.com';
        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
            ->postJson('/api/v1/login', [
                'email' => $email,
                'password' => 'password',
            ]);

        $response->assertStatus(429);
        $response->assertJsonFragment(['success' => false]);

        $securityService->unblockIp($ipAddress);
    }

    /**
     * Test login fails with 429 when Account is locked.
     */
    public function test_login_fails_when_account_locked(): void
    {
        $email = 'locked@example.com';
        \Illuminate\Support\Facades\Cache::put('security:account_locked:'.$email, now()->addMinutes(10)->toIso8601String(), 600);

        $response = $this->postJson('/api/v1/login', [
            'email' => $email,
            'password' => 'password',
        ]);

        $response->assertStatus(429);
        $response->assertJsonFragment(['success' => false]);
        $response->assertSee('locked');

        \Illuminate\Support\Facades\Cache::forget('security:account_locked:'.$email);
    }

    /**
     * Test failed login triggers progressive blocking if max attempts reached.
     */
    public function test_failed_login_triggers_ip_block(): void
    {
        $ipAddress = '192.168.1.101';
        $email = 'trigger_block@example.com';

        \Modules\Core\Models\Setting::set('login_attempts_limit', 1);

        User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password'),
        ]);

        // Attempt 1 - Should trigger block because limit is 1
        $response = $this->postJson('/api/v1/login', ['email' => $email, 'password' => 'wrong'], ['X-Forwarded-For' => $ipAddress]);

        $response->assertStatus(429);
        $response->assertJsonFragment(['success' => false]);

        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('security:block_until:'.$ipAddress));

        \Illuminate\Support\Facades\Cache::forget('security:block_until:'.$ipAddress);
        \Illuminate\Support\Facades\Cache::forget('security:failed_attempts:ip:'.$ipAddress);
        \Illuminate\Support\Facades\Cache::forget('security:failed_attempts:email:'.$email);
        \Illuminate\Support\Facades\Cache::forget('security:account_locked:'.$email);
    }

    /**
     * Test single session enabled revokes tokens.
     */
    public function test_single_session_enabled_revokes_tokens(): void
    {
        \Modules\Core\Models\Setting::set('single_session_enabled', true);

        $user = $this->createUser();
        $user->createToken('old-token')->plainTextToken;

        $this->assertEquals(1, $user->tokens()->count());

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertEquals(0, $user->tokens()->count());

        \Modules\Core\Models\Setting::where('key', 'single_session_enabled')->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test max concurrent sessions limit.
     */
    public function test_max_concurrent_sessions_limit(): void
    {
        \Modules\Core\Models\Setting::set('max_concurrent_sessions', 2);

        $user = $this->createUser();
        $user->createToken('token-1')->plainTextToken;
        $user->createToken('token-2')->plainTextToken;

        $this->assertEquals(2, $user->tokens()->count());

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertEquals(1, $user->tokens()->count());

        \Modules\Core\Models\Setting::where('key', 'max_concurrent_sessions')->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test verify email API.
     */
    public function test_verify_email_api(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'api_verify@example.com',
        ]);

        $appKey = config('app.key');
        $token = hash_hmac('sha256', $user->getEmailForVerification(), is_string($appKey) ? $appKey : '');

        $response = $this->postJson('/api/v1/verify-email', [
            'email' => 'api_verify@example.com',
            'token' => $token,
        ]);

        TestHelpers::assertApiSuccess($response);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // Test already verified
        $response = $this->postJson('/api/v1/verify-email', [
            'email' => 'api_verify@example.com',
            'token' => $token,
        ]);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment(['message' => 'Email already verified']);
    }

    /**
     * Test verify email API fails when unknown user
     */
    public function test_verify_email_api_fails_unknown_user(): void
    {
        $response = $this->postJson('/api/v1/verify-email', [
            'email' => 'unknown_verify@example.com',
            'token' => 'dummy-token',
        ]);

        $response->assertStatus(404);
        $response->assertJsonFragment(['message' => 'User not found']);
    }

    /**
     * Test resend verification email API.
     */
    public function test_resend_verification_email_api(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'api_resend@example.com',
        ]);

        $response = $this->postJson('/api/v1/resend-verification', [
            'email' => 'api_resend@example.com',
        ]);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment(['message' => 'Verification email sent']);
    }

    /**
     * Test registration disabled
     */
    public function test_registration_disabled(): void
    {
        \Modules\Core\Models\Setting::set('enable_registration', false);

        $userData = TestHelpers::getUserData();

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Registration is currently disabled.']);

        \Modules\Core\Models\Setting::where('key', 'enable_registration')->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test login with 2FA required (missing code).
     */
    public function test_login_requires_two_factor_code(): void
    {
        \Modules\Core\Models\Setting::set('enable_2fa', true);

        $user = User::factory()->create([
            'email' => '2fa_login@example.com',
            'password' => Hash::make('password'),
        ]);

        \Modules\Core\Models\TwoFactorAuth::create([
            'user_id' => $user->id,
            'enabled' => true,
            'two_factor_secret' => encrypt('secret'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => '2fa_login@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['requires_two_factor' => true]);

        \Modules\Core\Models\Setting::where('key', 'enable_2fa')->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test login fails with invalid 2FA code.
     */
    public function test_login_fails_with_invalid_two_factor_code(): void
    {
        \Modules\Core\Models\Setting::set('enable_2fa', true);

        $user = User::factory()->create([
            'email' => '2fa_invalid@example.com',
            'password' => Hash::make('password'),
        ]);

        \Modules\Core\Models\TwoFactorAuth::create([
            'user_id' => $user->id,
            'enabled' => true,
            'two_factor_secret' => encrypt('secret'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => '2fa_invalid@example.com',
            'password' => 'password',
            'two_factor_code' => '000000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['two_factor_code']);

        \Modules\Core\Models\Setting::where('key', 'enable_2fa')->delete();
        \Illuminate\Support\Facades\Cache::tags(['settings'])->flush();
    }

    /**
     * Test logout web guard branches (session invalidation).
     */
    public function test_logout_web_guard_branches(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'web')->post('/api/v1/logout');

        $response->assertStatus(200);
    }

    /**
     * Test user profile returns unauthorized when no user.
     */
    public function test_user_profile_unauthorized_if_no_user(): void
    {
        $controller = new \Modules\Core\Http\Controllers\Api\AuthController;
        $request = \Illuminate\Http\Request::create('/api/v1/user', 'GET');
        $response = $controller->user($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test web verify email route generation.
     */
    public function test_web_verify_email_routes(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'web_verify@example.com',
        ]);

        $request = \Illuminate\Http\Request::create('/api/v1/email/verification-notification', 'POST');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $controller = new \Modules\Core\Http\Controllers\Api\AuthController;
        $response = $controller->resendVerificationEmail($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Already verified
        $user->markEmailAsVerified();
        $response = $controller->resendVerificationEmail($request);
        $this->assertEquals(422, $response->getStatusCode());

        // Test no user unauthorized
        $request->setUserResolver(function () {
            return null;
        });
        $response = $controller->resendVerificationEmail($request);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test web verify email link handling.
     */
    public function test_web_verify_email_link(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'web_verify_link@example.com',
        ]);

        $hash = sha1($user->getEmailForVerification());

        $request = \Illuminate\Http\Request::create("/api/v1/email/verify/{$user->id}/{$hash}", 'GET');
        $controller = new \Modules\Core\Http\Controllers\Api\AuthController;

        $response = $controller->verifyEmail($request, $user->id, $hash);
        $this->assertEquals(200, $response->getStatusCode());

        // Invalid hash
        $response = $controller->verifyEmail($request, $user->id, 'invalid');
        $this->assertEquals(422, $response->getStatusCode());

        // Already verified
        $user->markEmailAsVerified();
        $response = $controller->verifyEmail($request, $user->id, $hash);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test forgot password doesn't reveal user existence.
     */
    public function test_forgot_password_unknown_user_returns_success(): void
    {
        $response = $this->postJson('/api/v1/forgot-password', [
            'email' => 'unknown_forgot@example.com',
        ]);

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonFragment(['message' => 'If the email exists, a password reset link has been sent']);
    }

    /**
     * Test reset password with expired token.
     */
    public function test_reset_password_with_expired_token(): void
    {
        $email = 'expired_token@example.com';
        $user = User::factory()->create(['email' => $email]);

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make('expired-token'),
                'created_at' => now()->subHours(2),
            ]
        );

        $response = $this->postJson('/api/v1/reset-password', [
            'email' => $email,
            'token' => 'expired-token',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Reset token has expired']);
    }
}
