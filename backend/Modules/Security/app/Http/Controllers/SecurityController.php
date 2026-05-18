<?php

declare(strict_types=1);

namespace Modules\Security\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\System\Helpers\IpHelper;
use Modules\Security\Models\SecurityLog;
use Modules\System\Services\SecurityAlertService;
use Modules\Security\Services\SecurityService;

class SecurityController extends \Modules\System\Http\Controllers\BaseApiController
{
    public function __construct(protected SecurityService $securityService, protected SecurityAlertService $alertService, protected \Modules\System\Services\AttackCorrelationService $correlationService, protected \Modules\System\Services\FileIntegrityService $fileIntegrityService, protected \Modules\Security\Services\SecurityNotificationService $notificationService, protected \Modules\System\Services\SecurityAssessmentService $assessmentService)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = SecurityLog::with('user');

        if ($request->has('event_type')) {
            $eventTypeRaw = $request->input('event_type');
            $eventType = is_string($eventTypeRaw) ? $eventTypeRaw : '';
            $query->where('event_type', $eventType);
        }

        if ($request->has('ip_address')) {
            $ipAddressRaw = $request->input('ip_address');
            $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
            $query->where('ip_address', $ipAddress);
        }

        if ($request->has('user_id')) {
            $userIdRaw = $request->input('user_id');
            $userId = is_numeric($userIdRaw) ? (int) $userIdRaw : 0;
            $query->where('user_id', $userId);
        }

        if ($request->has('date_from')) {
            $dateFromRaw = $request->input('date_from');
            $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->has('date_to')) {
            $dateToRaw = $request->input('date_to');
            $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;
        $logs = $query->latest()->paginate($perPage);

        return $this->paginated($logs, 'Security logs retrieved successfully');
    }

    public function show(SecurityLog $securityLog): \Illuminate\Http\JsonResponse
    {
        return $this->success($securityLog->load('user'), 'Security log retrieved successfully');
    }

    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;
        $stats = $this->securityService->getSecurityStats($days);

        return $this->success($stats, 'Security statistics retrieved successfully');
    }

    /**
     * Get security alerts for suspicious activity
     */
    public function alerts(): \Illuminate\Http\JsonResponse
    {
        $alerts = $this->alertService->getAlerts();
        $count = $this->alertService->getAlertCount();

        return $this->success([
            'alerts' => $alerts,
            'count' => $count,
        ], 'Security alerts retrieved successfully');
    }

    // =====================
    // Blocklist Management
    // =====================

    public function getBlocklist(): \Illuminate\Http\JsonResponse
    {
        $blocklist = $this->securityService->getBlocklist();

        return $this->success($blocklist, 'Blocklist retrieved successfully');
    }

    public function blockIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
            'permanent' => 'sometimes|boolean',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;
        $permanent = $request->boolean('permanent', true);

        // Default to permanent blocking so IP appears in blocklist tab
        // Use permanent=false to only block temporarily (cache only)
        if ($permanent) {
            $result = $this->securityService->blockIpPermanently($ipAddress, $reason);
        } else {
            $seconds = $this->securityService->blockIpTemporarily($ipAddress, $reason);
            $result = $seconds > 0;
        }

        if (! $result) {
            return $this->error('Cannot block whitelisted or protected IP address', 400);
        }

        return $this->success(null, 'IP address blocked successfully');
    }

    public function unblockIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        $this->securityService->unblockIp($ipAddress);

        return $this->success(null, 'IP address unblocked successfully');
    }

    public function bulkBlock(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $blocked = 0;
        $skipped = 0;

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        foreach ($ipAddresses as $ip) {
            if (is_string($ip) && $this->securityService->blockIpPermanently($ip, $reason)) {
                $blocked++;
            } else {
                $skipped++;
            }
        }

        $blockedStr = (string) $blocked;
        $skippedStr = (string) $skipped;

        return $this->success([
            'blocked' => $blocked,
            'skipped' => $skipped,
        ], "{$blockedStr} IP addresses blocked, {$skippedStr} skipped (whitelisted)");
    }

    public function bulkUnblock(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->unblockIp($ip);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses unblocked");
    }

    // =====================
    // Whitelist Management
    // =====================

    public function getWhitelist(): \Illuminate\Http\JsonResponse
    {
        $whitelist = $this->securityService->getWhitelist();

        return $this->success($whitelist, 'Whitelist retrieved successfully');
    }

    public function addToWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        $this->securityService->addToWhitelist($ipAddress, $reason);

        return $this->success(null, 'IP address added to whitelist');
    }

    public function removeFromWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        // Frontend sends { data: { ip_address: ... } } (likely from DELETE request structure)
        if ($request->has('data')) {
            $data = $request->input('data');
            $request->merge(is_array($data) ? $data : []);
        }

        $request->validate([
            'ip_address' => 'required|ip',
        ]);

        $ipAddressRaw = $request->input('ip_address');
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        $this->securityService->removeFromWhitelist($ipAddress);

        return $this->success(null, 'IP address removed from whitelist');
    }

    public function bulkWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
            'reason' => 'nullable|string',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];
        $reasonRaw = $request->input('reason');
        $reason = is_string($reasonRaw) ? $reasonRaw : null;

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->addToWhitelist($ip, $reason);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses added to whitelist");
    }

    public function bulkRemoveWhitelist(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ip_addresses' => 'required|array',
            'ip_addresses.*' => 'required|ip',
        ]);

        $ipAddressesRaw = $request->input('ip_addresses');
        $ipAddresses = is_array($ipAddressesRaw) ? $ipAddressesRaw : [];

        foreach ($ipAddresses as $ip) {
            if (is_string($ip)) {
                $this->securityService->removeFromWhitelist($ip);
            }
        }

        $count = count($ipAddresses);
        $countStr = (string) $count;

        return $this->success(null, "{$countStr} IP addresses removed from whitelist");
    }

    // =====================
    // IP Check & Clear
    // =====================

    public function checkIp(Request $request): \Illuminate\Http\JsonResponse
    {
        $ipAddressRaw = $request->input('ip_address', \Modules\System\Helpers\IpHelper::getClientIp($request));
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';
        $blockInfo = $this->securityService->getBlockInfo($ipAddress);

        return $this->success([
            'ip_address' => $ipAddress,
            'is_blocked' => $blockInfo['is_blocked'],
            'remaining_seconds' => $blockInfo['remaining_seconds'],
            'failed_attempts' => $blockInfo['failed_attempts'],
            'offense_count' => $blockInfo['offense_count'],
        ], 'IP status retrieved successfully');
    }

    public function clearFailedAttempts(Request $request): \Illuminate\Http\JsonResponse
    {
        $ipAddressRaw = $request->input('ip_address', \Modules\System\Helpers\IpHelper::getClientIp($request));
        $ipAddress = is_string($ipAddressRaw) ? $ipAddressRaw : '';

        // Clear all security cache for IP
        $this->securityService->clearSecurityCache($ipAddress);
        $this->securityService->unblockIp($ipAddress);

        // Also clear email-based locks if provided
        if ($request->has('email')) {
            $emailRaw = $request->input('email');
            $email = is_string($emailRaw) ? $emailRaw : '';
            $this->securityService->clearSecurityCache($email, 'email');
            $this->securityService->unlockAccount($email);
        }

        return $this->success(null, 'Security cache cleared for IP: '.$ipAddress);
    }

    public function clear(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');

            if ($retainDaysRaw) {
                $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : 0;
                $countRaw = \Modules\Security\Models\SecurityLog::where('created_at', '<', now()->subDays($retainDays))->delete();
                $count = is_numeric($countRaw) ? (int) $countRaw : 0;

                $countStr = (string) $count;
                $retainDaysStr = (string) $retainDays;

                return $this->success(null, 'Cleared '.($count).' security logs older than '.($retainDays).' days');
            }

            \Modules\Security\Models\SecurityLog::truncate();

            return $this->success(null, 'All security logs cleared successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Security logs clear error: '.$e->getMessage());

            return $this->error('Failed to clear security logs', 500);
        }
    }

    /**
     * Verify the Proof-of-Work solution for the security shield.
     */
    public function verifyConnection(Request $request): \Illuminate\Http\JsonResponse
    {
        // 1. Honeypot check: If any honeypot field is filled, it's a bot.
        $ip = IpHelper::getClientIp($request);
        if ($request->filled('_hp_email') || $request->filled('_hp_subject')) {
            $this->securityService->blockIpPermanently($ip, 'Security Shield: Honeypot trap triggered');

            return $this->error('Access Denied', 403);
        }

        $nonceRaw = $request->input('nonce');
        $nonce = is_string($nonceRaw) ? $nonceRaw : '';

        $solutionRaw = $request->input('solution');
        $solution = is_string($solutionRaw) ? $solutionRaw : '';

        $fingerprintRaw = $request->input('fingerprint');
        $fingerprint = is_string($fingerprintRaw) ? $fingerprintRaw : null;

        if (! $nonce || ! $solution) {
            return $this->error('Missing challenge details', 400);
        }

        // Track attempt for dynamic scaling
        $this->securityService->trackShieldAttempt();

        if ($this->securityService->verifyShieldSolution($nonce, $solution, $ip)) {
            \Illuminate\Support\Facades\Log::info("Shield Verification SUCCESS for IP: {$ip}");
            // Record verification in Trust Cache
            $ua = (string) $request->userAgent();
            $this->securityService->recordShieldVerification($ip, $ua, $fingerprint);

            // Create response with Trust Cookie for mobile/rotating IP support
            $token = $this->securityService->getShieldTrustCookieValue($ip, $ua);
            
            return $this->success([
                'verified' => true,
                'redirect_to' => $request->input('redirect_to') ?? session()->pull('shield_redirect_to', '/'),
            ], 'Connection verified successfully')->cookie(
                'shield_trust', 
                $token, 
                $this->securityService->getShieldTrustTtlMinutes(),
                null, 
                null, 
                true, // Secure
                true  // HttpOnly
            );
        }

        \Illuminate\Support\Facades\Log::warning("Shield Verification FAILED for IP: {$ip}. Nonce: {$nonce}, Solution: {$solution}");
        return $this->error('Challenge verification failed', 422);
    }

    /**
     * Get the Bot Shield journal of security events.
     */
    public function shieldJournal(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPageRaw = $request->input('per_page', 50);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;

        $logs = SecurityLog::whereIn('event_type', ['shield_verified', 'shield_failed', 'shield_honeypot', 'malicious_scanner_blocked', 'malicious_extension_blocked'])
            ->latest()
            ->paginate($perPage);

        // Transform to include computed details field
        $logs->getCollection()->transform(function ($log): array {
            $metadata = $log->metadata ?? [];
            $path = $metadata['path'] ?? null;

            // For scanner events, show the attacked path; for shield events, show description
            $details = $path ?: $log->description ?? '';

            return [
                'id' => $log->id,
                'event_type' => $log->event_type,
                'ip_address' => $log->ip_address,
                'details' => $details,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at,
            ];
        });

        return $this->paginated($logs, 'Shield journal retrieved successfully');
    }

    /**
     * Get statistics for the Bot Shield protection.
     */
    public function shieldStats(): \Illuminate\Http\JsonResponse
    {
        $stats = $this->securityService->getShieldStats();

        return $this->success($stats, 'Shield statistics retrieved successfully');
    }

    /**
     * Clear Bot Shield logs.
     */
    public function clearShieldLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');

            $query = SecurityLog::whereIn('event_type', ['shield_verified', 'shield_failed', 'shield_honeypot', 'malicious_scanner_blocked', 'malicious_extension_blocked']);

            if ($retainDaysRaw !== null) {
                $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : 0;
                $deletedCount = $query->where('created_at', '<', now()->subDays($retainDays))->delete();
                $count = is_numeric($deletedCount) ? (int) $deletedCount : 0;
                $message = 'Cleared '.$count.' shield logs older than '.$retainDays.' days';
            } else {
                $query->delete();
                $message = 'All shield logs cleared successfully';
            }

            return $this->success(null, $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Shield logs clear error: '.$e->getMessage());

            return $this->error('Failed to clear shield logs', 500);
        }
    }

    /**
     * Get security threat analysis (campaigns and high-risk IPs).
     */
    public function threatAnalysis(Request $request): \Illuminate\Http\JsonResponse
    {
        $hoursRaw = $request->input('hours', 24);
        $hours = is_numeric($hoursRaw) ? (int) $hoursRaw : 24;

        $analysis = $this->correlationService->analyzeThreats($hours);

        return $this->success($analysis, 'Security threat analysis retrieved successfully');
    }

    /**
     * Get file integrity status and baseline information.
     */
    public function fileIntegrityStatus(): \Illuminate\Http\JsonResponse
    {
        $verification = $this->fileIntegrityService->verify();
        $baselinesCount = \Modules\Security\Models\FileIntegrityBaseline::count();
        $lastIntegrityEvent = \Modules\Security\Models\SecurityLog::whereIn('event_type', [
            'integrity_check',
            'integrity_baseline_resynced',
            'file_integrity_violation',
        ])->latest()->first();

        $stats = [
            'total_files' => $baselinesCount,
            'violations_count' => count($verification['modified']) + count($verification['missing']) + count($verification['new']),
            'results' => $verification,
            'last_check' => $lastIntegrityEvent?->created_at,
        ];

        return $this->success($stats, 'File integrity status retrieved successfully');
    }

    /**
     * Run a manual file integrity check.
     */
    public function runIntegrityCheck(): \Illuminate\Http\JsonResponse
    {
        $verification = $this->fileIntegrityService->verify(true);

        return $this->success($verification, 'Manual file integrity check completed');
    }

    /**
     * Re-sync file integrity baseline after authorized updates.
     */
    public function resyncFileIntegrity(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'required|string|min:8|max:500',
                'clear_history' => 'sometimes|boolean',
            ]);

            $reasonRaw = $request->input('reason');
            $reason = is_string($reasonRaw) ? trim($reasonRaw) : '';
            $clearHistory = $request->boolean('clear_history', true);

            $baselineStats = $this->fileIntegrityService->generateBaseline();
            $deletedMissingBaselines = \Illuminate\Support\Facades\DB::table('sec_file_integrity_baselines')
                ->get()
                ->filter(static fn (object $row): bool => ! file_exists(base_path((string) $row->file_path)))
                ->pluck('file_path')
                ->values()
                ->all();
            if ($deletedMissingBaselines !== []) {
                \Illuminate\Support\Facades\DB::table('sec_file_integrity_baselines')
                    ->whereIn('file_path', $deletedMissingBaselines)
                    ->delete();
            }

            $verification = $this->fileIntegrityService->verify(true);
            $clearedLogs = 0;
            if ($clearHistory) {
                $clearedLogs = \Modules\Security\Models\SecurityLog::whereIn('event_type', [
                    'integrity_check',
                    'file_integrity_violation',
                ])->delete();
            }

            // Clear health cache so score updates immediately after resync.
            \Illuminate\Support\Facades\Cache::forget('security_health_assessment');

            $user = $request->user();
            \Modules\Security\Models\SecurityLog::log(
                'integrity_baseline_resynced',
                $user,
                IpHelper::getClientIp($request),
                'File integrity baseline re-synced after authorized updates',
                [
                    'reason' => $reason,
                    'baseline' => $baselineStats,
                    'clear_history' => $clearHistory,
                    'cleared_logs' => $clearedLogs,
                    'deleted_missing_baselines' => count($deletedMissingBaselines),
                    'remaining_violations' => count($verification['modified']) + count($verification['missing']) + count($verification['new']),
                ]
            );

            return $this->success([
                'baseline' => $baselineStats,
                'verification' => $verification,
                'cleared_logs' => $clearedLogs,
                'deleted_missing_baselines' => count($deletedMissingBaselines),
            ], 'File integrity baseline re-synced successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('File integrity resync error: '.$e->getMessage());

            return $this->error('Failed to re-sync file integrity baseline', 500);
        }
    }

    /**
     * Get auto-tune history logs.
     */
    public function autoTuneLogs(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPageRaw = $request->input('per_page', 20);
        $perPage = is_numeric($perPageRaw) ? (int) $perPageRaw : 20;

        $logs = \Modules\Security\Models\SecurityLog::where('event_type', 'auto_tune')
            ->latest()
            ->paginate($perPage);

        return $this->paginated($logs, 'Auto-tune logs retrieved successfully');
    }

    // ── Maintenance Mode ─────────────────────────────────────────────

    /**
     * Get security maintenance mode status.
     */
    public function maintenanceStatus(): \Illuminate\Http\JsonResponse
    {
        $service = app(\Modules\System\Services\SecurityMaintenanceService::class);

        return response()->json([
            'success' => true,
            'data' => $service->getStatus(),
        ]);
    }

    /**
     * Activate security maintenance mode.
     */
    public function maintenanceActivate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'modules' => 'array',
            'modules.*' => 'string|in:all,'.implode(',', \Modules\System\Services\SecurityMaintenanceService::MODULES),
            'duration' => 'integer|min:1|max:240',
        ]);

        $modulesRaw = $request->input('modules', ['all']);
        $modules = is_array($modulesRaw) ? array_values(array_filter($modulesRaw, is_string(...))) : ['all'];
        $durationRaw = $request->input('duration', 60);
        $duration = is_numeric($durationRaw) ? (int) $durationRaw : 60;

        $service = app(\Modules\System\Services\SecurityMaintenanceService::class);
        $result = $service->activate(
            $modules,
            $duration
        );

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
        ]);
    }

    /**
     * Deactivate security maintenance mode.
     */
    public function maintenanceDeactivate(): \Illuminate\Http\JsonResponse
    {
        $service = app(\Modules\System\Services\SecurityMaintenanceService::class);
        $result = $service->deactivate();

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
        ]);
    }

    /**
     * Get security health assessment.
     */
    public function health(): \Illuminate\Http\JsonResponse
    {
        return $this->success([
            'assessment' => $this->assessmentService->calculateScore(),
            'trend' => $this->assessmentService->getTrendData(),
        ], 'Security health assessment retrieved successfully');
    }

    /**
     * Send a test notification.
     */
    public function testNotification(): \Illuminate\Http\JsonResponse
    {
        $this->notificationService->send(
            'test_notification',
            'Sistem Keamanan Aktif 🛡️',
            'Ini adalah notifikasi uji coba dari sistem keamanan JA-Platform. Koneksi Anda berhasil dikonfigurasi!',
            \Modules\Security\Services\SecurityNotificationService::SEVERITY_INFO,
            [
                'server' => gethostname(),
                'ip' => IpHelper::getClientIp(request()),
                'status' => 'Verified',
            ]
        );

        return $this->success(null, 'Test notification sent successfully');
    }

    /**
     * Get KPI snapshot for security operations dashboard.
     */
    public function kpi(Request $request): \Illuminate\Http\JsonResponse
    {
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? max(1, (int) $daysRaw) : 30;
        $since = now()->subDays($days);

        $drills = $this->loadRecoveryDrillsSince($since);
        $drillCount = count($drills);
        $passCount = count(array_filter($drills, fn (array $d): bool => (bool) data_get($d, 'results.overall_pass', false)));
        $passRate = $drillCount > 0 ? round(($passCount / $drillCount) * 100, 2) : 0.0;
        $avgRto = $this->avg(array_map(static function (array $d): float {
            $value = data_get($d, 'results.observed_rto_seconds', 0);

            return is_numeric($value) ? (float) $value : 0.0;
        }, $drills));
        $avgRpo = $this->avg(array_map(static function (array $d): float {
            $value = data_get($d, 'results.observed_rpo_minutes', 0);

            return is_numeric($value) ? (float) $value : 0.0;
        }, $drills));

        $signalInfo = SecurityLog::query()
            ->where('created_at', '>=', $since)
            ->whereIn('event_type', ['permission_denied', 'login_failed'])
            ->count();
        $signalCritical = SecurityLog::query()
            ->where('created_at', '>=', $since)
            ->whereIn('event_type', ['ip_blocked', 'ip_blocked_temp', 'ip_blocked_permanent', 'login_blocked'])
            ->count();
        $noiseRate = ($signalInfo + $signalCritical) > 0
            ? round(($signalInfo / ($signalInfo + $signalCritical)) * 100, 2)
            : 0.0;

        $latestKpiReport = $this->latestKpiReportFileMeta();

        return $this->success([
            'period_days' => $days,
            'drills' => [
                'count' => $drillCount,
                'pass_count' => $passCount,
                'pass_rate_percent' => $passRate,
                'avg_rto_seconds' => round($avgRto, 3),
                'avg_rpo_minutes' => round($avgRpo, 3),
            ],
            'detection' => [
                'info_signals' => $signalInfo,
                'critical_signals' => $signalCritical,
                'noise_rate_percent' => $noiseRate,
            ],
            'latest_kpi_report' => $latestKpiReport,
        ], 'Security KPI snapshot retrieved successfully');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadRecoveryDrillsSince(Carbon $since): array
    {
        if (! Storage::disk('local')->exists('security/recovery-drills')) {
            return [];
        }

        $files = Storage::disk('local')->files('security/recovery-drills');
        $drills = [];

        foreach ($files as $file) {
            $raw = Storage::disk('local')->get($file);
            if (! is_string($raw)) {
                continue;
            }
            $json = json_decode($raw, true);
            if (! is_array($json)) {
                continue;
            }

            $completedAtRaw = data_get($json, 'completed_at');
            if (! is_string($completedAtRaw)) {
                continue;
            }

            try {
                $completedAt = Carbon::parse($completedAtRaw);
            } catch (\Throwable) {
                continue;
            }

            if ($completedAt->lt($since)) {
                continue;
            }

            $drills[] = $json;
        }

        return $drills;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestKpiReportFileMeta(): ?array
    {
        if (! Storage::disk('local')->exists('security/kpi')) {
            return null;
        }

        $files = Storage::disk('local')->files('security/kpi');
        if (count($files) === 0) {
            return null;
        }

        rsort($files);
        $latest = $files[0];

        return [
            'path' => $latest,
            'updated_at' => now()->toISOString(),
        ];
    }

    /**
     * @param  array<int, float>  $values
     */
    private function avg(array $values): float
    {
        if (count($values) === 0) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }

    // ── Security Settings ────────────────────────────────────────────

    /**
     * Get current security settings.
     */
    public function getSettings(): \Illuminate\Http\JsonResponse
    {
        $securityRetention = \Modules\System\Models\Setting::get('security_log_retention_days', 90);
        $activityRetention = \Modules\System\Models\Setting::get('activity_log_retention_days', 90);
        $loginRetention = \Modules\System\Models\Setting::get('login_history_retention_days', 180);
        $frequencyRaw = \Modules\System\Models\Setting::get('security_autotune_frequency', 'weekly');

        $settings = [
            'security_log_retention_days' => is_numeric($securityRetention) ? (int) $securityRetention : 90,
            'activity_log_retention_days' => is_numeric($activityRetention) ? (int) $activityRetention : 90,
            'login_history_retention_days' => is_numeric($loginRetention) ? (int) $loginRetention : 180,
            'security_autotune_frequency' => is_string($frequencyRaw) ? $frequencyRaw : 'weekly',
        ];

        return $this->success($settings, 'Security settings retrieved successfully');
    }

    /**
     * Update security settings.
     */
    public function updateSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'security_log_retention_days' => 'sometimes|integer|min:7|max:365',
            'activity_log_retention_days' => 'sometimes|integer|min:7|max:365',
            'login_history_retention_days' => 'sometimes|integer|min:7|max:365',
            'security_autotune_frequency' => 'sometimes|string|in:daily,weekly',
        ]);

        $updated = [];

        $intSettings = [
            'security_log_retention_days' => 90,
            'activity_log_retention_days' => 90,
            'login_history_retention_days' => 180,
        ];

        foreach ($intSettings as $key => $default) {
            if ($request->has($key)) {
                $raw = $request->input($key);
                $value = is_numeric($raw) ? (int) $raw : $default;
                \Modules\System\Models\Setting::set($key, $value, 'integer', 'security');
                $updated[$key] = $value;
            }
        }

        if ($request->has('security_autotune_frequency')) {
            $freqRaw = $request->input('security_autotune_frequency');
            $freq = is_string($freqRaw) ? $freqRaw : 'weekly';
            \Modules\System\Models\Setting::set('security_autotune_frequency', $freq, 'string', 'security');
            $updated['security_autotune_frequency'] = $freq;
        }

        return $this->success($updated, 'Security settings updated successfully');
    }

    /**
     * Store frontend logs/journal.
     */
    public function storeFrontendLog(Request $request): \Illuminate\Http\JsonResponse
    {
        $logData = $request->all();
        
        // Log to Laravel logs for now
        \Illuminate\Support\Facades\Log::channel('frontend')->info('Frontend Journal:', $logData);

        // Also record as a security log if it's an error
        if (($logData['level'] ?? '') === 'error' || ($logData['level'] ?? '') === 'critical') {
            SecurityLog::log(
                'frontend_error',
                $request->user(),
                IpHelper::getClientIp($request),
                $logData['message'] ?? 'Frontend Error',
                $logData
            );
        }

        return response()->json(['success' => true]);
    }
}
