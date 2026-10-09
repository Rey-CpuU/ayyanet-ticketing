<x-app-layout>
    @php
        $hasFilters = collect($filters)->except('sort')->filter(fn ($value) => $value !== '')->isNotEmpty();
        $isMine = ! empty($showMyTicketsOnly);
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">{{ $isMine ? 'My Tickets' : 'Tickets' }}</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $tickets->total() }} tiket ditemukan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $isMine ? route('tickets.index') : route('my.tickets') }}" class="btn-secondary">
                    {{ $isMine ? 'Semua Tiket' : 'Tiket Saya' }}
                </a>
                <a href="{{ route('tickets.export.csv') }}" class="btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M8 2v8m0 0L5 7m3 3l3-3M2.5 12.5v1h11v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    CSV
                </a>
                <a href="{{ route('tickets.export.pdf') }}" target="_blank" rel="noopener" class="btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M8 2v8m0 0L5 7m3 3l3-3M2.5 12.5v1h11v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="card card-3d overflow-hidden rounded-lg">
            {{-- Filter bar --}}
            <form method="GET" action="{{ url()->current() }}" role="search" aria-label="Filter tiket"
                class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="min-w-[180px] flex-1">
                        <input type="search" name="q" value="{{ $filters['q'] }}" aria-label="Cari tiket"
                            placeholder="Cari nomor tiket, judul, atau customer..."
                            class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2.5 py-1.5 text-[12px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />
                    </div>

                    <x-custom-select name="status" :value="$filters['status']" placeholder="Semua Status" width="w-44"
                        :options="['' => 'Semua Status'] + array_combine(App\Models\Ticket::STATUSES, App\Models\Ticket::STATUSES)" />

                    <x-custom-select name="priority" :value="$filters['priority']" placeholder="Semua Prioritas" width="w-40"
                        :options="['' => 'Semua Prioritas', 'High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']" />

                    <x-custom-select name="category" :value="$filters['category']" placeholder="Semua Kategori" width="w-40"
                        :options="['' => 'Semua Kategori'] + $categories->mapWithKeys(fn ($c) => [$c => $c])->all()" />

                    <x-custom-select name="assigned_to" :value="$filters['assigned_to']" placeholder="Semua Penanggung Jawab" width="w-48"
                        :options="['' => 'Semua Penanggung Jawab', 'unassigned' => 'Unassigned'] + $assignees->mapWithKeys(fn ($a) => [(string) $a->id => $a->name])->all()" />

                    @if (auth()->user()->isAdmin())
                        <x-custom-select name="trashed" :value="$filters['trashed']" placeholder="Tiket aktif" width="w-44"
                            :options="['' => 'Tiket aktif', 'with' => 'Termasuk terhapus', 'only' => 'Hanya terhapus']" />
                    @endif

                    <x-custom-select name="sort" :value="$filters['sort']" placeholder="Urutkan" width="w-48"
                        :options="collect($sorts)->mapWithKeys(fn ($label, $key) => [$key => 'Urut: '.$label])->all()" />

                    <button type="submit" class="rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3.5 py-2 text-[12.5px] font-semibold text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)]">
                        Terapkan
                    </button>
                    @if ($hasFilters || $filters['sort'] !== 'newest')
                        <a href="{{ url()->current() }}" class="text-[11.5px] font-semibold text-[var(--accent)] transition hover:text-[var(--accent-text)]">Reset</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1040px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Ticket</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Customer</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Priority</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Status</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Assignee</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">SLA</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Messages</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse ($tickets as $ticket)
                            <tr class="transition hover:bg-[var(--hover-overlay)] {{ $ticket->trashed() ? 'opacity-70' : '' }}">
                                <td class="px-5 py-4">
                                    @if ($ticket->trashed())
                                        <span class="font-mono text-[11px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                                        <span class="mt-0.5 block max-w-[340px] truncate text-[13.5px] font-medium text-[var(--foreground)]">{{ $ticket->title }}</span>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                            <span class="badge badge-red">Terhapus</span>
                                            @can('restore', $ticket)
                                                <form method="POST" action="{{ route('tickets.restore', $ticket->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-[11.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Pulihkan</button>
                                                </form>
                                            @endcan
                                            @can('forceDelete', $ticket)
                                                <form method="POST" action="{{ route('tickets.force-delete', $ticket->id) }}"
                                                    onsubmit="return confirm({{ Js::from('Hapus permanen '.$ticket->ticket_number.'? Tindakan ini tidak dapat dibatalkan.') }})">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[11.5px] font-semibold text-[var(--red-bright)] hover:underline">Hapus permanen</button>
                                                </form>
                                            @endcan
                                        </div>
                                    @else
                                        <a href="{{ route('tickets.show', $ticket) }}" class="group block">
                                            <span class="font-mono text-[11px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                                            <span class="mt-0.5 block max-w-[340px] truncate text-[13.5px] font-medium text-[var(--foreground)] group-hover:text-[var(--accent-text)]">{{ $ticket->title }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-[13px] text-[var(--foreground)]">{{ $ticket->customer->name ?? 'Unknown' }}</div>
                                    <div class="text-[11.5px] text-[var(--muted)]">{{ $ticket->customer->phone ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4"><x-ticket-priority :priority="$ticket->priority" /></td>
                                <td class="px-5 py-4"><x-ticket-status :status="$ticket->status" /></td>
                                <td class="px-5 py-4">
                                    @if ($ticket->assignee)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-2 py-[3px]">
                                            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[8.5px] font-bold text-[var(--accent-text)]">{{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}</span>
                                            <span class="text-[11px] font-semibold text-[var(--accent-text-strong)]">{{ $ticket->assignee->name }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-dashed border-[var(--amber-text-40)] px-2 py-[3px] text-[10.5px] font-semibold uppercase tracking-[0.04em] text-[var(--amber-text)]">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <x-sla-indicator :ticket="$ticket" compact />
                                </td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--muted)]">{{ $ticket->messages_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-mono text-[11.5px] text-[var(--muted)]">{{ $ticket->created_at?->format('d M Y') }}</div>
                                    <div class="text-[10.5px] text-[var(--muted-strong)]">{{ $ticket->created_at?->format('H:i') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    @if ($hasFilters)
                                        <p class="text-[13.5px] font-medium text-[var(--muted)]">Tidak ada tiket yang cocok</p>
                                        <a href="{{ url()->current() }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Reset filter →</a>
                                    @else
                                        <p class="text-[13.5px] font-medium text-[var(--muted)]">{{ $isMine ? 'Anda belum memiliki tiket' : 'Belum ada tiket' }}</p>
                                        @can('create', App\Models\Ticket::class)
                                            <a href="{{ route('tickets.create') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Buat tiket pertama →</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tickets->hasPages())
                <div class="border-t border-[var(--border)] px-5 py-3">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
