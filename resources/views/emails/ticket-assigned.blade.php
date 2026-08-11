<x-mail::message>
# Penugasan Tiket: {{ $ticket->ticket_number }}

Tiket **{{ $ticket->ticket_number }}** telah ditugaskan kepada Anda.

- **Judul:** {{ $ticket->title }}
- **Pelanggan:** {{ $ticket->customer->name ?? '-' }}
- **Prioritas:** {{ $ticket->priority }}
- **Status:** {{ $ticket->status }}

<x-mail::button :url="route('tickets.show', $ticket->id)">
Buka Tiket Saya
</x-mail::button>

Terima kasih,<br>
**Ayyanet Support Desk**
</x-mail::message>