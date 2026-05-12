<?php

namespace Modules\Core\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Core\Models\Media;
use Modules\Core\Traits\CoreLogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $avatar
 * @property string|null $phone
 * @property string|null $bio
 * @property string|null $website
 * @property string|null $location
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property array<string, mixed>|null $preferences
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Modules\Core\Models\TwoFactorAuth|null $twoFactorAuth
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\Media> $media
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\ActivityLog> $activityLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\Notification> $notifications
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasOne<\Illuminate\Database\Eloquent\Model, $this> staff()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Illuminate\Database\Eloquent\Model, $this> levels()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Modules\Core\Database\Factories\UserFactory> */
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

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\Core\Database\Factories\UserFactory
    {
        return \Modules\Core\Database\Factories\UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }

    /**
     * Get a user preference value.
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Set a user preference value.
     *
     * @return $this
     */
    public function setPreference(string $key, mixed $value): self
    {
        /** @var array<string, mixed> $preferences */
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);
        /** @var array<string, mixed> $preferences */
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Core\Models\Media, $this>
     */
    public function media(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Media::class, 'author_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Core\Models\ActivityLog, $this>
     */
    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Modules\Core\Models\Notification, $this>
     */
    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Core\Models\Notification::class);
    }



    /**
     * Get the two factor authentication record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\Modules\Core\Models\TwoFactorAuth, $this>
     */
    public function twoFactorAuth(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\Core\Models\TwoFactorAuth::class);
    }

    /**
     * Check if 2FA is enabled for this user.
     */
    public function hasTwoFactorEnabled(): bool
    {
        if (! \Modules\Core\Models\Setting::get('enable_2fa', false)) {
            return false;
        }

        return $this->twoFactorAuth && $this->twoFactorAuth->enabled;
    }

    /**
     * Check if 2FA is required for this user (admin users).
     */
    public function requiresTwoFactor(): bool
    {
        // Check global enable switch
        if (! \Modules\Core\Models\Setting::get('enable_2fa', false)) {
            return false;
        }

        $enforcement = \Modules\Core\Models\Setting::get('two_factor_enforced_roles', 'no');

        if ($enforcement === 'all') {
            return true;
        }

        if ($enforcement === 'admin') {
            return $this->isAtLeastRole('admin');
        }

        return false;
    }

    /**
     * Get the numerical rank of the user's highest role.
     * Higher number means higher authority.
     */
    public function getRoleRank(): int
    {
        $roleRanks = $this->getRoleRankMap();
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

    /**
     * Compare this user's rank with another user.
     */
    public function isHigherThan(User $target): bool
    {
        return $this->getRoleRank() > $target->getRoleRank();
    }

    /**
     * Check if user has at least the minimum required role level.
     */
    public function isAtLeastRole(string $roleName): bool
    {
        $roleRanks = self::getRoleRankMap();

        if (! isset($roleRanks[$roleName])) {
            return false;
        }

        return $this->getRoleRank() >= $roleRanks[$roleName];
    }

    /**
     * Get the standardized role rank map for the system.
     * Higher number means higher authority.
     *
     * @return array<string, int>
     */
    public static function getRoleRankMap(): array
    {
        $coreRanks = [
            // Global/System Roles
            'super' => 100,
            'system-admin' => 98,
            'admin' => 95,
            'operator' => 85,
            'member' => 20,

            // CMS scoped roles (prefix strategy)
            'cms:admin' => 60,
            'cms:editor' => 50,
        ];

        return array_merge($coreRanks, static::$moduleRoleRanks);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \Modules\Core\Notifications\VerifyEmail);
    }
}
