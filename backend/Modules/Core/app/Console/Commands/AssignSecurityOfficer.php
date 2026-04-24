<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\User;
use Spatie\Permission\Models\Role;

class AssignSecurityOfficer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:assign-officer
        {--emails= : Comma-separated email list}
        {--role=security-officer : Role name to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign security officer role to target users by email';

    public function handle(): int
    {
        $emailsRaw = $this->option('emails');
        $configuredOfficerEmails = config('security.officer_emails', '');
        $emailsInput = is_string($emailsRaw) && trim($emailsRaw) !== ''
            ? $emailsRaw
            : (is_string($configuredOfficerEmails) ? $configuredOfficerEmails : '');

        $emails = collect(explode(',', $emailsInput))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '' && filter_var($v, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            $this->warn('No valid emails provided. Use --emails or set SECURITY_OFFICER_EMAILS.');

            return self::SUCCESS;
        }

        $roleNameOpt = $this->option('role');
        $roleName = is_string($roleNameOpt) && trim($roleNameOpt) !== '' ? trim($roleNameOpt) : 'security-officer';
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        $users = User::query()->whereIn('email', $emails->all())->get(['id', 'email']);
        $assigned = 0;

        foreach ($users as $user) {
            if (! $user->hasRole($role->name)) {
                $user->assignRole($role->name);
                $assigned++;
            }
        }

        /** @var array<int, string> $foundEmails */
        $foundEmails = $users->pluck('email')->filter(static fn ($v): bool => is_string($v))->values()->all();
        /** @var array<int, string> $requestedEmails */
        $requestedEmails = $emails->all();
        $missingEmails = array_values(array_diff($requestedEmails, $foundEmails));

        $this->info("Role '{$role->name}' assignment completed.");
        $this->line('Requested: '.count($emails));
        $this->line('Found users: '.count($foundEmails));
        $this->line('Newly assigned: '.$assigned);

        if (! empty($missingEmails)) {
            $this->warn('Emails not found: '.implode(', ', $missingEmails));
        }

        return self::SUCCESS;
    }
}
