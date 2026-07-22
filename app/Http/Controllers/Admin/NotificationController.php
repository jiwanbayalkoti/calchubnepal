<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->paginate(30);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'data' => $notifications->getCollection()->map(fn (DatabaseNotification $n) => $this->transform($n)),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'total' => $notifications->total(),
                    'unread' => $user->unreadNotifications()->count(),
                ],
            ]);
        }

        return view('admin.notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (DatabaseNotification $n) => $this->transform($n));

        return response()->json([
            'data' => $items,
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $this->transform($notification->fresh()),
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unread' => 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(DatabaseNotification $notification): array
    {
        $data = (array) $notification->data;

        return [
            'id' => $notification->id,
            'category' => $data['category'] ?? 'general',
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'icon' => $data['icon'] ?? 'fas fa-bell',
            'url' => $data['url'] ?? route('admin.notifications.index'),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->diffForHumans(),
            'created_at_raw' => $notification->created_at?->toIso8601String(),
            'is_unread' => $notification->read_at === null,
        ];
    }
}
