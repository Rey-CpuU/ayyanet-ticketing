{{-- In-app notification bell. Self-contained styles so it works on the standalone pages and in the app layout. --}}
<details class="nb" {{ $attributes }}>
    <summary class="nb-trigger" aria-label="Notifikasi{{ $unreadCount ? " ({$unreadCount} belum dibaca)" : '' }}" title="Notifikasi">
        <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2.5a5.5 5.5 0 0 0-5.5 5.5v3.2L3 13.5v1h14v-1l-1.5-2.3V8a5.5 5.5 0 0 0-5.5-5.5Zm-4 9.5v-4a4 4 0 1 1 8 0v4l1 1.5H5l1-1.5Zm3 4.5a1 1 0 0 0 2 0H9Z"/></svg>
        @if($unreadCount > 0)
            <span class="nb-count">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </summary>

    <div class="nb-panel" role="dialog" aria-label="Daftar notifikasi">
        <div class="nb-head">
            <strong>Notifikasi</strong>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="nb-link">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <ul class="nb-list">
            @forelse($notifications as $notification)
                <li class="{{ $notification->read_at ? '' : 'nb-unread' }}">
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="nb-item">
                            <span class="nb-message">{{ $notification->data['message'] ?? 'Notifikasi' }}</span>
                            @if(! empty($notification->data['title']))
                                <span class="nb-meta">{{ \Illuminate\Support\Str::limit($notification->data['title'], 60) }}</span>
                            @endif
                            <span class="nb-meta">{{ $notification->created_at?->diffForHumans() }}</span>
                        </button>
                    </form>
                </li>
            @empty
                <li class="nb-empty">Belum ada notifikasi.</li>
            @endforelse
        </ul>
    </div>
</details>

@once
    <style>
        .nb { position: relative; display: inline-block; font-family: inherit; }
        .nb > summary { list-style: none; }
        .nb > summary::-webkit-details-marker { display: none; }
        .nb-trigger {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(148, 163, 184, 0.08);
            color: inherit;
            cursor: pointer;
        }
        .nb-trigger:focus-visible { outline: 2px solid #8b5cf6; outline-offset: 2px; }
        .nb-count {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
        }
        .nb-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            z-index: 1100;
            width: min(340px, calc(100vw - 32px));
            background: #111827;
            color: #e5e7eb;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            text-align: left;
        }
        .nb-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
            font-size: 14px;
        }
        .nb-head form { margin: 0; }
        .nb-link {
            background: none;
            border: none;
            padding: 0;
            width: auto;
            margin: 0;
            color: #c4b5fd;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .nb-list { list-style: none; margin: 0; padding: 0; max-height: 360px; overflow-y: auto; }
        .nb-list li { border-bottom: 1px solid rgba(148, 163, 184, 0.08); }
        .nb-list li:last-child { border-bottom: none; }
        .nb-list form { margin: 0; display: block; }
        .nb-item {
            display: block;
            width: 100%;
            margin: 0;
            padding: 10px 14px;
            border: none;
            border-radius: 0;
            background: none;
            color: inherit;
            font: inherit;
            font-weight: 400;
            text-align: left;
            cursor: pointer;
        }
        .nb-item:hover { background: rgba(139, 92, 246, 0.1); }
        .nb-unread .nb-item { background: rgba(139, 92, 246, 0.08); box-shadow: inset 3px 0 0 #8b5cf6; }
        .nb-message { display: block; font-size: 13px; line-height: 1.4; }
        .nb-meta { display: block; margin-top: 2px; font-size: 11px; color: #94a3b8; }
        .nb-empty { padding: 18px 14px; font-size: 13px; color: #94a3b8; }
    </style>
    <script>
        // Close the notification panel when clicking outside of it.
        document.addEventListener('click', function (e) {
            document.querySelectorAll('details.nb[open]').forEach(function (el) {
                if (!el.contains(e.target)) el.removeAttribute('open');
            });
        });
    </script>
@endonce
