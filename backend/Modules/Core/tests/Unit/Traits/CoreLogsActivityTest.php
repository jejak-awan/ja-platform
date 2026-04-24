<?php

namespace Modules\Core\Tests\Unit\Traits;

use Modules\Core\Models\ActivityLog;
use Modules\Core\Models\User;
use Tests\TestCase;

class CoreLogsActivityTest extends TestCase
{
    public function test_trait_logs_created_updated_deleted_events()
    {
        // 1. Test Created
        $user = User::factory()->create(['name' => 'Original Name']);

        $createLog = ActivityLog::where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($createLog);
        $this->assertEquals('Original Name', $createLog->changes['attributes']['name']);

        // 2. Test Updated (with changes)
        $user->name = 'New Name';
        $user->save();

        $updateLog = ActivityLog::where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($updateLog);
        $this->assertEquals('Original Name', $updateLog->changes['old']['name']);
        $this->assertEquals('New Name', $updateLog->changes['new']['name']);

        // 3. Test Updated (without changes should not log)
        $logCountBefore = ActivityLog::count();
        $user->forceFill(['updated_at' => now()->addSecond()])->save();
        $this->assertEquals($logCountBefore, ActivityLog::count());

        // 4. Test Deleted
        $user->delete();

        $deleteLog = ActivityLog::where('model_id', $user->id)
            ->where('model_type', get_class($user))
            ->where('action', 'deleted')
            ->first();

        $this->assertNotNull($deleteLog);
    }
}
