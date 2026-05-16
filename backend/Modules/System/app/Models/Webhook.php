<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Webhook extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Stub for triggerForEvent to avoid breaking dependencies.
     */
    public static function triggerForEvent(string $event, array $data): void
    {
        // For now, do nothing. Webhooks can be implemented later.
        // \Illuminate\Support\Facades\Log::info("Webhook triggered: {$event}", $data);
    }
}
