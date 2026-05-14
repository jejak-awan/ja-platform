<?php

namespace Modules\Core\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\ActivityLog;
use Modules\Core\Models\User;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_updated_observer()
    {
        $user = User::factory()->create(['name' => 'Original Name']);
        $user->name = 'New Name';
        $user->save();

        $this->assertDatabaseHas('core_activity_logs', [
            'action' => 'updated',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_user_relationship()
    {
        $user = User::factory()->create();
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'test',
        ]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_model_relationship()
    {
        $user = User::factory()->create();
        $log = ActivityLog::create([
            'action' => 'test',
            'model_type' => get_class($user),
            'model_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $log->model);
        $this->assertEquals($user->id, $log->model->id);
    }

    public function test_generate_description_paths()
    {
        $user = User::factory()->create();

        // This will hit the 'viewed' path explicitly
        $logViewed = ActivityLog::log('viewed', $user);
        $this->assertEquals('Viewed User', $logViewed->description);

        $logPublished = ActivityLog::log('published', $user);
        $this->assertEquals('Published User', $logPublished->description);

        $logUnpublished = ActivityLog::log('unpublished', $user);
        $this->assertEquals('Unpublished User', $logUnpublished->description);

        $logUpdated = ActivityLog::log('updated', $user);
        $this->assertEquals('Updated User', $logUpdated->description);

        $logDeleted = ActivityLog::log('deleted', $user);
        $this->assertEquals('Deleted User', $logDeleted->description);

        // This hits the default path
        $logDefault = ActivityLog::log('magic_action', $user);
        $this->assertEquals('Magic_action User', $logDefault->description);

        // This hits the $model = null default
        $logNoModel = ActivityLog::log('mysterious_action');
        $this->assertEquals('Mysterious_action item', $logNoModel->description);
    }

    public function test_log_with_changes()
    {
        $log = ActivityLog::log('updated', null, ['old' => 'a', 'new' => 'b']);
        $this->assertEquals(['old' => 'a', 'new' => 'b'], $log->changes);
    }

    public function test_log_without_request_context()
    {
        // Mock request without User-Agent
        request()->headers->remove('User-Agent');

        $log = ActivityLog::log('test_no_request');
        $this->assertEquals('127.0.0.1', $log->ip_address);
        $this->assertNull($log->user_agent);
    }
}
