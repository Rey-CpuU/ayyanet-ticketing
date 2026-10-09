<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket #{{ $ticket->ticket_number ?? $ticket->id }}</title>
    <style>
        .chat-box {
            border: 1px solid #ccc;
            padding: 15px;
            height: 300px;
            overflow-y: scroll;
            background-color: #f9f9f9;
            margin-bottom: 15px;
        }
        .message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 5px;
            background-color: #e2e3e5;
        }
    </style>
</head>
<body>
    <a href="{{ route('tickets.index') }}">← Kembali ke Daftar Tiket</a>

    <h2>Detail Tiket: {{ $ticket->title }}</h2>
    <p><strong>Customer:</strong> {{ $ticket->customer->name ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ $ticket->status }} | <strong>Prioritas:</strong> {{ $ticket->priority }}</p>
    <p><strong>Deskripsi Keluhan:</strong> {{ $ticket->description }}</p>

    <hr>

    <h3>💬 Obrolan / Riwayat Penanganan</h3>

    @if(session('success'))
        <p style="color: green; font-weight: bold;">{{ session('success') }}</p>
    @endif

    <div class="chat-box">
        @forelse($ticket->messages as $msg)
            <div class="message">
                <strong>{{ $msg->user_id ? 'CS Ayyanet' : 'User/Customer' }}:</strong>
                <p style="margin: 5px 0 0 0;">{{ $msg->message }}</p>
            </div>
        @empty
            <p>Belum ada riwayat pesan.</p>
        @endforelse
    </div>

    <form action="{{ route('tickets.messages.store', $ticket->id) }}" method="POST">
        @csrf
        <input type="text" name="message" placeholder="Tulis balasan pesan di sini..." style="width: 70%; padding: 8px;" required>
        <button type="submit" style="padding: 8px 15px;">Kirim Pesan</button>
    </form>
</body>
</html>
