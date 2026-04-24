<?php

namespace Modules\Cms\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Cms\Models\Comment;
use Modules\Cms\Models\Content;
use Modules\Core\Http\Controllers\Api\BaseApiController;

class CommentController extends BaseApiController
{
    protected \Modules\Cms\Services\CommentSecurityService $securityService;

    public function __construct(\Modules\Cms\Services\CommentSecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Content $content): \Illuminate\Http\JsonResponse
    {
        $comments = Comment::with(['user', 'replies' => function ($q) {
            $q->where('status', 'approved')->with('user');
        }])
            ->where('content_id', $content->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return $this->success($comments, 'Comments retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Content $content): \Illuminate\Http\JsonResponse
    {
        // Check if comments are enabled for this content
        if (! $content->comment_status) {
            return $this->error('Comments are disabled for this content', 403);
        }

        try {
            $validated = $request->validate([
                'body' => 'required|string',
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'parent_id' => 'nullable|exists:comments,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        $authorEmail = '';

        // If user is authenticated, use user data
        if ($user) {
            $validated['user_id'] = $user->id;
            $authorEmail = (string) $user->email;
        } else {
            // Check if guests are allowed
            if (! \Modules\Core\Models\Setting::get('comments.security.allow_guests', true)) {
                return $this->error('Guest comments are disabled', 403);
            }

            // For guest comments, name and email are required
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);

            // Captcha Validation for Guests
            if (\Modules\Core\Models\Setting::get('comments.security.guest_captcha', true)) {
                $captchaService = app(\Modules\Core\Services\CaptchaService::class);
                $captchaTokenRaw = $request->input('captcha_token');
                $captchaToken = is_string($captchaTokenRaw) ? $captchaTokenRaw : '';
                $captchaInputRaw = $request->input('captcha_input');
                $captchaInput = is_string($captchaInputRaw) ? $captchaInputRaw : '';

                if (! $captchaService->verify($captchaToken, $captchaInput)) {
                    return $this->error('Invalid captcha', 422);
                }
            }

            $authorEmailRaw = $validated['email'] ?? '';
            $authorEmail = is_string($authorEmailRaw) ? $authorEmailRaw : '';
        }

        // Security Checks
        $body = is_string($validated['body']) ? $validated['body'] : '';
        $isSpam = $this->securityService->isSpam($body, $authorEmail, (string) $request->ip());

        $validated['content_id'] = $content->id;
        $validated['status'] = $this->securityService->getInitialStatus($isSpam);

        $comment = Comment::create($validated);

        $message = $comment->status === 'approved'
            ? 'Comment posted successfully'
            : ($comment->status === 'spam' ? 'Comment marked as spam' : 'Comment pending approval');

        return $this->success($comment->load('user'), $message, 201);
    }

    /**
     * Display a listing of the resource for admin.
     */
    public function adminIndex(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Comment::with(['content', 'user', 'parent']);

        // Multi-tenancy: Authors only see comments on their own content
        if (! $user->can('manage comments')) {
            $query->whereHas('content', function ($q) use ($user) {
                $q->where('author_id', $user->id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('content_id')) {
            $query->where('content_id', $request->input('content_id'));
        }

        if ($request->filled('search')) {
            $searchRaw = $request->input('search');
            $search = is_string($searchRaw) ? $searchRaw : '';
            $query->where(function ($q) use ($search) {
                $q->where('body', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPageRaw = $request->input('per_page', 10);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 100) : 10;
        $comments = $query->latest()->paginate($perPage);

        return $this->paginated($comments, 'Comments retrieved successfully');
    }

    /**
     * Get comment statistics.
     */
    public function statistics(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \Modules\Core\Models\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Comment::query();

        // Multi-tenancy scoping
        if (! $user->can('manage comments')) {
            $query->whereHas('content', function ($q) use ($user) {
                $q->where('author_id', $user->id);
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'spam' => (clone $query)->where('status', 'spam')->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return $this->success($stats, 'Comment statistics retrieved successfully');
    }

    /**
     * Approve the specified comment.
     */
    public function approve(Request $request, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Ownership check
        if (! $user->can('manage comments')) {
            if ($comment->content->author_id !== $user->id) {
                return $this->forbidden('You can only moderate comments on your own content');
            }
        }

        // Lock check
        if ($comment->locked_by && $comment->locked_by !== $user->id) {
            if ($comment->locked_at && $comment->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('Comment is currently locked by another user', 423);
            }
        }

        $comment->update(['status' => 'approved']);

        return $this->success($comment->load(['content', 'user']), 'Comment approved successfully');
    }

    /**
     * Reject the specified comment.
     */
    public function reject(Request $request, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Ownership check
        if (! $user->can('manage comments')) {
            if ($comment->content->author_id !== $user->id) {
                return $this->forbidden('You can only moderate comments on your own content');
            }
        }

        // Lock check
        if ($comment->locked_by && $comment->locked_by !== $user->id) {
            if ($comment->locked_at && $comment->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('Comment is currently locked by another user', 423);
            }
        }

        $comment->update(['status' => 'rejected']);

        return $this->success($comment->load(['content', 'user']), 'Comment rejected successfully');
    }

    /**
     * Mark the specified comment as spam.
     */
    public function markAsSpam(Request $request, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Ownership check
        if (! $user->can('manage comments')) {
            if ($comment->content->author_id !== $user->id) {
                return $this->forbidden('You can only moderate comments on your own content');
            }
        }

        // Lock check
        if ($comment->locked_by && $comment->locked_by !== $user->id) {
            if ($comment->locked_at && $comment->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('Comment is currently locked by another user', 423);
            }
        }

        $comment->update(['status' => 'spam']);

        return $this->success($comment->load(['content', 'user']), 'Comment marked as spam');
    }

    /**
     * Bulk action on multiple comments.
     */
    public function bulkAction(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:comments,id',
            'action' => 'required|in:approve,reject,spam,delete',
        ]);

        $idsRaw = $validated['ids'];
        $ids = is_array($idsRaw) ? $idsRaw : [];
        $count = 0;
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        $comments = Comment::whereIn('id', $ids)->get();
        $action = $validated['action'];

        foreach ($comments as $comment) {
            // Ownership check
            if (! $user->can('manage comments')) {
                if ($comment->content->author_id !== $user->id) {
                    continue; // Skip
                }
            }

            // Lock check
            if ($comment->locked_by && $comment->locked_at && $comment->locked_at->diffInMinutes(now()) < 60) {
                continue; // Skip
            }

            switch ($action) {
                case 'approve':
                    $comment->update(['status' => 'approved']);
                    $count++;
                    break;
                case 'reject':
                    $comment->update(['status' => 'rejected']);
                    $count++;
                    break;
                case 'spam':
                    $comment->update(['status' => 'spam']);
                    $count++;
                    break;
                case 'delete':
                    $comment->delete();
                    $count++;
                    break;
            }
        }

        $message = "{$count} comments processed";

        return $this->success(['affected' => $count], $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return $this->unauthorized();
        }

        // Ownership check
        if (! $user->can('manage comments')) {
            if ($comment->content->author_id !== $user->id) {
                return $this->forbidden('You can only delete comments on your own content');
            }
        }

        // Lock check
        if ($comment->locked_by && $comment->locked_by !== $user->id) {
            if ($comment->locked_at && $comment->locked_at->diffInMinutes(now()) < 60) {
                return $this->error('Cannot delete: Comment is currently locked by another user', 423);
            }
        }

        $comment->delete();

        return $this->success(null, 'Comment deleted successfully');
    }
}
