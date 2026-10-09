<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Customer - {{ $customer->name }}</title>
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111827;
            --line: rgba(148, 163, 184, 0.18);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --purple: #8b5cf6;
            --green: #22c55e;
            --amber: #f59e0b;
            --red: #f87171;
            --blue: #60a5fa;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #020817 0%, #0f172a 100%);
            color: var(--text);
        }

        .wrap {
            max-width: 980px;
            margin: 0 auto;
            padding: 30px 20px 50px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .title {
            margin: 0;
            font-size: 2rem;
        }

        .link {
            color: #c4b5fd;
            text-decoration: none;
            font-weight: 700;
        }

        .panel {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
        }

        .customer-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .customer-id {
            color: #c4b5fd;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .customer-name {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 10px;
            background: rgba(139, 92, 246, 0.15);
            border: 1px solid rgba(139, 92, 246, 0.4);
            color: #e9ddff;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .btn.danger {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.4);
            color: #fca5a5;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: rgba(17, 24, 39, 0.75);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 18px;
        }

        .card h3 {
            margin: 0 0 12px;
            font-size: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .detail-row:last-child { border-bottom: none; }

        .label {
            color: var(--muted);
        }

        .ticket-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 12px;
        }

        .ticket-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--line);
            border-radius: 10px;
            text-decoration: none;
            color: var(--text);
        }

        .ticket-item:hover {
            border-color: rgba(139, 92, 246, 0.4);
        }

        .ticket-number {
            font-weight: 700;
            color: #c4b5fd;
            font-size: 0.85rem;
            min-width: 80px;
        }

        .ticket-title {
            flex: 1;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .status-open { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
        .status-checking { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
        .status-waiting-customer { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
        .status-solved { background: rgba(34, 197, 94, 0.12); color: #86efac; }
        .status-closed { background: rgba(148, 163, 184, 0.12); color: #cbd5e1; }
        .status-escalated { background: rgba(248, 113, 113, 0.12); color: #fca5a5; }

        .empty {
            padding: 20px;
            color: var(--muted);
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 760px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .actions {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1 class="title">Detail Customer</h1>
            <a href="{{ route('customers.index') }}" class="link">← Kembali ke Daftar Customer</a>
        </div>

        <div class="panel">
            <div class="customer-header">
                <div class="avatar">{{ strtoupper(substr($customer->name, 0, 2)) }}</div>
                <div>
                    <div class="customer-name">{{ $customer->name }}</div>
                    <div class="customer-id">{{ $customer->customer_id }}</div>
                </div>
                <div class="actions">
                    @can('update', $customer)
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn">Edit</a>
                    @endcan
                    @can('delete', $customer)
                        <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" id="delete-customer-form" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn danger" onclick="document.getElementById('delete-customer-form').requestSubmit()">Hapus</button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="grid">
                <div class="card">
                    <h3>Informasi Kontak</h3>
                    <div class="detail-row"><span class="label">Email</span><strong>{{ $customer->email ?? 'N/A' }}</strong></div>
                    <div class="detail-row"><span class="label">No HP</span><strong>{{ $customer->phone ?? 'N/A' }}</strong></div>
                    <div class="detail-row"><span class="label">Alamat</span><strong>{{ $customer->address ?? 'N/A' }}</strong></div>
                    <div class="detail-row"><span class="label">Paket</span><strong>{{ $customer->package ?? 'N/A' }}</strong></div>
                    <div class="detail-row"><span class="label">Terdaftar</span><strong>{{ $customer->created_at?->format('d M Y') ?? '-' }}</strong></div>
                </div>

                <div class="card">
                    <h3>Riwayat Tiket</h3>
                    @php
                        $tickets = $customer->tickets()->visibleTo(auth()->user())->latest()->get();
                    @endphp

                    @forelse($tickets as $ticket)
                        <div class="ticket-list">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-item">
                                <span class="ticket-number">{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="ticket-title">{{ $ticket->title }}</span>
                                <span class="badge status-{{ \Illuminate\Support\Str::slug(strtolower($ticket->status)) }}">{{ $ticket->status }}</span>
                            </a>
                        </div>
                    @empty
                        <div class="empty">Belum ada tiket untuk customer ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>