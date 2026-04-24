<?php

declare(strict_types=1);

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\SecurityLog;
use Modules\Core\Services\SecurityNotificationService;
use Modules\Core\Services\SecurityService;
use Modules\Core\Services\ThreatIntelService;

class CheckThreatIntel extends Command
{
    protected $signature = 'security:check-threat-intel {--limit=50 : Max IPs to check}';

    protected $description = 'Check recent suspicious IPs against AbuseIPDB threat intelligence';

    public function handle(
        ThreatIntelService $threatIntel,
        SecurityService $security,
        SecurityNotificationService $notifier,
    ): int {
        // Skip during maintenance mode
        $maintenance = app(\Modules\Core\Services\SecurityMaintenanceService::class);
        if ($maintenance->isModulePaused('threatintel')) {
            $this->info('⏸️  Skipping threat intel check — Security Maintenance Mode active.');

            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        $this->info("Checking recent suspicious IPs against threat intel (limit: {$limit})...");

        // Get recent unique IPs from security logs
        $recentIps = SecurityLog::whereIn('event_type', [
            'login_failed',
            'suspicious_activity',
            'malicious_scanner_blocked',
            'malicious_extension_blocked',
            'waf_sql_injection',
            'waf_xss',
            'waf_path_traversal',
            'waf_command_injection',
            'waf_violation',
            '404',
            'suspicious_behavior',
            'suspicious_ua',
        ])
            ->where('created_at', '>=', now()->subDay())
            ->whereNotNull('ip_address')
            ->distinct()
            ->limit($limit)
            ->pluck('ip_address');

        $checked = 0;
        $blocked = 0;

        foreach ($recentIps as $ip) {
            if (! is_string($ip) || $ip === '') {
                continue;
            }

            // Skip already blocked IPs
            if ($security->isIpBlocked($ip)) {
                continue;
            }

            $result = $threatIntel->checkIp($ip);
            if ($result === null) {
                continue;
            }

            $checked++;

            if ($result['is_malicious']) {
                $reasonRaw = json_encode([
                    'key' => 'features.security.reasons.threatIntel',
                    'params' => ['ip' => $ip, 'provider' => 'AbuseIPDB'],
                ]);
                $security->blockIpPermanently($ip, is_string($reasonRaw) ? $reasonRaw : null);
                $blocked++;

                $this->warn("🚫 Blocked {$ip} (score: {$result['score']}, reports: {$result['total_reports']})");
            } else {
                $this->line("  ✓ {$ip} (score: {$result['score']})");
            }
        }

        $this->info("Checked: {$checked}, Blocked: {$blocked}");

        if ($blocked > 0) {
            $notifier->warning(
                'threat_intel_blocks',
                "Threat Intel: {$blocked} IP(s) auto-blocked",
                "{$blocked} IPs blocked based on AbuseIPDB reputation scores",
                ['checked' => $checked, 'blocked' => $blocked]
            );
        }

        return Command::SUCCESS;
    }
}
