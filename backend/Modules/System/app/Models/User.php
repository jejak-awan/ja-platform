<?php

namespace Modules\System\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Media\Models\File;
use Modules\System\Traits\CoreLogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $website
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property array|null $preferences
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, File> $media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ActivityLog> $activityLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\System\Models\Notification> $notifications
 * @property-read \Modules\System\Models\TwoFactorAuth|null $twoFactorAuth
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'srv_auth_users';

    /** @use HasFactory<\Modules\System\Database\Factories\UserFactory> */
    use CoreLogsActivity, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * Role ranks contributed by other modules.
     * @var array<string, int>
     */
    protected static array $moduleRoleRanks = [];

    /**
     * Register role ranks for modules.
     *
     * @param  array<string, int>  $ranks
     */
    public static function registerRoleRanks(array $ranks): void
    {
        static::$moduleRoleRanks = array_merge(static::$moduleRoleRanks, $ranks);
    }

    protected static function newFactory(): \Modules\System\Database\Factories\UserFactory
    {
        return \Modules\System\Database\Factories\UserFactory::new();
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'bio',
        'website',
        'location',
        'last_login_at',
        'last_login_ip',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, mixed $value): self
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        $this->preferences = $preferences;
        return $this;
    }

    public function media(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Media\Models\File::class, 'author_id');
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\System\Models\Notification::class);
    }

    public function twoFactorAuth(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\System\Models\TwoFactorAuth::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        if (! \Modules\System\Models\Setting::get('enable_2fa', false)) {
            return false;
        }
        return $this->twoFactorAuth && $this->twoFactorAuth->enabled;
    }

    public function requiresTwoFactor(): bool
    {
        if (! \Modules\System\Models\Setting::get('enable_2fa', false)) {
            return false;
        }
        $enforcement = \Modules\System\Models\Setting::get('two_factor_enforced_roles', 'no');
        if ($enforcement === 'all') {
            return true;
        }
        if ($enforcement === 'admin') {
            return $this->isAtLeastRole('admin');
        }
        return false;
    }

    public function getRoleRank(): int
    {
        $roleRanks = static::getRoleRankMap();
        $userRoles = $this->getRoleNames();
        $maxRank = 0;
        foreach ($userRoles as $role) {
            if (! is_string($role)) {
                continue;
            }
            if (isset($roleRanks[$role]) && $roleRanks[$role] > $maxRank) {
                $maxRank = $roleRanks[$role];
            }
        }
        return $maxRank;
    }

    public function isHigherThan(User $target): bool
    {
        return $this->getRoleRank() > $target->getRoleRank();
    }

    public function isAtLeastRole(string $roleName): bool
    {
        $roleRanks = self::getRoleRankMap();
        if (! isset($roleRanks[$roleName])) {
            return false;
        }
        return $this->getRoleRank() >= $roleRanks[$roleName];
    }

    public static function getRoleRankMap(): array
    {
        $coreRanks = [
            'super' => 100,
            'system-admin' => 98,
            'admin' => 95,
            'operator' => 85,
            'member' => 20,
        ];
        return array_merge($coreRanks, static::$moduleRoleRanks);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \Modules\System\Notifications\VerifyEmail);
    }
}
