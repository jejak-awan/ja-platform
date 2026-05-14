<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\AnalyticsEvent;
use Modules\Core\Models\AnalyticsSession;
use Modules\Core\Models\AnalyticsVisit;
use Modules\Core\Models\Setting;

class CleanupAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:cleanup 
        {--days=90 : Number of days to retain analytics data}
        {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old analytics data to manage database size';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $retentionDays = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("Analytics Cleanup - Retention: {$retentionDays} days");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be deleted');
        }

        $this->newLine();

        // Count records to delete
        $visitsCount = AnalyticsVisit::where('visited_at', '<', $cutoffDate)->count();
        $eventsCount = AnalyticsEvent::where('occurred_at', '<', $cutoffDate)->count();
        $sessionsCount = AnalyticsSession::where('started_at', '<', $cutoffDate)->count();

        $this->table(
            ['Table', 'Records to Delete'],
            [
                ['analytics_visits', $visitsCount],
                ['analytics_events', $eventsCount],
                ['analytics_sessions', $sessionsCount],
            ]
        );

        $totalRecords = $visitsCount + $eventsCount + $sessionsCount;

        if ($totalRecords === 0) {
            $this->info('No records to delete.');

            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Would delete {$totalRecords} total records.");

            return Command::SUCCESS;
        }

        if (! $this->confirm("Delete {$totalRecords} records older than {$retentionDays} days?", true)) {
            $this->info('Cleanup cancelled.');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('Deleting records...');

        $deleted = [
            'visits' => 0,
            'events' => 0,
            'sessions' => 0,
        ];

        // Delete visits
        $daysVisitsRaw = Setting::get('analytics_retention_days', 90);
        /** @var int $daysVisits */
        $daysVisits = is_numeric($daysVisitsRaw) ? (int) $daysVisitsRaw : 90;
        /** @var int $countVisits */
        $countVisits = AnalyticsVisit::where('visited_at', '<', now()->subDays($daysVisits))->delete();
        $this->info(sprintf('Deleted %d old visits.', $countVisits));

        // Delete events
        $daysEventsRaw = Setting::get('analytics_event_retention_days', 60);
        /** @var int $daysEvents */
        $daysEvents = is_numeric($daysEventsRaw) ? (int) $daysEventsRaw : 60;
        /** @var int $countEvents */
        $countEvents = AnalyticsEvent::where('occurred_at', '<', now()->subDays($daysEvents))->delete();
        $this->info(sprintf('Deleted %d old events.', $countEvents));

        // Delete sessions
        $daysSessionsRaw = Setting::get('analytics_visitor_retention_days', 30);
        /** @var int $daysSessions */
        $daysSessions = is_numeric($daysSessionsRaw) ? (int) $daysSessionsRaw : 30;
        /** @var int $countSessions */
        $countSessions = AnalyticsSession::where('started_at', '<', now()->subDays($daysSessions))->delete();
        $this->info(sprintf('Deleted %d old sessions.', $countSessions));

        $this->newLine();
        $totalDeleted = $countVisits + $countEvents + $countSessions;
        $this->info(sprintf('Cleanup complete! Deleted %d total records.', $totalDeleted));

        Log::info('Analytics cleanup completed', [
            'retention_days' => $retentionDays,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'deleted' => $deleted,
        ]);

        return Command::SUCCESS;
    }
}
