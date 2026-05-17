<?php

namespace Modules\Security\Console\Commands;

use Illuminate\Console\Command;
use Modules\Security\Models\CspReport;
use Modules\System\Models\Setting;

class CleanupCspReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:cleanup-csp-reports {--days=90 : Number of days to retain}';

    /**
     * The console command description.
     */
    protected $description = 'Remove Content Security Policy (CSP) reports older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysRaw = $this->option('days') ?? Setting::get('csp_reports_retention_days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $count = CspReport::where('created_at', '<', now()->subDays($days))->delete();

        $this->info(sprintf('Deleted %d CSP report(s) older than %d days.', $count, $days));

        return 0;
    }
}
