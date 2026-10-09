{{-- In-app notification bell (database notifications) for the top navigation. --}}
<details class="nb relative" {{ $attributes }}>
    <summary class="nb-trigger relative inline-flex cursor-pointer list-none items-center justify-center rounded-md p-2 text-[var(--muted)] transition hover:bg-[var(--surface-2)] hover:text-[var(--foreground)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--accent)]"
        aria-label="Notifikasi{{ $unreadCount ? " ({$unreadCount} belum dibaca)" : '' }}" title="Notifikasi">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M8 1.5a3.5 3.5 0 00-3.5 3.5v2.2L3.2 8.7A1 1 0 004 10.2h8a1 1 0 00.8-1.5L11.5 7.2V5A3.5 3.5 0 008 1.5zM6 11.5a2 2 0 004 0" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="nb-count absolute -right-1 -top-1 min-w-[17px] rounded-full bg-[var(--red-solid)] px-1 text-center font-mono text-[10px] font-bold leading-[17px] text-white shadow-[var(--red-glow)]">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </summary>

    <div class="absolute right-0 top-[calc(100%+8px)] z-[1100] w-[min(340px,calc(100vw-32px))] overflow-hidden rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] text-left shadow-2xl" role="dialog" aria-label="Daftar notifikasi">
        <div class="flex items-center justify-between gap-2 border-b border-[var(--border)] px-4 py-2.5">
            <span class="text-xs font-semibold text-[var(--foreground)]">Notifikasi</span>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-[11px] font-semibold text-[var(--accent)] transition hover:text-[var(--accent-text)]">Tandai semua dibaca</button>
                </form>
            @else
                <span class="font-mono text-[10px] text-[var(--muted)]">0 unread</span>
            @endif
        </div>

        <ul class="max-h-80 divide-y divide-[var(--border)] overflow-y-auto">
            @forelse ($notifications as $notification)
                <li>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="block w-full px-4 py-2.5 text-left text-xs transition hover:bg-[var(--hover-overlay)] {{ $notification->read_at ? '' : 'bg-[var(--accent-soft)] shadow-[inset_3px_0_0_var(--accent)]' }}">
                            <span class="block text-[12.5px] leading-snug text-[var(--foreground)]">{{ $notification->data['message'] ?? 'Notifikasi' }}</span>
                            @if (! empty($notification->data['title']))
                                <span class="mt-0.5 block truncate text-[11px] text-[var(--muted)]">{{ \Illuminate\Support\Str::limit($notification->data['title'], 60) }}</span>
                            @endif
                            <span class="mt-0.5 block font-mono text-[10.5px] text-[var(--muted)]">{{ $notification->created_at?->diffForHumans() }}</span>
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-3 text-center text-xs text-[var(--muted)]">Tidak ada notifikasi baru</li>
            @endforelse
        </ul>
    </div>
</details>

@once
    <style>
        details.nb > summary::-webkit-details-marker { display: none; }
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
