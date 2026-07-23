<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications)
    {
    }

    /**
     * Display the notification center — every notification for the
     * authenticated user, newest first.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('Notification/NotificationPage', [
            'notifications' => NotificationResource::collection($this->notifications->notificationsForUser($userId))->resolve(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeOwner($request, $notification);

        $this->notifications->markAsRead($notification);

        return back();
    }

    /**
     * Mark every notification for the authenticated user as read.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllAsRead($request->user()->id);

        return back();
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorizeOwner($request, $notification);

        $this->notifications->destroy($notification);

        return back();
    }

    /**
     * Ensure the authenticated user owns the given notification.
     */
    private function authorizeOwner(Request $request, Notification $notification): void
    {
        if ($notification->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('You do not own this notification.');
        }
    }
}
