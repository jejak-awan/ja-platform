<?php

namespace Modules\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\System\Models\Notification;

class SendBroadcastNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload
    ) {}

    public function handle(): void
    {
        $title = is_string($this->payload['title'] ?? null) ? $this->payload['title'] : 'Notification';
        $message = is_string($this->payload['message'] ?? null) ? $this->payload['message'] : '';
        $type = is_string($this->payload['type'] ?? null) ? $this->payload['type'] : 'info';
        $targetType = is_string($this->payload['target_type'] ?? null) ? $this->payload['target_type'] : 'all';

        try {
            if ($targetType === 'all') {
                Notification::createForAll($type, $title, $message, data: $this->payload);

                return;
            }

            if ($targetType === 'user' && is_numeric($this->payload['target_id'] ?? null)) {
                Notification::createForUser((int) $this->payload['target_id'], $type, $title, $message, data: $this->payload);

                return;
            }

            Log::info('SendBroadcastNotification: unsupported target', $this->payload);
        } catch (\Throwable $e) {
            Log::warning('SendBroadcastNotification failed: '.$e->getMessage(), [
                'payload' => $this->payload,
            ]);
        }
    }
}
