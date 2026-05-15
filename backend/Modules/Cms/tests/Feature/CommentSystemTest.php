<?php

namespace Modules\Cms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Models\Content;
use Modules\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class CommentSystemTest extends TestCase
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test user can post a comment on published content.
     */
    public function test_user_can_post_comment_on_published_content(): void
    {
        // Mock CaptchaService to always return true
        $this->mock(\Modules\System\Services\CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(true);
        });

        $user = $this->createUser();
        $content = Content::factory()->published()->create();

        // Controller expects 'name' and 'email' for guest comments, plus captcha_token/captcha_input
        $response = $this->postJson("/api/v1/ja/contents/{$content->id}/comments", [
            'body' => 'This is a test comment',
            'name' => $user->name,
            'email' => $user->email,
            'captcha_token' => 'test-token', // Will be mocked
            'captcha_input' => 'test-answer',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'body',
                'status',
            ],
        ]);

        $this->assertDatabaseHas('comments', [
            'content_id' => $content->id,
            'body' => 'This is a test comment',
        ]);
    }

    /**
     * Test comment requires body.
     */
    public function test_comment_requires_body(): void
    {
        $this->withoutMiddleware('throttle');
        $content = Content::factory()->published()->create();

        $response = $this->postJson("/api/v1/ja/contents/{$content->id}/comments", [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['body']);
    }

    /**
     * Test user can view approved comments.
     */
    public function test_user_can_view_approved_comments(): void
    {
        $content = Content::factory()->published()->create();

        // Create approved comments
        $content->comments()->createMany([
            [
                'body' => 'First comment',
                'name' => 'User 1',
                'email' => 'user1@example.com',
                'status' => 'approved',
            ],
            [
                'body' => 'Second comment',
                'name' => 'User 2',
                'email' => 'user2@example.com',
                'status' => 'approved',
            ],
        ]);

        $response = $this->getJson("/api/v1/ja/contents/{$content->id}/comments");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
    }

    /**
     * Test pending comments are not visible to public.
     */
    public function test_pending_comments_not_visible_to_public(): void
    {
        $content = Content::factory()->published()->create();

        $content->comments()->create([
            'body' => 'Pending comment',
            'name' => 'User',
            'email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/ja/contents/{$content->id}/comments");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Test admin can approve comment.
     */
    public function test_admin_can_approve_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Pending comment',
            'name' => 'User',
            'email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/v1/manage/cms/comments/{$comment->id}/approve");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'approved',
        ]);
    }

    /**
     * Test admin can reject comment.
     */
    public function test_admin_can_reject_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Pending comment',
            'name' => 'User',
            'email' => 'user@example.com',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/v1/manage/cms/comments/{$comment->id}/reject");

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test admin can delete comment.
     */
    public function test_admin_can_delete_comment(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $content = Content::factory()->published()->create();
        $comment = $content->comments()->create([
            'body' => 'Test comment',
            'name' => 'User',
            'email' => 'user@example.com',
            'status' => 'approved',
        ]);

        $response = $this->deleteJson("/api/v1/manage/cms/comments/{$comment->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    /**
     * Test comment is rate limited.
     */
    public function test_comment_submission_is_rate_limited(): void
    {
        $content = Content::factory()->published()->create();

        // Make 11 requests (limit is 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson("/api/v1/ja/contents/{$content->id}/comments", [
                'body' => "Comment {$i}",
                'name' => 'Test User',
                'email' => "test{$i}@example.com", // Unique email to ensure guest ID doesn't conflict?
                // Throttle usually defaults to IP.
            ]);
        }

        // The last request should be rate limited
        $response->assertStatus(429);
    }
}
