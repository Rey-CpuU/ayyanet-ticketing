<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Queued: the login request must not wait on SMTP. Carries no secrets.
 */
class SuspiciousLoginAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $targetEmail,
        public string $ipAddress,
        public string $timestamp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Peringatan Keamanan: Percobaan Login Gagal Mencurigakan'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.suspicious-login'
        );
    }
}
