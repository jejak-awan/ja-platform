<?php

namespace Modules\Security\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Database\Factories\IpListFactory;
use Modules\System\Models\User;

class IpList extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sec_ip_lists';

    /** @use HasFactory<IpListFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): IpListFactory
    {
        return IpListFactory::new();
    }

    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'created_by',
    ];

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeBlocklist($query)
    {
        return $query->where('type', 'blocklist');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWhitelist($query)
    {
        return $query->where('type', 'whitelist');
    }

    /**
     * Get the user who created this entry
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if IP is in blocklist
     */
    public static function isBlocked(string $ip): bool
    {
        return self::where('ip_address', $ip)->where('type', 'blocklist')->exists();
    }

    /**
     * Check if IP is in whitelist
     */
    public static function isWhitelisted(string $ip): bool
    {
        return self::where('ip_address', $ip)->where('type', 'whitelist')->exists();
    }
}
