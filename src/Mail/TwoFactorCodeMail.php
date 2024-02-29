<?php

namespace BuckhamDuffy\LaravelTwoFactor\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private int $code)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Two Factor Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'two-factor::emails.two-factor-code',
            with: [
                'code' => $this->code,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
