<?php

namespace Modules\Member\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\System\Models\User;

class Member extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'mbr_members';

    protected $fillable = [
        'user_id',
        'points',
        'tier',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
