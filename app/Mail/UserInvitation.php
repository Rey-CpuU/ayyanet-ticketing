<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent synchronously (see Invitation::sendMail): the link contains a sign-up token.
 */
class UserInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invitation $invitation,
    ) {}

    /**
     * Absolute sign-up link, built from the named route so it follows the configured app URL.
     */
    public function inviteUrl(): string
    {
        return route('register.invite', $this->invitation->token);
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
                'inviteUrl' => $this->inviteUrl(),
                'expiryHours' => Invitation::EXPIRY_HOURS,
            ],
        );
    }
}
