<?php

namespace Modules\Security\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Modules\System\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpList extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'sec_ip_lists';


    /** @use HasFactory<\Modules\System\Database\Factories\IpListFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Modules\System\Database\Factories\IpListFactory
    {
        return \Modules\System\Database\Factories\IpListFactory::new();
    }

    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'created_by',
    ];

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
     */
    public function scopeBlocklist($query)
    {
        return $query->where('type', 'blocklist');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<$this>  $query
     * @return \Illuminate\Database\Eloquent\Builder<$this>
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
