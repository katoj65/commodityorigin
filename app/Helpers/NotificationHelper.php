<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationHelper
{
    /**
     * Send a platform (in-app) notification to a user by writing a row to the
     * `notifications` table. This is the static convenience entry point for
     * code that doesn't resolve App\Services\NotificationService through the
     * container — it stores the same shape of data so both paths stay in sync.
     *
     * @param  array<string, mixed>  $data
     */
    public static function notify(
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
}
