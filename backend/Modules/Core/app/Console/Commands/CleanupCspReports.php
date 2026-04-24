<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\CspReport;
use Modules\Core\Models\Setting;

class CleanupCspReports extends Command
{
    protected $signature = 'logs:cleanup-csp-reports {--days=90 : Number of days to retain}';

    protected $description = 'Remove CSP reports older than specified days';

    public function handle(): int
    {
        $daysRaw = $this->option('days') ?? Setting::get('csp_reports_retention_days', 30);
        /** @var int $days */
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;
        /** @var int $count */
        $count = CspReport::where('created_at', '<', now()->subDays($days))->delete();

        $this->info(sprintf('Deleted %d CSP report(s) older than %d days.', $count, $days));

        return Command::SUCCESS;
    }
}
