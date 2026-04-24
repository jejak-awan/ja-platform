<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class SecuritySmokeCheck extends Command
{
    protected $signature = 'security:smoke-check';

    protected $description = 'Validate security route-permission matrix and least-privilege guards';

    public function handle(): int
    {
        $requiredPermissions = [
            'manage security operations',
            'manage security logs',
            'manage security ip-lists',
            'manage security integrity',
            'manage security maintenance',
        ];

        $this->info('Running security smoke checks...');

        $missingPermissions = collect($requiredPermissions)
            ->reject(fn (string $permission) => Permission::query()->where('name', $permission)->exists())
            ->values()
            ->all();

        if (! empty($missingPermissions)) {
            $this->error('Missing permissions in database: '.implode(', ', $missingPermissions));
        } else {
            $this->info('Permission seed check: OK');
        }

        $issues = [];
        $allRoutes = Route::getRoutes()->getRoutes();
        $securityRoutes = collect($allRoutes)
            ->filter(fn ($route) => str_starts_with($route->uri(), 'api/v1/admin/core/security'))
            ->values();

        foreach ($securityRoutes as $route) {
            $uri = $route->uri();
            $methods = implode(',', array_values(array_diff($route->methods(), ['HEAD'])));
            $middleware = $route->gatherMiddleware();
            $permissionTokens = $this->extractPermissionTokens($middleware);
            $domainPermissions = $this->expectedDomainPermissions($uri);

            if (in_array('manage settings', $permissionTokens, true)) {
                $issues[] = "[{$methods}] {$uri} still allows deprecated permission manage settings";
            }

            if (empty($permissionTokens)) {
                $issues[] = "[{$methods}] {$uri} has no permission middleware";
                continue;
            }

            if (! empty($domainPermissions) && count(array_intersect($permissionTokens, $domainPermissions)) === 0) {
                $issues[] = "[{$methods}] {$uri} missing expected domain permission (expected one of: ".implode(', ', $domainPermissions).')';
            }
        }

        if ($securityRoutes->isEmpty()) {
            $issues[] = 'No security routes found under api/v1/admin/core/security';
        } else {
            $this->info('Security route discovery: '.$securityRoutes->count().' routes');
        }

        if (! empty($issues)) {
            $this->newLine();
            $this->error('Smoke check failed with '.count($issues).' issue(s):');
            foreach ($issues as $issue) {
                $this->line('- '.$issue);
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Security smoke check passed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $middleware
     * @return array<int, string>
     */
    private function extractPermissionTokens(array $middleware): array
    {
        $tokens = [];

        foreach ($middleware as $entry) {
            if (! str_starts_with($entry, 'permission:')) {
                continue;
            }

            $raw = substr($entry, strlen('permission:'));
            foreach (explode('|', $raw) as $token) {
                $token = trim($token);
                if ($token !== '') {
                    $tokens[] = $token;
                }
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * @return array<int, string>
     */
    private function expectedDomainPermissions(string $uri): array
    {
        $operationFallback = ['manage security operations'];

        if (
            str_contains($uri, 'journal') ||
            str_contains($uri, 'alerts') ||
            str_contains($uri, 'health') ||
            str_contains($uri, 'csp-reports') ||
            str_contains($uri, 'slow-queries') ||
            str_contains($uri, 'test-notification') ||
            str_contains($uri, 'auto-tune/logs') ||
            str_contains($uri, 'stats')
        ) {
            return array_merge(['manage security logs'], $operationFallback);
        }

        if (
            str_contains($uri, 'block') ||
            str_contains($uri, 'whitelist')
        ) {
            return array_merge(['manage security ip-lists'], $operationFallback);
        }

        if (
            str_contains($uri, 'shield') ||
            str_contains($uri, 'threat-analysis') ||
            str_contains($uri, 'file-integrity') ||
            str_contains($uri, 'run-integrity-check') ||
            str_contains($uri, 'dependency-')
        ) {
            return array_merge(['manage security integrity'], $operationFallback);
        }

        if (
            str_contains($uri, 'maintenance') ||
            str_contains($uri, 'security/settings')
        ) {
            return array_merge(['manage security maintenance'], $operationFallback);
        }

        return $operationFallback;
    }
}
