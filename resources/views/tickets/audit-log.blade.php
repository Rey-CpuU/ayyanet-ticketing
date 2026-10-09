<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('tickets.show', $ticket) }}" aria-label="Kembali ke detail tiket" class="flex h-8 w-8 items-center justify-center rounded-md border border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--muted)] transition hover:text-[var(--foreground)]">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Audit Log</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]"><span class="font-mono text-[var(--accent)]">{{ $ticket->ticket_number }}</span> — {{ $ticket->title }}</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="card overflow-hidden">
            @forelse ($logs as $log)
                @php
                    $old = is_array($log->old_value) ? $log->old_value : (json_decode($log->old_value ?? '{}', true) ?: []);
                    $new = is_array($log->new_value) ? $log->new_value : (json_decode($log->new_value ?? '{}', true) ?: []);
                    $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
                @endphp
                <div class="flex items-start gap-3 border-b border-[var(--border-60)] px-5 py-4 last:border-0">
                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[10px] font-semibold text-[var(--foreground)]">
                        {{ strtoupper(substr($log->user?->name ?? 'SY', 0, 2)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-blue">{{ $log->action }}</span>
                            <span class="text-[12.5px] font-semibold text-[var(--foreground)]">{{ $log->user?->name ?? 'System' }}</span>
                            <span class="font-mono text-[10.5px] text-[var(--muted)]">{{ $log->created_at?->format('d M Y, H:i:s') ?? '-' }}</span>
                        </div>
                        @foreach ($fields as $field)
                            <p class="mt-1 text-[12px] text-[var(--muted)]">
                                <span class="font-semibold text-[var(--foreground)]">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                <span class="font-mono text-[var(--red-bright)]">{{ \Illuminate\Support\Str::limit((string) ($old[$field] ?? '-'), 120) }}</span>
                                <span class="mx-1 text-[var(--muted-strong)]">→</span>
                                <span class="font-mono text-[var(--green-text)]">{{ \Illuminate\Support\Str::limit((string) ($new[$field] ?? '-'), 120) }}</span>
                            </p>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="px-5 py-12 text-center text-[12.5px] font-medium text-[var(--muted)]">Belum ada aktivitas audit untuk tiket ini.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
