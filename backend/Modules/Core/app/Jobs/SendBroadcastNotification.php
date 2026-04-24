<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Notification;
use Modules\Core\Models\User;

class SendBroadcastNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<string, mixed> */
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $type = $this->payload['type'] ?? 'info';
        $title = $this->payload['title'] ?? '';
        $message = $this->payload['message'] ?? '';
        $targetType = $this->payload['target_type'] ?? 'all';
        $targetId = $this->payload['target_id'] ?? null;
        $senderId = $this->payload['sender_id'] ?? null;

        $query = User::query();

        if ($targetType === 'role' && $targetId) {
            $role = is_string($targetId) || is_numeric($targetId) ? (string) $targetId : '';
            $query->role($role);
        } elseif ($targetType === 'user' && $targetId) {
            $id = is_string($targetId) || is_numeric($targetId) ? (string) $targetId : '';
            $query->where('id', $id);
        }

        $query->chunk(100, function ($users) use ($type, $title, $message, $senderId) {
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false,
                    'data' => [
                        'sender_id' => $senderId,
                    ],
                ]);
            }
        });
    }
}
