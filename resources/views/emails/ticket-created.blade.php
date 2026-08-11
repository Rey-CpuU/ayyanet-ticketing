<x-mail::message>
# Tiket Baru: {{ $ticket->ticket_number }}

Tiket baru telah dibuat oleh **{{ $ticket->creator->name ?? 'System' }}**.

- **Judul:** {{ $ticket->title }}
- **Pelanggan:** {{ $ticket->customer->name ?? '-' }} ({{ $ticket->customer->customer_id ?? '-' }})
- **Kategori:** {{ $ticket->category }}
- **Prioritas:** {{ $ticket->priority }} | **Dampak:** {{ $ticket->impact }}

<x-mail::button :url="route('tickets.show', $ticket->id)">
Lihat Tiket
</x-mail::button>

Terima kasih,<br>
**Ayyanet Support Desk**
</x-mail::message>