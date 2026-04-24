<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Modules\Core\Models\DependencyVulnerability;

class SecurityAuditDependencies extends Command
{
    protected $signature = 'security:audit-dependencies';

    protected $description = 'Scan composer and npm dependencies for known vulnerabilities';

    public function handle(): int
    {
        $this->info('Running dependency security audit...');

        // Composer audit
        $this->info('Checking Composer dependencies...');
        $composerResult = Process::path(base_path())->run('composer audit --format=json --no-interaction');

        if ($composerResult->successful()) {
            $this->parseComposerAudit($composerResult->output());
        }

        // NPM audit
        $this->info('Checking NPM dependencies...');
        $npmResult = Process::path(base_path())->run('npm audit --json');

        if ($npmResult->successful()) {
            $this->parseNpmAudit($npmResult->output());
        }

        $newCount = DependencyVulnerability::where('status', 'new')->count();

        if ($newCount > 0) {
            $this->warn("Found {$newCount} new vulnerabilities!");
        } else {
            $this->info('No new vulnerabilities found.');
        }

        return Command::SUCCESS;
    }

    protected function parseComposerAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $advisories = $data['advisories'] ?? [];
            if (! is_array($advisories)) {
                return;
            }

            foreach ($advisories as $package => $alerts) {
                if (! is_array($alerts)) {
                    continue;
                }
                foreach ($alerts as $advisory) {
                    if (! is_array($advisory)) {
                        continue;
                    }

                    $packageName = isset($advisory['packageName']) && is_string($advisory['packageName']) ? $advisory['packageName'] : 'unknown';
                    $affectedVersions = isset($advisory['affectedVersions']) && is_string($advisory['affectedVersions']) ? $advisory['affectedVersions'] : 'unknown';
                    $cve = isset($advisory['cve']) && is_string($advisory['cve']) ? $advisory['cve'] : null;
                    $severityRaw = $advisory['severity'] ?? 'medium';
                    $severity = strtolower(is_string($severityRaw) ? $severityRaw : 'medium');
                    $title = isset($advisory['title']) && is_string($advisory['title']) ? $advisory['title'] : '';
                    $fixedIn = null;
                    if (isset($advisory['sources']) && is_array($advisory['sources']) && isset($advisory['sources'][0]) && is_array($advisory['sources'][0])) {
                        $remediated = $advisory['sources'][0]['remediatedVersions'] ?? null;
                        $fixedIn = is_string($remediated) ? $remediated : null;
                    }

                    DependencyVulnerability::updateOrCreate(
                        [
                            'package_name' => $packageName,
                            'version' => $affectedVersions,
                            'cve' => $cve,
                        ],
                        [
                            'severity' => $severity,
                            'fixed_in' => $fixedIn,
                            'source' => 'composer',
                            'description' => $title,
                            'status' => 'new',
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse composer audit: '.$e->getMessage());
        }
    }

    protected function parseNpmAudit(string $json): void
    {
        try {
            $data = json_decode($json, true);
            if (! is_array($data)) {
                return;
            }

            $vulnerabilities = $data['vulnerabilities'] ?? [];
            if (! is_array($vulnerabilities)) {
                return;
            }

            foreach ($vulnerabilities as $name => $vuln) {
                if (! is_array($vuln)) {
                    continue;
                }

                $version = isset($vuln['range']) && is_string($vuln['range']) ? $vuln['range'] : 'unknown';
                $severityRaw = $vuln['severity'] ?? 'medium';
                $severity = strtolower(is_string($severityRaw) ? $severityRaw : 'medium');
                $fixAvailable = (bool) ($vuln['fixAvailable'] ?? false);
                $cve = null;
                $description = '';
                if (isset($vuln['via']) && is_array($vuln['via']) && isset($vuln['via'][0]) && is_array($vuln['via'][0])) {
                    $via = $vuln['via'][0];
                    $cve = isset($via['cve']) && is_string($via['cve']) ? $via['cve'] : null;
                    $description = isset($via['title']) && is_string($via['title']) ? $via['title'] : '';
                }

                DependencyVulnerability::updateOrCreate(
                    [
                        'package_name' => is_string($name) ? $name : 'unknown',
                        'version' => $version,
                        'cve' => $cve,
                    ],
                    [
                        'severity' => $severity,
                        'fixed_in' => $fixAvailable ? 'available' : null,
                        'source' => 'npm',
                        'description' => $description,
                        'status' => 'new',
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->error('Failed to parse npm audit: '.$e->getMessage());
        }
    }
}
