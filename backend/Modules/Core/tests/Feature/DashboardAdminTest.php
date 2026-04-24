<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Models\AnalyticsVisit;
use Modules\Core\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_admin_dashboard_returns_content_traffic_series_for_days_param(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $today = now()->startOfDay();
        AnalyticsVisit::factory()->create([
            'visited_at' => $today->copy()->addHours(3),
        ]);
        AnalyticsVisit::factory()->create([
            'visited_at' => $today->copy()->addHours(5),
        ]);

        $response = $this->getJson('/api/v1/dashboard/admin?days=7');

        TestHelpers::assertApiSuccess($response);

        $charts = $response->json('data.charts');
        $this->assertIsArray($charts);
        $this->assertArrayHasKey('contentTraffic', $charts);
        $this->assertCount(7, $charts['contentTraffic']);

        $todayKey = $today->toDateString();
        $todayRow = collect($charts['contentTraffic'])->firstWhere('period', $todayKey);
        $this->assertNotNull($todayRow);
        $this->assertSame(2, (int) $todayRow['visits']);
    }

    public function test_admin_dashboard_user_activity_respects_days_window(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        User::factory()->create(['created_at' => now()->subDays(2)->startOfDay()]);
        User::factory()->create(['created_at' => now()->subDays(40)->startOfDay()]);

        $response = $this->getJson('/api/v1/dashboard/admin?days=7');

        TestHelpers::assertApiSuccess($response);

        $activity = $response->json('data.charts.userActivity');
        $this->assertIsArray($activity);
        $this->assertNotEmpty($activity);

        $twoDaysAgo = now()->subDays(2)->toDateString();
        $row = collect($activity)->firstWhere('date', $twoDaysAgo);
        $this->assertNotNull($row);
        $this->assertGreaterThanOrEqual(1, (int) $row['count']);
    }
}
