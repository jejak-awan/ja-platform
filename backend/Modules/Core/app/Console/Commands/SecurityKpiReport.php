<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\SecurityLog;

class SecurityKpiReport extends Command
{
    protected $signature = 'security:kpi-report
        {--days=30 : Lookback period in days}
        {--max-rto-seconds=600 : SLO target for recovery drill RTO}
        {--max-rpo-minutes=1440 : SLO target for recovery drill RPO}
        {--max-noise-rate=0.70 : Maximum tolerated detection noise ratio (0-1)}';

    protected $description = 'Build security KPI report from drill history and security logs';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $maxRto = max(1, (float) $this->option('max-rto-seconds'));
        $maxRpo = max(1, (float) $this->option('max-rpo-minutes'));
        $maxNoiseRate = min(1, max(0, (float) $this->option('max-noise-rate')));
        $since = now()->subDays($days);

        $drills = $this->loadDrillsSince($since);
        $drillCount = count($drills);
        $passCount = count(array_filter($drills, fn (array $d) => (bool) data_get($d, 'results.overall_pass', false)));
        $passRate = $drillCount > 0 ? $passCount / $drillCount : 0.0;
        $avgRto = $this->avg(array_map(fn (array $d) => (float) data_get($d, 'results.observed_rto_seconds', 0), $drills));
        $avgRpo = $this->avg(array_map(fn (array $d) => (float) data_get($d, 'results.observed_rpo_minutes', 0), $drills));

        // Proxy noise ratio: low-confidence signals compared to high-confidence security events.
        $signalInfo = SecurityLog::query()
            ->where('created_at', '>=', $since)
            ->whereIn('event_type', ['permission_denied', 'login_failed'])
            ->count();
        $signalCritical = SecurityLog::query()
            ->where('created_at', '>=', $since)
            ->whereIn('event_type', ['ip_blocked', 'ip_blocked_temp', 'ip_blocked_permanent', 'login_blocked'])
            ->count();
        $noiseRate = ($signalInfo + $signalCritical) > 0 ? $signalInfo / ($signalInfo + $signalCritical) : 0.0;

        $rtoOk = $drillCount > 0 && $avgRto <= $maxRto;
        $rpoOk = $drillCount > 0 && $avgRpo <= $maxRpo;
        $noiseOk = $noiseRate <= $maxNoiseRate;
        $overallOk = $rtoOk && $rpoOk && $noiseOk && $drillCount > 0;

        $report = [
            'generated_at' => now()->toISOString(),
            'period_days' => $days,
            'targets' => [
                'max_rto_seconds' => $maxRto,
                'max_rpo_minutes' => $maxRpo,
                'max_noise_rate' => $maxNoiseRate,
            ],
            'drill_kpi' => [
                'count' => $drillCount,
                'pass_count' => $passCount,
                'pass_rate' => round($passRate, 4),
                'avg_rto_seconds' => round($avgRto, 3),
                'avg_rpo_minutes' => round($avgRpo, 3),
            ],
            'detection_kpi' => [
                'info_signals' => $signalInfo,
                'critical_signals' => $signalCritical,
                'noise_rate' => round($noiseRate, 4),
            ],
            'status' => [
                'rto_ok' => $rtoOk,
                'rpo_ok' => $rpoOk,
                'noise_ok' => $noiseOk,
                'overall_ok' => $overallOk,
            ],
        ];

        $path = 'security/kpi/security-kpi-'.now()->format('Ymd_His').'.json';
        Storage::disk('local')->put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Security KPI report generated.');
        $this->line('Period: last '.$days.' days');
        $this->line('Drills: '.$drillCount.' (pass rate '.round($passRate * 100, 2).'%)');
        $this->line('Avg RTO: '.round($avgRto, 3).'s | Avg RPO: '.round($avgRpo, 3).'m');
        $this->line('Noise rate: '.round($noiseRate * 100, 2).'%');
        $this->line('Report: storage/app/'.$path);

        return $overallOk ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadDrillsSince(Carbon $since): array
    {
        $files = Storage::disk('local')->files('security/recovery-drills');
        $drills = [];

        foreach ($files as $file) {
            $raw = Storage::disk('local')->get($file);
            $json = json_decode($raw, true);
            if (! is_array($json)) {
                continue;
            }

            $completed = data_get($json, 'completed_at');
            if (! is_string($completed)) {
                continue;
            }

            try {
                $completedAt = Carbon::parse($completed);
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
     * @param  array<int, float>  $values
     */
    private function avg(array $values): float
    {
        if (count($values) === 0) {
            return 0.0;
        }

        return array_sum($values) / count($values);
    }
}
