<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Get a base query builder for notifications.
     */
    public function query(): Builder
    {
        return Notification::query();
    }

    /**
     * Get a user's notifications, newest first.
     *
     * @return Collection<int, Notification>
     */
    public function notificationsForUser(int $userId): Collection
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    /**
     * Count a user's unread notifications — powers the bell icon badge.
     */
    public function unreadCountForUser(int $userId): int
    {
        return Notification::query()
            ->where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Create and dispatch a notification for a major activity. This is the
     * single entry point every other feature (orders, bids, tasks, ...)
     * should call to notify a user — the in-app channel today, additional
     * channels (email, push, SMS, WhatsApp) can hook in here later without
     * changing any callers.
     *
     * @param  array<string, mixed>  $data
     */
    public function notify(
        int $userId,
        string $type,
        string $category,
        string $title,
        ?string $body = null,
        string $priority = 'normal',
        ?string $actionUrl = null,
        ?string $icon = null,
        array $data = [],
        ?Model $source = null,
    ): Notification {
        return Notification::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'category' => $category,
            'priority' => $priority,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
            'icon' => $icon,
            'data' => $data,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        if ($notification->read_at) {
            return;
        }

        $notification->update(['read_at' => now()]);
    }

    /**
     * Mark every unread notification for a user as read.
     */
    public function markAllAsRead(int $userId): void
    {
        Notification::query()
            ->where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): void
    {
        $notification->delete();
    }
}
