<x-mail::message>
# Perubahan Status Tiket: {{ $ticket->ticket_number }}

Status tiket **{{ $ticket->ticket_number }}** telah diperbarui menjadi **{{ $ticket->status }}**.

- **Judul:** {{ $ticket->title }}
- **Pelanggan:** {{ $ticket->customer->name ?? '-' }}
- **Status Baru:** {{ $ticket->status }}

<x-mail::button :url="route('tickets.show', $ticket->id)">
Lihat Rincian Tiket
</x-mail::button>

Terima kasih,<br>
**Ayyanet Support Desk**
</x-mail::message>