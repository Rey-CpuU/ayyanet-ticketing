<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketUpdatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Ticket $ticket, public string $action) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket '.$this->ticket->ticket_number.' - '.$this->action,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-updated',
            with: [
                'ticket' => $this->ticket,
                'action' => $this->action,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
