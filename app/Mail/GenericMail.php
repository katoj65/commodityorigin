<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $mailSubject  The email's subject line.
     * @param  string  $body  The email's main message. May contain Markdown.
     * @param  string|null  $actionText  Label for the optional call-to-action button.
     * @param  string|null  $actionUrl  URL for the optional call-to-action button.
     */
    public function __construct(
        public string $mailSubject,
        public string $body,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.generic',
            with: [
                'body' => $this->body,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
            ],
        );
    }
}
