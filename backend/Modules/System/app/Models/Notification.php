<?php

namespace Modules\System\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property array<string, mixed> $data
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string $title
 * @property string $message
 * @property string|null $action_url
 * @property string|null $action_text
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Notification extends Model
{
    protected $table = 'sys_notifications';


    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'action_url',
        'action_text',
        'is_read',
        'read_at',
        'data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * @param  int|string  $userId
     * @param  array<string, mixed>  $data
     */
    public static function createForUser($userId, string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, array $data = []): self
    {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, self>
     */
    public static function createForAll(string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, array $data = []): array
    {
        $users = \Modules\System\Models\User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = static::createForUser($user->id, $type, $title, $message, $actionUrl, $actionText, $data);
        }

        return $notifications;
    }
}
