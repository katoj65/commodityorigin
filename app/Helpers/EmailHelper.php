<?php

namespace App\Helpers;

use App\Mail\GenericMail;
use App\Models\Email;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailHelper
{
    /**
     * Send an email immediately and record it in the `emails` table.
     *
     * The row is written up-front with a "queued" status so the outbox always
     * has a record, then marked "sent" (with `sent_at`) on success or "failed"
     * (with the error message) when the transport throws.
     *
     * @param  string|array<int, string>  $to  One recipient address, or a list of them.
     */
    public static function send(
        string|array $to,
        string $subject,
        string $body,
        ?string $actionText = null,
        ?string $actionUrl = null,
        ?int $userId = null,
    ): Email {
        $email = self::record($to, $subject, $body, $actionText, $actionUrl, $userId);

        try {
            Mail::to($to)->send(new GenericMail($subject, $body, $actionText, $actionUrl));

            $email->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $e) {
            $email->update(['status' => 'failed', 'error' => $e->getMessage()]);

            throw $e;
        }

        return $email;
    }

    /**
     * Queue an email to be sent by a queue worker, and record it in the
     * `emails` table with a "queued" status.
     *
     * @param  string|array<int, string>  $to  One recipient address, or a list of them.
     */
    public static function queue(
        string|array $to,
        string $subject,
        string $body,
        ?string $actionText = null,
        ?string $actionUrl = null,
        ?int $userId = null,
    ): Email {
        $email = self::record($to, $subject, $body, $actionText, $actionUrl, $userId);

        try {
            Mail::to($to)->queue(new GenericMail($subject, $body, $actionText, $actionUrl));
        } catch (Throwable $e) {
            $email->update(['status' => 'failed', 'error' => $e->getMessage()]);

            throw $e;
        }

        return $email;
    }

    /**
     * Persist an outbox row describing the email before it is dispatched.
     *
     * @param  string|array<int, string>  $to
     */
    private static function record(
        string|array $to,
        string $subject,
        string $body,
        ?string $actionText,
        ?string $actionUrl,
        ?int $userId,
    ): Email {
        return Email::query()->create([
            'user_id' => $userId,
            'recipient' => is_array($to) ? implode(', ', $to) : $to,
            'subject' => $subject,
            'body' => $body,
            'action_text' => $actionText,
            'action_url' => $actionUrl,
            'status' => 'queued',
        ]);
    }
}
