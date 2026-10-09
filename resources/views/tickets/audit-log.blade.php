<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - {{ $ticket->ticket_number ?? $ticket->id }}</title>
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

        .ticket-ref {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--line);
        }

        .ticket-ref .ticket-id {
            font-weight: 700;
            color: #c4b5fd;
            font-size: 1.1rem;
        }

        .ticket-ref .ticket-title {
            color: var(--muted);
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

        .badge.created { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
        .badge.updated { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
        .badge.deleted { background: rgba(248, 113, 113, 0.12); color: #fca5a5; }
        .badge.restored { background: rgba(34, 197, 94, 0.12); color: #86efac; }
        .badge.force-deleted { background: rgba(248, 113, 113, 0.18); color: #f87171; }

        .timeline {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .log-item {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .log-item:last-child { border-bottom: none; }

        .log-time {
            color: var(--muted);
            font-size: 0.8rem;
            padding-top: 2px;
        }

        .log-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .log-header {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .log-user {
            font-weight: 700;
            font-size: 0.9rem;
        }

        .log-action {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .log-diff {
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .diff-row {
            display: flex;
            gap: 8px;
            align-items: baseline;
            flex-wrap: wrap;
        }

        .diff-field {
            font-weight: 700;
            color: #c4b5fd;
            min-width: 90px;
        }

        .diff-old {
            color: #fca5a5;
            text-decoration: line-through;
        }

        .diff-new {
            color: #86efac;
        }

        .diff-arrow {
            color: var(--muted);
        }

        .empty {
            padding: 28px 18px;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 720px) {
            .log-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1 class="title">Audit Log</h1>
            <a href="{{ route('tickets.show', $ticket->id) }}" class="link">← Kembali ke Detail Tiket</a>
        </div>

        <div class="panel">
            <div class="ticket-ref">
                <span class="ticket-id">{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
                <span class="ticket-title">{{ $ticket->title }}</span>
            </div>

            <div class="timeline">
                @forelse($logs as $log)
                    <div class="log-item">
                        <div class="log-time">{{ $log->created_at?->format('d M Y, H:i:s') ?? '-' }}</div>
                        <div class="log-content">
                            <div class="log-header">
                                <span class="badge {{ strtolower(str_replace(' ', '-', $log->action)) }}">{{ $log->action }}</span>
                                <span class="log-user">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="log-action">melakukan perubahan pada tiket</span>
                            </div>

                            @if($log->old_value || $log->new_value)
                                <div class="log-diff">
                                    @php
                                        $old = is_array($log->old_value) ? $log->old_value : json_decode($log->old_value ?? '{}', true);
                                        $new = is_array($log->new_value) ? $log->new_value : json_decode($log->new_value ?? '{}', true);
                                        $old = $old ?: [];
                                        $new = $new ?: [];
                                        $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
                                    @endphp

                                    @foreach($fields as $field)
                                        <div class="diff-row">
                                            <span class="diff-field">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                                            @if(isset($old[$field]))
                                                <span class="diff-old">{{ $old[$field] }}</span>
                                            @endif
                                            <span class="diff-arrow">→</span>
                                            @if(isset($new[$field]))
                                                <span class="diff-new">{{ $new[$field] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty">Belum ada aktivitas audit untuk tiket ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>