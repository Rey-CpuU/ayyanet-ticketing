<x-app-layout>
    <style>
        @keyframes popoverFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
    </style>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Dashboard</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Ringkasan penempatan — setiap tiket butuh penanggung jawab</p>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]">
                {{ session('success') }}
            </div>
        @endif

        {{-- Operational status strip --}}
        <div class="mb-5 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
            <div class="fx-chip border-[var(--amber-text-40)] bg-[var(--amber-text-07)] text-[var(--amber-text)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 1.5l1.6 4.9 5.1.1-4 3.1 1.5 4.9-4.2-2.9-4.2 2.9 1.5-4.9-4-3.1 5.1-.1L8 1.5z" fill="currentColor" opacity=".9" />
                </svg>
                <span>{{ $stats['unassigned'] }} belum ditugaskan</span>
                <span class="hidden text-[10.5px] font-medium text-[var(--amber-text-60)] sm:inline">— perlu penempatan</span>
            </div>
            <div class="fx-chip border-[var(--red-text-40)] bg-[var(--red-solid-07)] text-[var(--red-bright)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 10V4M8 12.5v.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3" opacity=".55" />
                </svg>
                <span>{{ $stats['high'] }} prioritas tinggi</span>
            </div>
            <div class="fx-chip border-[var(--green-text-30)] bg-[var(--green-text-06)] text-[var(--green-text)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2.5 8.5l3.5 3.5 7.5-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>{{ $stats['solved_week'] }} selesai</span>
                <span class="hidden text-[10.5px] font-medium text-[var(--green-text-60)] sm:inline">minggu ini</span>
            </div>
        </div>

        {{-- Status stat cards --}}
        @php
            $statCards = [
                ['label' => 'Total', 'value' => $stats['total'], 'textClass' => 'text-[var(--foreground)]', 'dotClass' => 'bg-[var(--foreground)]', 'hint' => 'Semua tiket'],
                ['label' => 'Open', 'value' => $stats['open'], 'textClass' => 'text-[var(--red-bright)]', 'dotClass' => 'bg-[var(--red-bright)]', 'hint' => 'Perlu respon'],
                ['label' => 'Checking', 'value' => $stats['checking'], 'textClass' => 'text-[var(--violet-text)]', 'dotClass' => 'bg-[var(--violet-text)]', 'hint' => 'Sedang dicek'],
                ['label' => 'Waiting', 'value' => $stats['waiting'], 'textClass' => 'text-[var(--amber-text)]', 'dotClass' => 'bg-[var(--amber-text)]', 'hint' => 'Menunggu customer'],
                ['label' => 'Escalated', 'value' => $stats['escalated'], 'textClass' => 'text-[var(--orange-text)]', 'dotClass' => 'bg-[var(--orange-text)]', 'hint' => 'Tier lebih tinggi'],
                ['label' => 'Solved', 'value' => $stats['solved'], 'textClass' => 'text-[var(--green-text)]', 'dotClass' => 'bg-[var(--green-text)]', 'hint' => 'Terselesaikan'],
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($statCards as $card)
                <div class="card rounded-lg p-4">
                    <div class="mb-2 flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full {{ $card['dotClass'] }}"></span>
                        <span class="truncate text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">{{ $card['label'] }}</span>
                    </div>
                    <div class="fx-glow-text font-display text-[24px] font-bold leading-none {{ $card['textClass'] }}">{{ $card['value'] }}</div>
                    <div class="mt-1.5 text-[11px] text-[var(--muted)]">{{ $card['hint'] }}</div>
                </div>
            @endforeach
        </div>

        @php
            $pageTicketCount = $tickets->count();
            $pageUnassigned = $tickets->whereNull('assigned_to')->count();
            $pageMine = $tickets->where('assigned_to', auth()->id())->count();
            $canAssign = $assignableUsers->isNotEmpty();
        @endphp

        {{-- Ticket queue + side panel --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_400px]">
            <div class="card overflow-hidden rounded-lg"
                x-data="ticketQueue({
                    unassigned: {{ $pageUnassigned }},
                    mine: {{ $pageMine }},
                    all: {{ $pageTicketCount }},
                    userId: {{ (int) auth()->id() }},
                    assignUrl: @js(url('tickets')),
                })">
                <div class="flex flex-col gap-3 border-b border-[var(--border)] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Ticket Queue</h3>
                        <span class="rounded-full border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-[2px] font-mono text-[10.5px] text-[var(--muted)]">{{ $tickets->total() }} total</span>
                    </div>

                    {{-- Queue tabs with sliding pill --}}
                    <div class="relative flex overflow-x-auto rounded-full border border-[var(--border-strong)] bg-[var(--surface)] p-1">
                        <div class="absolute top-1 bottom-1 rounded-full bg-gradient-to-r from-[var(--accent-cta)] to-[var(--accent-cta-hover)] shadow-[0_0_16px_var(--glow-accent)] transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)]"
                            :style="{
                                left: (tab === 'unassigned' ? $refs.tabUnassigned : (tab === 'mine' ? $refs.tabMine : $refs.tabAll))?.offsetLeft + 'px',
                                width: (tab === 'unassigned' ? $refs.tabUnassigned : (tab === 'mine' ? $refs.tabMine : $refs.tabAll))?.offsetWidth + 'px'
                            }"
                            aria-hidden="true">
                        </div>

                        <button type="button" x-ref="tabUnassigned" @click="setTab('unassigned')"
                            :class="tab === 'unassigned' ? 'text-white' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="relative z-10 flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition-colors duration-200">
                            Unassigned
                            <span class="font-mono text-[10.5px] opacity-80" x-text="unassignedCount">{{ $pageUnassigned }}</span>
                        </button>
                        <button type="button" x-ref="tabMine" @click="setTab('mine')"
                            :class="tab === 'mine' ? 'text-white' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="relative z-10 flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition-colors duration-200">
                            Mine
                            <span class="font-mono text-[10.5px] opacity-80" x-text="mineCount">{{ $pageMine }}</span>
                        </button>
                        <button type="button" x-ref="tabAll" @click="setTab('all')"
                            :class="tab === 'all' ? 'text-white' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                            class="relative z-10 flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition-colors duration-200">
                            All
                            <span class="font-mono text-[10.5px] opacity-80">{{ $pageTicketCount }}</span>
                        </button>
                    </div>
                </div>

                {{-- Filter & live search bar --}}
                <div class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5"
                    x-data="liveTicketSearch(@js($filters['search']), @js(route('tickets.live-search')))"
                    @click.outside="isOpen = false">
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                        <div class="relative min-w-[200px] flex-1">
                            <div class="relative">
                                <input type="text"
                                    name="search"
                                    x-model="query"
                                    @input.debounce.250ms="doLiveSearch()"
                                    @focus="if (liveResults.length > 0) isOpen = true"
                                    @keydown.escape="isOpen = false"
                                    autocomplete="off"
                                    aria-label="Cari tiket"
                                    placeholder="Live search: nomor (TKT-...), nama customer, no HP, judul..."
                                    class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] pl-8 pr-8 py-1.5 text-[12.5px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />

                                <svg class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-[var(--muted)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>

                                <div x-show="isLoading" class="absolute right-2.5 top-1/2 -translate-y-1/2" x-cloak>
                                    <svg class="h-3.5 w-3.5 animate-spin text-[var(--accent)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            {{-- Live search dropdown --}}
                            <div x-show="isOpen && query.trim().length > 0"
                                x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute left-0 right-0 top-full z-50 mt-1.5 max-h-80 overflow-y-auto rounded-lg border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl backdrop-blur-md">

                                <div class="flex items-center justify-between border-b border-[var(--border)] bg-[var(--surface)] px-3.5 py-2 text-[11px] font-semibold text-[var(--muted)]">
                                    <span>Hasil Pencarian Live (<span x-text="liveResults.length"></span>)</span>
                                    <span class="text-[10px] text-[var(--muted-strong)]">ESC untuk menutup</span>
                                </div>

                                <template x-if="liveResults.length === 0 && !isLoading">
                                    <div class="px-4 py-6 text-center text-[12.5px] text-[var(--muted)]">
                                        Tidak ada tiket ditemukan untuk "<span class="font-semibold text-[var(--foreground)]" x-text="query"></span>"
                                    </div>
                                </template>

                                <template x-for="item in liveResults" :key="item.id">
                                    <a :href="item.url" class="group flex items-start gap-3 border-b border-[var(--border-60)] p-3 transition hover:bg-[var(--hover-overlay)] last:border-b-0">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-bold text-[var(--accent-text-strong)]" x-text="item.initial"></div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                                <span class="font-mono text-[11.5px] font-bold text-[var(--accent)]" x-text="item.ticket_number"></span>
                                                <span class="text-[var(--border-strong)]">·</span>
                                                <span class="truncate text-[12.5px] font-semibold text-[var(--foreground)]" x-text="item.customer_name"></span>
                                                <template x-if="item.customer_phone">
                                                    <span class="inline-flex items-center gap-1 rounded border border-[var(--border-strong)] bg-[var(--surface-3)] px-1.5 py-[1px] font-mono text-[11px] text-[var(--muted-strong)]">
                                                        <svg class="h-3 w-3 shrink-0 text-[var(--muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                                        </svg>
                                                        <span x-text="item.customer_phone"></span>
                                                    </span>
                                                </template>
                                                <span class="text-[10.5px] text-[var(--muted)]" x-text="item.category ? '· ' + item.category : ''"></span>
                                            </div>

                                            <p class="mt-1 truncate text-[13px] font-medium text-[var(--foreground)] group-hover:text-[var(--accent-text)]" x-text="item.title"></p>

                                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-[10.5px] text-[var(--muted)]">
                                                <span class="rounded border border-[var(--border-strong)] bg-[var(--surface-3)] px-1.5 py-0.5 font-medium" x-text="'Status: ' + item.status"></span>
                                                <span class="rounded border border-[var(--border-strong)] bg-[var(--surface-3)] px-1.5 py-0.5 font-medium" x-text="'Priority: ' + item.priority"></span>
                                                <span class="ml-auto font-mono text-[10px]" x-text="item.created_at"></span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <x-custom-select
                            name="status"
                            :value="$filters['status']"
                            placeholder="Semua Status"
                            :options="['' => 'Semua Status'] + array_combine(App\Models\Ticket::STATUSES, App\Models\Ticket::STATUSES)"
                            width="w-48" />

                        <x-custom-select
                            name="priority"
                            :value="$filters['priority']"
                            placeholder="Semua Prioritas"
                            :options="['' => 'Semua Prioritas', 'High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low']"
                            width="w-44" />

                        <div class="w-full sm:w-auto">
                            <button type="submit" class="w-full rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3.5 py-2 text-[12.5px] font-semibold text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)] sm:w-auto">
                                Filter
                            </button>
                        </div>
                        @if (collect($filters)->filter(fn ($v) => $v !== '')->isNotEmpty())
                            <a href="{{ route('dashboard') }}" class="text-[12px] font-semibold text-[var(--accent)] transition hover:text-[var(--accent-text)]">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="max-h-[calc(100vh-320px)] overflow-y-auto">
                    @forelse ($tickets as $ticket)
                        @php
                            $tabKey = $ticket->assigned_to === null
                                ? 'unassigned'
                                : ((int) $ticket->assigned_to === (int) auth()->id() ? 'mine' : 'all');
                        @endphp
                        <div class="ticket-row-interactive border-b border-[var(--border)] px-4 py-3.5 sm:px-5"
                            x-data="ticketRow({
                                id: {{ $ticket->id }},
                                tabKey: @js($tabKey),
                                status: @js($ticket->status),
                                title: @js($ticket->title),
                                priority: @js($ticket->priority),
                                category: @js($ticket->category ?? ''),
                            })"
                            @ticket-updated.window="applyUpdate($event.detail)"
                            x-show="tab === 'all' || tab === rowTabKey">
                            <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-2">
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                        {{ strtoupper(substr($ticket->customer->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <button type="button" @click="$dispatch('open-ticket-modal', { ticketId: {{ $ticket->id }} })" class="ticket-title-link block truncate text-left text-[13.5px] font-medium text-[var(--foreground)] transition-colors duration-200" x-text="rowTitle">{{ $ticket->title }}</button>

                                        <div class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11.5px] text-[var(--muted)]">
                                            <span class="truncate">{{ $ticket->customer->name ?? 'Unknown' }}</span>
                                            <a href="{{ route('tickets.show', $ticket) }}" class="font-mono text-[10.5px] hover:text-[var(--accent)]">{{ $ticket->ticket_number }}</a>
                                            <span class="text-[var(--border-strong)]">·</span>

                                            @if ($canAssign)
                                                <div class="inline-flex">
                                                    <select name="assigned_to"
                                                        aria-label="Penanggung jawab {{ $ticket->ticket_number }}"
                                                        data-current-assignee="{{ $ticket->assigned_to ?? '' }}"
                                                        @change="assignTicket({{ $ticket->id }}, $el, $data)"
                                                        class="select-chip inline-flex items-center gap-1.5 rounded-full border text-[13px] font-semibold transition
                                                            {{ $ticket->assigned_to === null
                                                                ? 'border-dashed border-[var(--amber-text-50)] bg-[var(--amber-text-06)] text-[var(--amber-text)] fx-unassigned-pulse'
                                                                : 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--foreground)]' }}">
                                                        <option value="" @selected($ticket->assigned_to === null)>Unassigned</option>
                                                        @foreach ($assignableUsers as $agent)
                                                            <option value="{{ $agent->id }}" @selected((int) $ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @elseif ($ticket->assignee)
                                                <span class="rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-2 py-[1px] text-[11px] font-semibold text-[var(--foreground)]">{{ $ticket->assignee->name }}</span>
                                            @else
                                                <span class="rounded-full border border-dashed border-[var(--amber-text-40)] px-2 py-[1px] text-[10.5px] font-semibold text-[var(--amber-text)]">Unassigned</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                                    <span class="badge" :class="statusBadgeClass(rowStatus)" x-text="rowStatus">{{ $ticket->status }}</span>
                                </div>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px]">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full" :class="'priority-dot-' + priorityKey(rowPriority)"></span>
                                    <span class="font-mono uppercase tracking-[0.04em]" :class="'priority-' + priorityKey(rowPriority)" x-text="rowPriority">{{ $ticket->priority }}</span>
                                </span>
                                <template x-if="rowCategory">
                                    <span class="flex items-center gap-2.5">
                                        <span class="text-[var(--border-strong)]">·</span>
                                        <span class="text-[var(--muted)]" x-text="rowCategory">{{ $ticket->category }}</span>
                                    </span>
                                </template>
                                <x-sla-indicator :ticket="$ticket" compact />
                                <span class="ml-auto font-mono text-[var(--muted)]">{{ $ticket->created_at?->diffForHumans() }}</span>
                                @if ($ticket->messages_count > 0)
                                    <span class="flex items-center gap-1 font-mono text-[var(--muted)]">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M2 4a2 2 0 012-2h8a2 2 0 012 2v5a2 2 0 01-2 2H6l-3 3v-3H4a2 2 0 01-2-2V4z" fill="currentColor" opacity=".6" />
                                        </svg>
                                        {{ $ticket->messages_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-16 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[var(--surface-3)]">
                                <svg width="22" height="22" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <rect x="1" y="2.5" width="14" height="11" rx="2" stroke="var(--muted)" stroke-width="1.3" />
                                    <path d="M1 5l7-3 7 3" stroke="var(--muted)" stroke-width="1.3" />
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-medium text-[var(--muted)]">Belum ada tiket</p>
                            @can('create', App\Models\Ticket::class)
                                <a href="{{ route('tickets.create') }}" class="mt-3 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Buat tiket pertama →</a>
                            @endcan
                        </div>
                    @endforelse

                    {{-- Per-tab empty states --}}
                    <div class="px-5 py-10 text-center" x-show="tab === 'unassigned' && unassignedCount === 0" x-cloak>
                        <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[var(--green-text-08)]">
                            <svg class="h-4 w-4 text-[var(--green-text)]" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M2.5 8.5l3.5 3.5 7.5-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <p class="text-[13px] font-medium text-[var(--foreground)]">Semua tiket sudah punya penanggung jawab</p>
                        <p class="mt-0.5 text-[12px] text-[var(--muted)]">Tidak ada yang menunggu penempatan.</p>
                    </div>

                    <div class="px-5 py-10 text-center" x-show="tab === 'mine' && mineCount === 0" x-cloak>
                        <p class="text-[13px] font-medium text-[var(--foreground)]">Belum ada tiket yang ditugaskan ke Anda</p>
                        <p class="mt-0.5 text-[12px] text-[var(--muted)]">Ambil satu dari tab Unassigned.</p>
                    </div>

                    @if ($tickets->hasPages())
                        <div class="border-t border-[var(--border)] px-5 py-3">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recently visited --}}
            <div class="card h-fit overflow-hidden rounded-lg">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Recently Visited</h3>
                </div>
                <div class="divide-y divide-[var(--border)]">
                    @forelse ($recentTickets as $ticket)
                        <div class="ticket-row-interactive flex items-center justify-between px-5 py-3"
                            data-recent-ticket="{{ $ticket->id }}"
                            x-data="{ rowTitle: @js($ticket->title) }"
                            @ticket-updated.window="if ($event.detail.id === {{ $ticket->id }} && $event.detail.title) rowTitle = $event.detail.title">
                            <div class="min-w-0 flex-1 cursor-pointer" @click="$dispatch('open-ticket-modal', { ticketId: {{ $ticket->id }} })">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-[11px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                                    <span class="font-mono text-[10.5px] text-[var(--muted)]">{{ ($ticket->last_visited_at ?? $ticket->created_at)?->diffForHumans() }}</span>
                                </div>
                                <div class="ticket-title-link mt-1 truncate text-[12.5px] font-medium text-[var(--foreground)] transition-colors duration-200" x-text="rowTitle">{{ $ticket->title }}</div>
                                <div class="mt-0.5 flex items-center gap-1.5 text-[11px] text-[var(--muted)]">
                                    <span>{{ $ticket->customer->name ?? 'Unknown' }}</span>
                                    @if ($ticket->assignee)
                                        <span class="flex items-center gap-1 rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-1.5 py-[1px] text-[10px] font-semibold text-[var(--accent-text-strong)]">
                                            {{ strtoupper(substr($ticket->assignee->name, 0, 2)) }} · {{ $ticket->assignee->name }}
                                        </span>
                                    @else
                                        <span class="rounded-full border border-dashed border-[var(--amber-text-40)] px-1.5 py-[1px] text-[10px] font-semibold text-[var(--amber-text)]">unassigned</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Floating quick chat popover --}}
                            <div class="relative ml-3 shrink-0"
                                x-data="quickChatPopover({{ $ticket->id }}, @js($ticket->ticket_number), @js($ticket->title))"
                                @open-quick-chat.window="if ($event.detail.ticketId !== ticketId) open = false">
                                <button type="button"
                                    @click.stop="toggle()"
                                    class="rounded-md border border-[var(--border-strong)] bg-[var(--surface-3)] p-1.5 text-[var(--muted)] transition hover:bg-[var(--accent)] hover:text-white"
                                    :class="open ? 'bg-[var(--accent)] text-white border-[var(--accent)]' : ''"
                                    title="Quick Chat & Reply"
                                    aria-label="Quick chat {{ $ticket->ticket_number }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                    </svg>
                                </button>

                                <template x-teleport="body">
                                    <div x-show="open"
                                        x-cloak
                                        @click.away="open = false"
                                        @keydown.escape.window="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                                        style="z-index: 9999; animation: popoverFloat 3s ease-in-out infinite;"
                                        class="fixed bottom-[calc(5.5rem+env(safe-area-inset-bottom))] right-4 z-50 w-96 md:bottom-6 md:right-6 max-w-[calc(100vw-2rem)] rounded-2xl border border-[var(--border-strong)] bg-[var(--surface-2)] p-4 text-[var(--foreground)] shadow-2xl">

                                        <div class="mb-2.5 flex items-center justify-between border-b border-[var(--border)] pb-2.5">
                                            <div class="flex min-w-0 items-center gap-2">
                                                <span class="font-mono text-xs font-bold text-[var(--accent)]" x-text="ticketNumber"></span>
                                                <span class="max-w-[200px] truncate text-xs font-semibold" x-text="ticketTitle"></span>
                                            </div>
                                            <button @click="open = false" type="button" class="rounded bg-[var(--surface-3)] px-2 py-1 text-xs text-[var(--muted)] transition hover:text-[var(--foreground)]" aria-label="Tutup">✕</button>
                                        </div>

                                        <div class="max-h-60 space-y-2.5 overflow-y-auto p-1 pr-1 text-[12px]" :id="'popover-chat-' + ticketId">
                                            <template x-if="loading">
                                                <div class="flex items-center justify-center gap-2 py-8 text-center text-xs text-[var(--muted)]">
                                                    <svg class="h-4 w-4 animate-spin text-[var(--accent)]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    <span>Memuat percakapan...</span>
                                                </div>
                                            </template>
                                            <template x-if="!loading && error">
                                                <div class="py-8 text-center text-xs text-[var(--red-bright)]" x-text="error"></div>
                                            </template>
                                            <template x-if="!loading && !error && messages.length === 0">
                                                <div class="py-8 text-center text-xs text-[var(--muted)]">Belum ada pesan dalam tiket ini.</div>
                                            </template>
                                            <template x-for="msg in messages" :key="msg.id">
                                                <div class="rounded-lg border p-2.5 text-[12px]" :class="msg.is_internal ? 'border-[var(--amber-text-40)] bg-[var(--amber-text-07)]' : 'border-[var(--border)] bg-[var(--surface-3)]'">
                                                    <div class="mb-1 flex items-center justify-between text-[10.5px] text-[var(--muted)]">
                                                        <span class="font-bold text-[var(--accent)]" x-text="msg.user_name + (msg.is_internal ? ' · Internal' : '')"></span>
                                                        <span class="font-mono text-[10px]" x-text="msg.created_at"></span>
                                                    </div>
                                                    <p class="whitespace-pre-wrap leading-relaxed" x-text="msg.message"></p>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-3 border-t border-[var(--border)] pt-2.5" x-show="canReply">
                                            <div class="mb-2 flex items-center justify-between" x-show="canInternal">
                                                <label :for="'popover_internal_' + ticketId" class="flex cursor-pointer items-center gap-1.5 text-[11px] text-[var(--muted)] transition hover:text-[var(--foreground)]">
                                                    <input type="checkbox" :id="'popover_internal_' + ticketId" x-model="isInternal" class="rounded border-[var(--border-strong)] bg-[var(--surface)] text-[var(--amber-text)] focus:ring-0">
                                                    <span>Catatan Internal</span>
                                                </label>
                                            </div>
                                            <div class="flex gap-2">
                                                <textarea :id="'popover_reply_' + ticketId"
                                                    aria-label="Tulis balasan tiket"
                                                    x-model="newMessage"
                                                    rows="2"
                                                    maxlength="2000"
                                                    @keydown="if ($event.key === 'Enter' && !$event.shiftKey) { $event.preventDefault(); if (newMessage.trim() && !sending) sendMessage(); }"
                                                    placeholder="Tulis balasan... (Enter kirim, Shift+Enter baris baru)"
                                                    class="max-h-24 flex-1 resize-none rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1.5 text-xs text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none"></textarea>
                                                <button type="button"
                                                    @click="sendMessage()"
                                                    :disabled="sending || !newMessage.trim()"
                                                    class="rounded-lg bg-[var(--accent)] px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[var(--accent-hover)] disabled:opacity-50">
                                                    <span x-show="!sending">Kirim</span>
                                                    <span x-show="sending">...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-[12.5px] text-[var(--muted)]">Belum ada tiket yang dikunjungi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-ticket-modal />

    <script>
        function ticketQueue(config) {
            const read = function () { try { return localStorage.getItem('ayyanet_ticket_queue_tab'); } catch (e) { return null; } };
            return {
                tab: read() || 'unassigned',
                unassignedCount: config.unassigned,
                mineCount: config.mine,
                currentUserId: config.userId,
                setTab(t) {
                    this.tab = t;
                    try { localStorage.setItem('ayyanet_ticket_queue_tab', t); } catch (e) {}
                },
                async assignTicket(ticketId, selectEl, rowData) {
                    const newId = selectEl.value ? parseInt(selectEl.value, 10) : null;
                    const oldId = selectEl.dataset.currentAssignee ? parseInt(selectEl.dataset.currentAssignee, 10) : null;
                    const chip = function (assigned) {
                        selectEl.className = 'select-chip inline-flex items-center gap-1.5 rounded-full border text-[13px] font-semibold transition ' + (assigned
                            ? 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--foreground)]'
                            : 'border-dashed border-[var(--amber-text-50)] bg-[var(--amber-text-06)] text-[var(--amber-text)] fx-unassigned-pulse');
                    };
                    chip(newId !== null);

                    try {
                        const res = await fetch(config.assignUrl + '/' + ticketId + '/assignee', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ assigned_to: newId }),
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);

                        selectEl.dataset.currentAssignee = newId ?? '';
                        if (rowData) {
                            rowData.rowTabKey = newId === null ? 'unassigned' : (newId === this.currentUserId ? 'mine' : 'all');
                        }
                        if (oldId === null && newId !== null) this.unassignedCount = Math.max(0, this.unassignedCount - 1);
                        if (oldId !== null && newId === null) this.unassignedCount++;
                        if (oldId === this.currentUserId && newId !== this.currentUserId) this.mineCount = Math.max(0, this.mineCount - 1);
                        if (oldId !== this.currentUserId && newId === this.currentUserId) this.mineCount++;
                    } catch (e) {
                        selectEl.value = oldId ?? '';
                        chip(oldId !== null);
                    }
                },
            };
        }

        function ticketRow(row) {
            return {
                rowTabKey: row.tabKey,
                rowStatus: row.status,
                rowTitle: row.title,
                rowPriority: row.priority,
                rowCategory: row.category,
                applyUpdate(detail) {
                    if (detail.id !== row.id) return;
                    if (detail.status) this.rowStatus = detail.status;
                    if (detail.title) this.rowTitle = detail.title;
                    if (detail.priority) this.rowPriority = detail.priority;
                    if (detail.category !== undefined) this.rowCategory = detail.category || '';
                },
                statusBadgeClass(st) {
                    return {
                        'Open': 'badge-red',
                        'Checking': 'badge-violet',
                        'Waiting Customer': 'badge-amber',
                        'Escalated': 'badge-orange',
                        'Solved': 'badge-green',
                    }[st] || 'badge-slate';
                },
                priorityKey(prio) {
                    return prio === 'High' ? 'high' : (prio === 'Medium' ? 'medium' : 'default');
                },
            };
        }

        function liveTicketSearch(initial, url) {
            return {
                query: initial || '',
                liveResults: [],
                isLoading: false,
                isOpen: false,
                controller: null,
                async doLiveSearch() {
                    const q = this.query.trim();
                    if (q.length === 0) {
                        this.liveResults = [];
                        this.isOpen = false;
                        return;
                    }
                    if (this.controller) this.controller.abort();
                    this.controller = new AbortController();
                    this.isLoading = true;
                    try {
                        const res = await fetch(url + '?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json' },
                            signal: this.controller.signal,
                        });
                        if (res.ok) {
                            this.liveResults = await res.json();
                            this.isOpen = true;
                        }
                    } catch (e) {
                        // aborted or offline: keep the previous results
                    } finally {
                        this.isLoading = false;
                    }
                },
            };
        }

        function quickChatPopover(ticketId, ticketNumber, ticketTitle) {
            return {
                open: false,
                loading: false,
                error: '',
                ticketId: ticketId,
                ticketNumber: ticketNumber || '',
                ticketTitle: ticketTitle || '',
                messages: [],
                newMessage: '',
                isInternal: false,
                canReply: false,
                canInternal: false,
                sending: false,

                toggle() {
                    if (this.open) {
                        this.open = false;
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('open-quick-chat', { detail: { ticketId: this.ticketId } }));
                    this.open = true;
                    this.loadMessages();
                },

                async loadMessages() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch(@js(url('tickets')) + '/' + this.ticketId + '/quick-details', {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();
                        this.messages = data.messages || [];
                        this.canReply = !!(data.can && data.can.reply);
                        this.canInternal = !!(data.can && data.can.internal_note);
                        this.scrollToBottom();
                    } catch (e) {
                        this.error = 'Gagal memuat percakapan.';
                    } finally {
                        this.loading = false;
                    }
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || this.sending) return;
                    this.sending = true;
                    try {
                        const res = await fetch(@js(url('tickets')) + '/' + this.ticketId + '/quick-message', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ message: this.newMessage, is_internal: this.isInternal ? 1 : 0 }),
                        });
                        const data = await res.json();
                        if (res.ok && data.success && data.message) {
                            this.messages.push(data.message);
                            this.newMessage = '';
                            this.scrollToBottom();
                        }
                    } catch (e) {
                        console.error('Failed to send message:', e);
                    } finally {
                        this.sending = false;
                    }
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = document.getElementById('popover-chat-' + this.ticketId);
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },
            };
        }
    </script>
</x-app-layout>
