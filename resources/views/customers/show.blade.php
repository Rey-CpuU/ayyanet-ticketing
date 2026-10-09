<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('customers.index') }}" aria-label="Kembali ke daftar customer" class="flex h-8 w-8 items-center justify-center rounded-md border border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--muted)] transition hover:text-[var(--foreground)]">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[var(--accent-40)] bg-[var(--accent-soft)] text-[12px] font-semibold text-[var(--accent-text)]">
                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">{{ $customer->name }}</h2>
                        <p class="mt-0.5 font-mono text-[11.5px] text-[var(--muted)]">{{ $customer->customer_id }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('create', App\Models\Ticket::class)
                    <a href="{{ route('tickets.create', ['customer_id' => $customer->id]) }}" class="btn-primary">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                        Tiket Baru
                    </a>
                @endcan
                @can('update', $customer)
                    <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary">Edit</a>
                @endcan
                @can('delete', $customer)
                    <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                        onsubmit="return confirm({{ Js::from('Hapus customer '.$customer->name.'?') }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Hapus</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
            <div class="card h-fit overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Informasi Kontak</h3>
                </div>
                <dl class="divide-y divide-[var(--border)] text-[12.5px]">
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-[var(--muted)]">Email</dt>
                        <dd class="truncate text-[var(--foreground)]">{{ $customer->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-[var(--muted)]">No HP</dt>
                        <dd class="font-mono text-[var(--foreground)]">{{ $customer->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-[var(--muted)]">Paket</dt>
                        <dd><span class="badge border border-[var(--accent-30)] bg-[var(--accent-soft)] text-[var(--accent-text)]">{{ $customer->package ?? '—' }}</span></dd>
                    </div>
                    <div class="flex justify-between gap-3 px-5 py-3">
                        <dt class="text-[var(--muted)]">Terdaftar</dt>
                        <dd class="font-mono text-[var(--foreground)]">{{ $customer->created_at?->format('d M Y') ?? '—' }}</dd>
                    </div>
                </dl>
                @if ($customer->address)
                    <p class="border-t border-[var(--border)] px-5 py-4 text-[12px] leading-relaxed text-[var(--muted)]">{{ $customer->address }}</p>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center gap-2.5 border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Riwayat Tiket</h3>
                    <span class="rounded-full border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-[2px] font-mono text-[10.5px] text-[var(--muted)]">{{ $tickets->count() }}</span>
                </div>
                <div class="divide-y divide-[var(--border)]">
                    @forelse ($tickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket) }}" class="ticket-row-interactive flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                            <div class="min-w-0 flex-1">
                                <span class="font-mono text-[11px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                                <span class="ticket-title-link mt-0.5 block truncate text-[13px] font-medium text-[var(--foreground)]">{{ $ticket->title }}</span>
                                <span class="mt-0.5 block text-[11px] text-[var(--muted)]">{{ $ticket->assignee->name ?? 'Unassigned' }} · {{ $ticket->created_at?->format('d M Y') }}</span>
                            </div>
                            <div class="flex shrink-0 items-center gap-2.5">
                                <x-ticket-priority :priority="$ticket->priority" />
                                <x-ticket-status :status="$ticket->status" />
                            </div>
                        </a>
                    @empty
                        <p class="px-5 py-12 text-center text-[12.5px] font-medium text-[var(--muted)]">Belum ada tiket untuk customer ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
