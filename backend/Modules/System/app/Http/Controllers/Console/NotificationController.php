<?php

namespace Modules\System\Http\Controllers\Console;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\System\Models\Notification;

class NotificationController extends BaseApiController
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();
            /** @var \Modules\System\Models\User|null $user */
            if (! $user) {
                return $this->unauthorized('Unauthenticated');
            }

            // Use Notification model directly with user_id filter
            $query = Notification::where('user_id', $user->id);

            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            if ($request->has('type')) {
                $typeRaw = $request->type;
                $type = is_string($typeRaw) ? $typeRaw : '';
                $query->where('type', $type);
            }

            $limitRaw = $request->input('limit', 20);
            $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

            // Always use pagination for consistency, but limit results if limit is specified
            if ($limit > 0 && $limit < 100) {
                $notifications = $query->latest()->limit($limit)->get();
                // Return as paginated response for consistency
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $notifications,
                    $notifications->count(),
                    $limit,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );

                return $this->paginated($paginator, 'Notifications retrieved successfully');
            }

            $notifications = $query->latest()->paginate($limit > 0 ? $limit : 20);

            return $this->paginated($notifications, 'Notifications retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Notifications index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty array instead of error
            return $this->success([], 'Notifications retrieved successfully');
        }
    }

    public function unreadCount(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->success(['count' => 0], 'Unread count retrieved');
        }

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->success(['count' => $count], 'Unread count retrieved');
    }

    public function markAsRead(Request $request, Notification $notification): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if ($notification->user_id !== $user->id) {
            return $this->forbidden('Unauthorized');
        }

        $notification->markAsRead();

        return $this->success($notification, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'All notifications marked as read');
    }

    public function indexSystem(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super')) {
            if (! $user->can('manage system')) {
                return $this->forbidden('Unauthorized');
            }
        }

        $limitRaw = $request->input('limit', 20);
        $limit = is_numeric($limitRaw) ? (int) $limitRaw : 20;

        // Group by title, message, type, and approximate created_at to find unique "broadcasts"
        $notifications = Notification::selectRaw('MIN(id) as id, title, message, type, MIN(created_at) as created_at, COUNT(*) as recipient_count')
            ->groupBy('title', 'message', 'type')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return $this->paginated($notifications, 'System notifications retrieved');
    }

    public function revokeSystem(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'created_at' => 'required|string',
        ]);

        $createdAtRaw = $request->created_at;
        $createdAtStr = is_string($createdAtRaw) ? $createdAtRaw : '';
        $createdAt = Carbon::parse($createdAtStr);
        $startOfMinute = (clone $createdAt)->startOfMinute();
        $endOfMinute = (clone $createdAt)->endOfMinute();

        $countRaw = Notification::where('title', $request->title)
            ->where('message', $request->message)
            ->whereBetween('created_at', [$startOfMinute, $endOfMinute])
            ->delete();

        $count = is_numeric($countRaw) ? (int) $countRaw : 0;

        $countStr = (string) $count;

        return $this->success(['count' => $count], "Broadcast revoked. {$countStr} notifications removed.");
    }

    public function bulkRevokeSystem(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $request->validate([
            'broadcasts' => 'required|array',
            'broadcasts.*.title' => 'required|string',
            'broadcasts.*.message' => 'required|string',
            'broadcasts.*.created_at' => 'required|string',
        ]);

        $totalDeleted = 0;
        $broadcasts = is_array($request->broadcasts) ? $request->broadcasts : [];

        foreach ($broadcasts as $broadcast) {
            if (! is_array($broadcast)) {
                continue;
            }
            $createdAtRaw = $broadcast['created_at'] ?? '';
            $createdAtStr = is_string($createdAtRaw) ? $createdAtRaw : '';
            $createdAt = Carbon::parse($createdAtStr);
            $startOfMinute = (clone $createdAt)->startOfMinute();
            $endOfMinute = (clone $createdAt)->endOfMinute();

            $titleRaw = $broadcast['title'] ?? '';
            $title = is_string($titleRaw) ? $titleRaw : '';
            $messageRaw = $broadcast['message'] ?? '';
            $message = is_string($messageRaw) ? $messageRaw : '';

            $countRaw = Notification::where('title', $title)
                ->where('message', $message)
                ->whereBetween('created_at', [$startOfMinute, $endOfMinute])
                ->delete();

            $count = is_numeric($countRaw) ? (int) $countRaw : 0;

            $totalDeleted += $count;
        }

        $totalDeletedStr = (string) $totalDeleted;

        return $this->success(['count' => $totalDeleted], "Bulk revocation complete. {$totalDeletedStr} notifications removed.");
    }

    public function broadcast(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if (! $user->hasRole('super') && ! $user->can('manage system')) {
            return $this->forbidden('Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'target_type' => 'required|in:all,role,user',
            'target_id' => 'required_if:target_type,user,role',
            'is_async' => 'nullable|boolean',
        ]);

        $payload = [
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'target_type' => $request->target_type,
            'target_id' => $request->target_id,
            'sender_id' => $user->id,
        ];

        $isAsync = $request->boolean('is_async', true);

        // Fallback to sync if queue driver is sync or user explicitly requested it
        if ($isAsync && config('queue.default') !== 'sync') {
            \Modules\System\Jobs\SendBroadcastNotification::dispatch($payload);

            return $this->success(null, 'Broadcast notification queued for delivery');
        } else {
            // Direct delivery (sync)
            \Modules\System\Jobs\SendBroadcastNotification::dispatchSync($payload);

            return $this->success(null, 'Broadcast notification delivered successfully');
        }
    }

    public function destroy(Request $request, Notification $notification): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\System\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized('Unauthenticated');
        }

        if ($notification->user_id !== $user->id) {
            return $this->forbidden('Unauthorized');
        }

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }
}
