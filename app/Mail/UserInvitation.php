<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public string $inviteUrl;

    public function __construct(
        public Invitation $invitation,
    ) {
        $baseUrl = env('APP_URL', 'https://ayyanet-ticketing.vercel.app');
        if ($baseUrl === 'http://localhost') {
            $baseUrl = 'https://ayyanet-ticketing.vercel.app';
        }

        $this->inviteUrl = rtrim($baseUrl, '/') . "/register/invite/{$invitation->token}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Akses Ayyanet Ticketing',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user-invitation',
            with: [
                'inviteUrl' => $this->inviteUrl,
            ],
        );
    }
}
