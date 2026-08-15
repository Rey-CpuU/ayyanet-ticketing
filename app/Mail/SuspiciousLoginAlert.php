<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuspiciousLoginAlert extends Mailable
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
