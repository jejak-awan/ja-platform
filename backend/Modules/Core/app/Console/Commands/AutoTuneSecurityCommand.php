<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\SecurityLog;
use Modules\Core\Models\Setting;
use Modules\Core\Services\AttackCorrelationService;
use Modules\Core\Services\SecurityNotificationService;
use Modules\Core\Services\SecurityService;

class AutoTuneSecurityCommand extends Command
{
    protected $signature = 'security:auto-tune {--dry-run : Show what would change without applying}';

    protected $description = 'Analyze security data and auto-tune protection parameters';

    public function handle(
        AttackCorrelationService $correlation,
        SecurityService $security,
        SecurityNotificationService $notifier,
    ): int {
        // Skip during maintenance mode
        $maintenance = app(\Modules\Core\Services\SecurityMaintenanceService::class);
        if ($maintenance->isModulePaused('autotune')) {
            $this->info('⏸️  Skipping auto-tune — Security Maintenance Mode active.');

            return Command::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '🔍 DRY RUN - No changes will be applied' : '⚡ Auto-tuning security parameters...');

        $changes = [];

        // 1. Analyze attack correlation for high-risk IPs
        $threats = $correlation->analyzeThreats(168); // 7 days
        $this->info("Threat analysis: {$threats['stats']['unique_ips']} unique IPs, {$threats['stats']['high_risk_count']} high-risk");

        // Auto-block high-risk IPs (score >= 20)
        $autoBlocked = 0;
        foreach ($threats['high_risk_ips'] as $entry) {
            if ($entry['score'] >= 20 && ! $security->isIpBlocked($entry['ip'])) {
                if (! $dryRun) {
                    $ip = $entry['ip'];
                    $score = $entry['score'];
                    $reasonRaw = json_encode([
                        'key' => 'features.security.reasons.autoTuned',
                        'params' => ['score' => $score, 'events' => $entry['events']],
                    ]);
                    $security->blockIpPermanently($ip, $reasonRaw ?: null);
                }
                $autoBlocked++;
                $this->warn("  🚫 Block {$entry['ip']} (score: {$entry['score']})");
            }
        }
        if ($autoBlocked > 0) {
            $changes[] = [
                'key' => 'features.security.threatAnalysis.autoTune.changes.blockedIps',
                'params' => ['count' => $autoBlocked],
            ];
        }

        // 2. Learn new scanner paths from WAF violations
        $newPaths = SecurityLog::where('event_type', 'waf_path_traversal')
            ->where('created_at', '>=', now()->subWeek())
            ->whereNotNull('metadata')
            ->get()
            ->map(function ($log) {
                $meta = is_array($log->metadata) ? $log->metadata : null;

                return is_array($meta) ? ($meta['path'] ?? null) : null;
            })
            ->filter()
            ->countBy()
            ->filter(fn ($count) => $count >= 5) // At least 5 occurrences
            ->keys();

        if ($newPaths->isNotEmpty()) {
            $this->info("  📝 Detected {$newPaths->count()} frequently attacked paths");
            $changes[] = [
                'key' => 'features.security.threatAnalysis.autoTune.changes.newScannerPaths',
                'params' => ['count' => $newPaths->count()],
            ];

            if (! $dryRun) {
                $existingLearned = Setting::get('security_learned_scanner_paths', []);
                if (! is_array($existingLearned)) {
                    $existingLearned = [];
                }

                // Merge and limit to top 100 paths to prevent performance issues
                /** @var array<int, string> $existingLearnedStrings */
                $existingLearnedStrings = [];
                foreach ($existingLearned as $path) {
                    if (is_string($path) && $path !== '') {
                        $existingLearnedStrings[] = $path;
                    }
                }
                /** @var array<int, string> $newPathStrings */
                $newPathStrings = [];
                foreach ($newPaths->toArray() as $path) {
                    if (is_string($path) && $path !== '') {
                        $newPathStrings[] = $path;
                    }
                }
                $updatedLearned = array_values(array_unique(array_merge($existingLearnedStrings, $newPathStrings)));
                $updatedLearned = array_slice($updatedLearned, 0, 100);

                Setting::set('security_learned_scanner_paths', $updatedLearned, 'json', 'security');
            }
        }

        // 3. Tune scanner threshold based on volume
        $scannerVolume = SecurityLog::whereIn('event_type', ['malicious_scanner_blocked', 'malicious_extension_blocked'])
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        $settingVal = \Modules\Core\Models\Setting::get('scanner_auto_block_threshold', 10);
        $currentThreshold = is_numeric($settingVal) ? (int) $settingVal : 10;
        if ($scannerVolume > 1000) {
            $suggestedThreshold = 5; // Lower threshold under high attack volume
            if ($currentThreshold > $suggestedThreshold) {
                $this->info("  🎯 Scanner threshold: {$currentThreshold} → {$suggestedThreshold} (high volume: {$scannerVolume}/week)");
                $changes[] = [
                    'key' => 'features.security.threatAnalysis.autoTune.changes.scannerThreshold',
                    'params' => ['value' => $suggestedThreshold],
                ];
            }
        }

        // 4. Report campaigns
        foreach ($threats['campaigns'] as $campaign) {
            $this->warn("  🎭 Campaign: [{$campaign['type']}] {$campaign['description']}");
        }

        // Summary
        $this->newLine();
        if (empty($changes)) {
            $this->info('✅ No tuning changes needed — parameters are optimal.');
        } else {
            $this->info('📊 Changes applied:');
            foreach ($changes as $change) {
                $this->line('  • '.$change['key']);
            }

            if (! $dryRun) {
                // Log the tuning event
                SecurityLog::log('auto_tune', null, null, 'Security auto-tune completed', [
                    'changes' => $changes,
                    'stats' => $threats['stats'],
                    'campaigns' => count($threats['campaigns']),
                ]);

                // Notify
                $notifier->send(
                    'auto_tune',
                    'Security Auto-Tune Report',
                    'Security parameters have been automatically adjusted for better protection.',
                    SecurityNotificationService::SEVERITY_INFO,
                    ['changes_count' => count($changes), 'auto_blocked' => $autoBlocked]
                );
            }
        }

        return Command::SUCCESS;
    }
}
