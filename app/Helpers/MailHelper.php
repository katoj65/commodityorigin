<?php

namespace App\Helpers;

use App\Mail\GenericMail;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    /**
     * Send an email immediately.
     *
     * @param  string|array<int, string>  $to  One recipient address, or a list of them.
     */
    public static function send(string|array $to, string $subject, string $body, ?string $actionText = null, ?string $actionUrl = null): void
    {
        Mail::to($to)->send(new GenericMail($subject, $body, $actionText, $actionUrl));
    }

    /**
     * Queue an email to be sent by a queue worker instead of the current request.
     *
     * @param  string|array<int, string>  $to  One recipient address, or a list of them.
     */
    public static function queue(string|array $to, string $subject, string $body, ?string $actionText = null, ?string $actionUrl = null): void
    {
        Mail::to($to)->queue(new GenericMail($subject, $body, $actionText, $actionUrl));
    }
}
