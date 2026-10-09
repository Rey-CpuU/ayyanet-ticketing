@php
    $channelMap = [
        'email' => ['label' => 'Email', 'class' => 'email', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'],
        'live chat' => ['label' => 'Live Chat', 'class' => 'live-chat', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v6A2.5 2.5 0 0 1 14.5 13H9l-4.5 3v-3H5.5A2.5 2.5 0 0 1 3 10.5v-6Zm2 1.5h10v1H5V6Zm0 3h7v1H5v-1Z"/></svg>'],
        'whatsapp' => ['label' => 'WhatsApp', 'class' => 'whatsapp', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M10 1.8A8.2 8.2 0 0 0 3.1 13.7L2 18l4.4-1.1A8.2 8.2 0 1 0 10 1.8Zm4.7 11.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1.3.2-4.1-1.2-3.5-1.7-5.8-6.1-6-6.4-.2-.4-.2-.9.1-1.3.1-.1.3-.2.5-.3l.5-.4c.2-.1.3-.1.5 0l.7.5c.2.2.4.5.5.8.1.2.3.5.1.6-.1.2-.2.3-.3.4-.2.2-.4.4-.6.6-.2.2-.1.4.1.6l.7.8c.3.3.7.5 1 .8.2.1.4.2.7.1.2-.1.7-.8.9-1.1.2-.3.4-.3.7-.2l.9.4c.2.1.4.3.4.5.1.2 0 .6-.2.8Z"/></svg>'],
        'web form' => ['label' => 'Web Form', 'class' => 'web-form', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v11A2.5 2.5 0 0 1 14.5 18h-9A2.5 2.5 0 0 1 3 15.5v-11Zm2.5-.5a.5.5 0 0 0-.5.5v1h10v-1a.5.5 0 0 0-.5-.5h-9Zm-.5 4v6h10v-6H5Zm2 1h4v1H7v-1Zm0 2h6v1H7v-1Z"/></svg>'],
        'portal' => ['label' => 'Portal', 'class' => 'portal', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M10 2.3A7.7 7.7 0 1 1 2.3 10 7.7 7.7 0 0 1 10 2.3Zm0 1.5a6.2 6.2 0 1 0 6.2 6.2A6.2 6.2 0 0 0 10 3.8Zm-1 2.2h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>'],
    ];

    $rawChannel = strtolower($ticket->category ?? 'Email');
    $channel = $channelMap[$rawChannel] ?? $channelMap['email'];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tiket #{{ $ticket->ticket_number ?? $ticket->id }}</title>
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111827;
            --panel-soft: #0f172a;
            --line: rgba(148, 163, 184, 0.18);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --purple: #8b5cf6;
            --green: #22c55e;
            --cyan: #2dd4bf;
            --blue: #60a5fa;
            --amber: #f59e0b;
            --red: #f87171;
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

        .ticket-panel {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
        }

        .meta-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 18px;
        }

        .channel-tag, .status-tag, .priority-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .channel-tag.email { background: rgba(96,165,250,0.14); color: #93c5fd; }
        .channel-tag.live-chat { background: rgba(45, 212, 191, 0.14); color: #5eead4; }
        .channel-tag.whatsapp { background: rgba(34, 197, 94, 0.14); color: #86efac; }
        .channel-tag.web-form { background: rgba(168, 85, 247, 0.14); color: #d8b4fe; }
        .channel-tag.portal { background: rgba(251, 146, 60, 0.14); color: #fdba74; }
        .status-tag.open { background: rgba(34, 197, 94, 0.12); color: #86efac; }
        .status-tag.checking { background: rgba(245, 158, 11, 0.12); color: #fbbf24; }
        .status-tag.solved { background: rgba(34, 197, 94, 0.12); color: #86efac; }
        .priority-tag.high { background: rgba(248, 113, 113, 0.12); color: #fca5a5; }
        .priority-tag.medium { background: rgba(251, 191, 36, 0.12); color: #fcd34d; }
        .priority-tag.low { background: rgba(96,165,250,0.12); color: #93c5fd; }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
        }

        .ticket-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        .ticket-id {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1.3fr 0.7fr;
            gap: 24px;
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

        .message-box {
            max-height: 300px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 12px;
        }

        .message {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            background: rgba(15, 23, 42, 0.8);
        }

        .message strong {
            display: block;
            margin-bottom: 6px;
        }

        form {
            margin-top: 18px;
            display: flex;
            gap: 10px;
        }

        input {
            flex: 1;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: rgba(15, 23, 42, 0.8);
            color: var(--text);
        }

        button {
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #8b5cf6, #6d58d1);
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .success {
            color: #86efac;
            margin-bottom: 12px;
            font-weight: 700;
        }

        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 10px 14px;
            background: rgba(139, 92, 246, 0.12);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 10px;
            color: #c4b5fd;
            text-decoration: none;
            font-weight: 700;
        }

        .action-links {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid rgba(139, 92, 246, 0.4);
            background: rgba(139, 92, 246, 0.12);
            color: #e9ddff;
        }

        .action-link.danger {
            border-color: rgba(248, 113, 113, 0.4);
            background: rgba(248, 113, 113, 0.12);
            color: #fca5a5;
        }

        .action-link.ghost {
            border-color: var(--line);
            background: rgba(148, 163, 184, 0.08);
            color: var(--text);
        }

        .action-link .spinner {
            display: none;
        }

        .action-link.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .action-link.loading .spinner {
            display: inline-block;
            margin-right: 6px;
        }

        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        .spinner.dark {
            border-color: rgba(139, 92, 246, 0.2);
            border-top-color: #8b5cf6;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Confirm delete modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 8, 23, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-box {
            background: #111827;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 28px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(248, 113, 113, 0.12);
            color: #f87171;
            font-size: 1.4rem;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .modal-text {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-actions button {
            padding: 10px 16px;
            font-size: 0.85rem;
            width: auto;
        }

        .sla-box {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 10px 14px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--line);
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .sla-status.breached { color: #f87171; }
        .sla-status.met { color: #47d791; }
        .sla-status.active { color: #fbbf24; }
        .sla-status.paused { color: #93c5fd; }

        .status-tag.waiting-customer { background: rgba(96, 165, 250, 0.12); color: #93c5fd; }
        .status-tag.escalated { background: rgba(248, 113, 113, 0.12); color: #fca5a5; }
        .status-tag.closed { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; }

        .sla-countdown {
            display: block;
            margin-top: 4px;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .sla-box.stacked {
            display: block;
        }

        .sla-box .sla-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        form.stack-form {
            display: block;
            margin-top: 0;
        }

        .stack-form label {
            display: block;
            margin: 12px 0 6px;
            color: var(--muted);
            font-size: 0.85rem;
        }

        .stack-form label:first-of-type {
            margin-top: 0;
        }

        .stack-form select,
        .stack-form textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: rgba(15, 23, 42, 0.8);
            color: var(--text);
            font: inherit;
        }

        .stack-form textarea {
            min-height: 80px;
            resize: vertical;
        }

        .stack-form button {
            width: 100%;
            margin-top: 12px;
        }

        .field-error-text {
            margin-top: 6px;
            color: #fca5a5;
            font-size: 0.8rem;
        }

        .message-form {
            flex-wrap: wrap;
        }

        .internal-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-basis: 100%;
            color: var(--muted);
            font-size: 0.85rem;
            cursor: pointer;
        }

        .internal-toggle input {
            flex: none;
            width: auto;
            padding: 0;
            accent-color: #8b5cf6;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 420px;
            overflow-y: auto;
        }

        .activity {
            padding: 10px 12px;
            border-left: 3px solid rgba(139, 92, 246, 0.5);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 0 10px 10px 0;
            font-size: 0.9rem;
        }

        .activity.internal_note { border-left-color: #fbbf24; }
        .activity.sla_breached { border-left-color: #f87171; }
        .activity.resolution_note { border-left-color: #22c55e; }

        .activity-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 8px;
        }

        .activity-label {
            color: #c4b5fd;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .activity-time {
            margin-left: auto;
            color: var(--muted);
            font-size: 0.75rem;
        }

        .activity-body {
            margin-top: 4px;
            color: #cbd5e1;
            white-space: pre-line;
            word-break: break-word;
        }

        .resolution-text {
            margin: 0;
            white-space: pre-line;
            color: #cbd5e1;
        }

        @media (max-width: 760px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .ticket-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @include('partials.status-banner')
    <div class="wrap">
        <div class="topbar">
            <h1 class="title">Ticket Detail</h1>
            <div class="action-links">
                <x-notification-bell />
                <a href="{{ route('dashboard') }}" class="link">← Kembali ke Queue</a>
            </div>
        </div>

        <div class="ticket-panel">
            <div class="meta-bar">
                <span class="channel-tag {{ $channel['class'] }}">{!! $channel['icon'] !!} {{ $channel['label'] }}</span>
                <span class="status-tag {{ Str::slug($ticket->status ?? 'open') }}">{{ $ticket->status ?? 'Open' }}</span>
                <span class="priority-tag {{ strtolower($ticket->priority ?? 'medium') }}">{{ $ticket->priority ?? 'Medium' }}</span>
            </div>

            <div class="ticket-header">
                <div>
                    <h2>{{ $ticket->title }}</h2>
                    <div class="ticket-id">{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="action-links">
                    <a href="{{ route('tickets.index') }}" class="action-link ghost">Lihat daftar tiket</a>
                    @can('update', $ticket)
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="action-link">✏️ Edit</a>
                    @endcan
                    <a href="{{ route('tickets.audit-log', $ticket->id) }}" class="action-link ghost">📋 Audit Log</a>
                    @can('delete', $ticket)
                        <button type="button" class="action-link danger" onclick="openDeleteModal({{ Js::from($ticket->ticket_number ?? 'TKT-' . str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT)) }}, 'delete-ticket-form')">🗑️ Hapus</button>
                    @endcan
                </div>
            </div>

            <div class="grid">
                <div>
                    <div class="card">
                        <h3>Deskripsi</h3>
                        <p>{{ $ticket->description }}</p>
                    </div>

                    <div class="card" style="margin-top: 18px;">
                        <h3>Riwayat Pesan</h3>

                        @if(session('success'))
                            <div class="success">{{ session('success') }}</div>
                        @endif

                        <x-notification />

                        <div class="message-box">
                            @forelse($ticket->messages as $msg)
                                <div class="message">
                                    <strong>{{ $msg->user?->name ?? 'Customer' }}</strong>
                                    @if($msg->is_internal)
                                        <span style="margin-left: 6px; padding: 2px 8px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; background: rgba(251, 191, 36, 0.14); color: #fbbf24;">Internal</span>
                                    @endif
                                    <div>{{ $msg->message }}</div>
                                </div>
                            @empty
                                <div class="message">
                                    <strong>System</strong>
                                    <div>Belum ada riwayat pesan untuk tiket ini.</div>
                                </div>
                            @endforelse
                        </div>

                        @can('reply', $ticket)
                            <form action="{{ route('tickets.messages.store', $ticket->id) }}" method="POST" id="message-form" class="message-form">
                                @csrf
                                <input type="text" name="message" placeholder="Tulis balasan..." maxlength="2000" required aria-label="Pesan">
                                <button type="submit" id="send-message-btn">
                                    <span class="spinner" style="display:none;"></span>
                                    Kirim
                                </button>
                                @can('addInternalNote', $ticket)
                                    <label class="internal-toggle">
                                        <input type="checkbox" name="is_internal" value="1">
                                        Catatan internal (hanya terlihat oleh staf)
                                    </label>
                                @endcan
                            </form>
                            @error('message')
                                <div class="field-error-text">{{ $message }}</div>
                            @enderror
                        @endcan
                    </div>

                    <div class="card" style="margin-top: 18px;">
                        <h3>Activity Log</h3>
                        <div class="activity-list">
                            @forelse($ticket->activities as $activity)
                                <div class="activity {{ $activity->action }}">
                                    <div class="activity-head">
                                        <strong>{{ $activity->user?->name ?? 'Sistem' }}</strong>
                                        <span class="activity-label">{{ $activity->label() }}</span>
                                        <span class="activity-time" title="{{ $activity->created_at?->format('d M Y, H:i') }}">{{ $activity->created_at?->diffForHumans() }}</span>
                                    </div>
                                    @if($activity->isTransition())
                                        <div class="activity-body">{{ $activity->old_value ?? '-' }} → {{ $activity->new_value ?? '-' }}</div>
                                    @elseif($activity->action === 'sla_breached')
                                        <div class="activity-body">Deadline {{ $activity->new_value ?? '-' }} terlewati.</div>
                                    @elseif($activity->action === 'created')
                                        <div class="activity-body">Status awal {{ $activity->new_value ?? 'Open' }}.</div>
                                    @elseif(filled($activity->new_value))
                                        <div class="activity-body">{{ Str::limit($activity->new_value, 300) }}</div>
                                    @endif
                                </div>
                            @empty
                                <div class="message">Belum ada aktivitas untuk tiket ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <h3>Informasi Customer</h3>
                        <div class="detail-row"><span class="label">Nama</span><strong>{{ $ticket->customer->name ?? 'N/A' }}</strong></div>
                        <div class="detail-row"><span class="label">Phone</span><strong>{{ $ticket->customer->phone ?? 'N/A' }}</strong></div>
                        <div class="detail-row"><span class="label">Package</span><strong>{{ $ticket->customer->package ?? 'N/A' }}</strong></div>
                        <div class="detail-row"><span class="label">Alamat</span><strong>{{ $ticket->customer->address ?? 'N/A' }}</strong></div>
                    </div>

                    <div class="card" style="margin-top: 18px;">
                        <h3>Detail Ticket</h3>
                        <div class="detail-row"><span class="label">Kategori</span><strong>{{ $ticket->category ?? 'Email' }}</strong></div>
                        <div class="detail-row"><span class="label">Status</span><strong>{{ $ticket->status ?? 'Open' }}</strong></div>
                        <div class="detail-row"><span class="label">Prioritas</span><strong>{{ $ticket->priority ?? 'Medium' }}</strong></div>
                        <div class="detail-row"><span class="label">Penanggung Jawab</span><strong>{{ $ticket->assignee->name ?? 'Belum ditugaskan' }}</strong></div>
                        <div class="detail-row"><span class="label">Dibuat</span><strong>{{ $ticket->created_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
                        @if($ticket->resolved_at)
                            <div class="detail-row"><span class="label">Diselesaikan</span><strong>{{ $ticket->resolved_at->format('d M Y, H:i') }}</strong></div>
                        @endif

                        @if($ticket->sla_deadline)
                            @php
                                if ($ticket->sla_status === App\Models\Ticket::SLA_MET) {
                                    [$slaState, $slaLabel] = ['met', 'Terpenuhi'];
                                } elseif ($ticket->sla_status === App\Models\Ticket::SLA_BREACHED || (! $ticket->isSlaPaused() && $ticket->sla_deadline->isPast())) {
                                    [$slaState, $slaLabel] = ['breached', 'Terlampaui'];
                                } elseif ($ticket->isSlaPaused()) {
                                    [$slaState, $slaLabel] = ['paused', 'Dijeda'];
                                } else {
                                    [$slaState, $slaLabel] = ['active', 'Berjalan'];
                                }
                                $slaRunning = ! $ticket->statusEnum()->isResolved() && ! $ticket->isSlaPaused();
                                // While paused the remaining time is frozen at the moment the clock stopped.
                                $slaRemaining = $ticket->isSlaPaused()
                                    ? (int) $ticket->sla_paused_at->diffInSeconds($ticket->sla_deadline, false)
                                    : null;
                            @endphp
                            <div class="sla-box stacked" id="sla-box">
                                <div class="sla-row">
                                    <span>SLA Deadline</span>
                                    <span class="sla-status {{ $slaState }}">{{ $slaLabel }}</span>
                                </div>
                                <strong>{{ $ticket->sla_deadline->format('d M Y, H:i') }}</strong>
                                @if($slaRunning)
                                    <span class="sla-countdown sla-status {{ $slaState }}" id="sla-countdown" data-deadline="{{ $ticket->sla_deadline->toIso8601String() }}" aria-live="polite"></span>
                                @elseif($slaRemaining !== null)
                                    <span class="sla-countdown sla-status paused">
                                        Menunggu customer · sisa {{ intdiv(max($slaRemaining, 0), 3600) }}j {{ intdiv(max($slaRemaining, 0) % 3600, 60) }}m
                                    </span>
                                @endif
                            </div>
                        @endif

                        @if($ticket->attachment_path)
                            @if($attachmentAvailable ?? false)
                                <a href="{{ route('tickets.attachment', $ticket) }}" class="attachment-link">
                                    📎 Download Lampiran ({{ basename($ticket->attachment_path) }})
                                </a>
                            @else
                                <span class="attachment-link" style="opacity: 0.6; cursor: default;">
                                    📎 Lampiran tidak tersedia ({{ basename($ticket->attachment_path) }})
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($ticket->resolution_note)
                        <div class="card" style="margin-top: 18px;">
                            <h3>Catatan Penyelesaian</h3>
                            <p class="resolution-text">{{ $ticket->resolution_note }}</p>
                        </div>
                    @endif

                    @can('update', $ticket)
                        <div class="card" style="margin-top: 18px;">
                            <h3>Ubah Status</h3>
                            <form method="POST" action="{{ route('tickets.status.update', $ticket->id) }}" class="stack-form" id="status-form">
                                @csrf
                                @method('PATCH')
                                <label for="status">Status baru</label>
                                <select name="status" id="status" required>
                                    @foreach($allowedStatuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->value }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="field-error-text">{{ $message }}</div>
                                @enderror

                                <label for="resolution_note">Catatan penyelesaian</label>
                                <textarea name="resolution_note" id="resolution_note" maxlength="2000" placeholder="Wajib untuk Solved, atau Closed tanpa penyelesaian sebelumnya">{{ old('resolution_note') }}</textarea>
                                @error('resolution_note')
                                    <div class="field-error-text">{{ $message }}</div>
                                @enderror

                                <button type="submit">Simpan Status</button>
                            </form>
                        </div>
                    @endcan

                    @can('assign', $ticket)
                        <div class="card" style="margin-top: 18px;">
                            <h3>Penanggung Jawab</h3>
                            <form method="POST" action="{{ route('tickets.assignee.update', $ticket->id) }}" class="stack-form">
                                @csrf
                                @method('PATCH')
                                <label for="assigned_to">Tugaskan ke</label>
                                <select name="assigned_to" id="assigned_to">
                                    <option value="">Belum ditugaskan</option>
                                    @foreach($assignableUsers as $staff)
                                        <option value="{{ $staff->id }}" @selected((int) $ticket->assigned_to === $staff->id)>{{ $staff->name }} ({{ $staff->role }})</option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="field-error-text">{{ $message }}</div>
                                @enderror
                                <button type="submit">Simpan Penugasan</button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Ticket Form -->
    @can('delete', $ticket)
        <form method="POST" action="{{ route('tickets.destroy', $ticket->id) }}" id="delete-ticket-form" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endcan

    <!-- Confirm Delete Modal -->
    <div id="confirm-delete-modal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <div class="modal-title">Apakah Anda yakin?</div>
            <div class="modal-text">
                Anda yakin ingin menghapus <strong id="delete-item-name">tiket ini</strong>? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-actions">
                <button type="button" class="action-link ghost" onclick="closeDeleteModal()">Batal</button>
                <button type="button" class="action-link danger" onclick="submitDelete()">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // SLA countdown
        (function () {
            const el = document.getElementById('sla-countdown');
            if (!el) return;

            const deadline = new Date(el.dataset.deadline).getTime();
            const format = function (ms) {
                const minutes = Math.floor(ms / 60000);
                const days = Math.floor(minutes / 1440);
                const hours = Math.floor((minutes % 1440) / 60);
                const mins = minutes % 60;
                return (days ? days + 'h ' : '') + hours + 'j ' + mins + 'm';
            };
            const tick = function () {
                const diff = deadline - Date.now();
                if (diff >= 0) {
                    el.textContent = 'Sisa waktu ' + format(diff);
                    el.className = 'sla-countdown sla-status active';
                } else {
                    el.textContent = 'Terlambat ' + format(-diff);
                    el.className = 'sla-countdown sla-status breached';
                }
            };

            tick();
            setInterval(tick, 30000);
        })();

        // Message form loading spinner
        document.getElementById('message-form')?.addEventListener('submit', function () {
            const btn = document.getElementById('send-message-btn');
            const spinner = btn?.querySelector('.spinner');
            if (btn && spinner) {
                spinner.style.display = 'inline-block';
                btn.classList.add('loading');
            }
        });

        // Confirm delete modal
        let deleteFormId = null;

        function openDeleteModal(itemName, formId) {
            deleteFormId = formId;
            document.getElementById('delete-item-name').textContent = itemName;
            document.getElementById('confirm-delete-modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('confirm-delete-modal').style.display = 'none';
            document.body.style.overflow = '';
        }

        function submitDelete() {
            if (deleteFormId) {
                document.getElementById(deleteFormId).requestSubmit();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Close modal on overlay click
        document.getElementById('confirm-delete-modal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
