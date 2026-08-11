<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Dashboard</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Placement overview — every ticket needs a responsible agent</p>
            </div>
            <a href="{{ route('tickets.create') }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                New Ticket
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]">
                {{ session('success') }}
            </div>
        @endif

        {{-- Operational impact strip --}}
        <div class="mb-5 grid grid-cols-1 gap-2.5 sm:grid-cols-3">
            <div class="fx-chip border-[var(--amber-text-40)] bg-[var(--amber-text-07)] text-[var(--amber-text)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 1.5l1.6 4.9 5.1.1-4 3.1 1.5 4.9-4.2-2.9-4.2 2.9 1.5-4.9-4-3.1 5.1-.1L8 1.5z" fill="currentColor" opacity=".9"/>
                </svg>
                <span>{{ $stats['unassigned'] }} unassigned</span>
                <span class="hidden text-[10.5px] font-medium text-[var(--amber-text-60)] sm:inline">— needs placement</span>
            </div>
            <div class="fx-chip border-[var(--red-text-40)] bg-[var(--red-solid-07)] text-[var(--red-bright)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 10V4M8 12.5v.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.3" opacity=".55"/>
                </svg>
                <span>{{ $stats['critical'] }} critical impact</span>
            </div>
            <div class="fx-chip border-[var(--green-text-30)] bg-[var(--green-text-06)] text-[var(--green-text)]">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M2.5 8.5l3.5 3.5 7.5-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>{{ $stats['solved'] }} solved</span>
                <span class="hidden text-[10.5px] font-medium text-[var(--green-text-60)] sm:inline">this week</span>
            </div>
        </div>

        {{-- Status stat cards --}}
        @php
            $statCards = [
                ['label' => 'Open', 'value' => $stats['open'], 'hint' => 'needs attention', 'color' => 'var(--red-text)'],
                ['label' => 'Checking', 'value' => $stats['checking'], 'hint' => 'being handled', 'color' => 'var(--violet-text)'],
                ['label' => 'Waiting Customer', 'value' => $stats['waiting'], 'hint' => 'awaiting reply', 'color' => 'var(--amber-text)'],
                ['label' => 'Solved', 'value' => $stats['solved'], 'hint' => 'resolved', 'color' => 'var(--green-text)'],
                ['label' => 'Escalated', 'value' => $stats['escalated'], 'hint' => 'urgent attention', 'color' => 'var(--orange-text)'],
                ['label' => 'Closed', 'value' => $stats['closed'], 'hint' => 'closed', 'color' => 'var(--slate-text)'],
            ];
            $impactBadge = [
                'Critical' => ['bg' => 'color-mix(in srgb, var(--red-text) 14%, transparent)', 'text' => 'var(--red-text)', 'glow' => 'var(--red-glow)'],
                'High'     => ['bg' => 'color-mix(in srgb, var(--amber-text) 13%, transparent)', 'text' => 'var(--amber-text)', 'glow' => 'var(--amber-glow)'],
                'Medium'   => ['bg' => 'color-mix(in srgb, var(--blue-text) 13%, transparent)', 'text' => 'var(--blue-text)', 'glow' => 'var(--blue-glow)'],
                'Low'      => ['bg' => 'color-mix(in srgb, var(--slate-text) 14%, transparent)', 'text' => 'var(--slate-text)', 'glow' => 'none'],
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($statCards as $card)
                <div class="card card-3d rounded-lg p-4">
                    <div class="mb-2 flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $card['color'] }}; box-shadow: 0 0 10px {{ $card['color'] }}"></span>
                        <span class="truncate text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">{{ $card['label'] }}</span>
                    </div>
                    <div class="fx-glow-text font-display text-[24px] font-bold leading-none" style="color: {{ $card['color'] }}">{{ $card['value'] }}</div>
                    <div class="mt-1.5 text-[11px] text-[var(--muted)]">{{ $card['hint'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- Ticket queue + side panel --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_400px]">
            <div class="card card-3d overflow-hidden rounded-lg" x-data="{ tab: 'unassigned', search: '', status: '', priority: '', impact: '', assignee: '', customer: '' }">
                <div class="flex flex-col gap-3 border-b border-[var(--border)] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div class="flex items-center gap-2.5">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Ticket Queue</h3>
                        <span class="rounded-full border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-[2px] font-mono text-[10.5px] text-[var(--muted)]">{{ $tickets->count() }} total</span>
                    </div>

                    {{-- Queue tabs --}}
                    <div class="flex overflow-x-auto rounded-full border border-[var(--border-strong)] bg-[var(--surface)] p-1">
                        <button type="button" @click="tab = 'unassigned'"
                                :class="tab === 'unassigned' ? 'bg-gradient-to-r from-[var(--accent-cta)] to-[var(--accent-cta-hover)] text-white shadow-[0_0_16px_var(--glow-accent)]' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                                class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition">
                            Unassigned
                            <span class="font-mono text-[10.5px] opacity-80">{{ $stats['unassigned'] }}</span>
                        </button>
                        <button type="button" @click="tab = 'mine'"
                                :class="tab === 'mine' ? 'bg-gradient-to-r from-[var(--accent-cta)] to-[var(--accent-cta-hover)] text-white shadow-[0_0_16px_var(--glow-accent)]' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                                class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition">
                            Mine
                            <span class="font-mono text-[10.5px] opacity-80">{{ $stats['mine'] }}</span>
                        </button>
                        <button type="button" @click="tab = 'all'"
                                :class="tab === 'all' ? 'bg-gradient-to-r from-[var(--accent-cta)] to-[var(--accent-cta-hover)] text-white shadow-[0_0_16px_var(--glow-accent)]' : 'text-[var(--muted)] hover:text-[var(--foreground)]'"
                                class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold transition">
                            All
                            <span class="font-mono text-[10.5px] opacity-80">{{ $tickets->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Filter bar --}}
                <div class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5">
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">
                        <div class="min-w-[160px] flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or number..."
                                   class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2.5 py-1 text-[11.5px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />
                        </div>
                        <div class="w-full sm:w-auto">
                            <select name="status" onchange="this.form.submit()" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                                <option value="">All Statuses</option>
                                <option value="Open" @selected(request('status') === 'Open')>Open</option>
                                <option value="Checking" @selected(request('status') === 'Checking')>Checking</option>
                                <option value="Waiting Customer" @selected(request('status') === 'Waiting Customer')>Waiting Customer</option>
                                <option value="Escalated" @selected(request('status') === 'Escalated')>Escalated</option>
                                <option value="Solved" @selected(request('status') === 'Solved')>Solved</option>
                                <option value="Closed" @selected(request('status') === 'Closed')>Closed</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <select name="priority" onchange="this.form.submit()" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                                <option value="">All Priorities</option>
                                <option value="High" @selected(request('priority') === 'High')>High</option>
                                <option value="Medium" @selected(request('priority') === 'Medium')>Medium</option>
                                <option value="Low" @selected(request('priority') === 'Low')>Low</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <button type="submit" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1 text-[11.5px] font-semibold text-[var(--foreground)] hover:bg-[var(--hover-overlay)] sm:w-auto transition">
                                Filter
                            </button>
                        </div>
                        @if(request()->anyFilled(['search', 'status', 'priority']))
                            <a href="{{ route('dashboard') }}" class="text-[11.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)] transition">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <div class="max-h-[calc(100vh-320px)] overflow-y-auto">
                    @forelse ($tickets as $ticket)
                        @php
                            $status = $ticket->status;
                            $priority = $ticket->priority;
                            $impact = $ticket->impact ?? 'Medium';
                            $imp = $impactBadge[$impact] ?? $impactBadge['Medium'];
                            $priorityColor = match ($priority) {
                                'High' => 'var(--amber-text)',
                                'Medium' => 'var(--blue-text)',
                                default => 'var(--muted)',
                            };
                            $statusBadge = match ($status) {
                                'Open' => ['bg' => 'color-mix(in srgb, var(--red-text) 12%, transparent)', 'text' => 'var(--red-text)'],
                                'Checking' => ['bg' => 'color-mix(in srgb, var(--violet-text) 15%, transparent)', 'text' => 'var(--violet-text)'],
                                'Waiting Customer' => ['bg' => 'color-mix(in srgb, var(--amber-text) 12%, transparent)', 'text' => 'var(--amber-text)'],
                                'Escalated' => ['bg' => 'color-mix(in srgb, var(--orange-text) 14%, transparent)', 'text' => 'var(--orange-text)'],
                                'Solved' => ['bg' => 'color-mix(in srgb, var(--green-text) 12%, transparent)', 'text' => 'var(--green-text)'],
                                default => ['bg' => 'color-mix(in srgb, var(--slate-text) 12%, transparent)', 'text' => 'var(--slate-text)'],
                            };
                            $tabKey = $ticket->assigned_to === null
                                ? 'unassigned'
                                : ($ticket->assigned_to === auth()->id() ? 'mine' : 'all');
                        @endphp
                        <div class="border-b border-[var(--border)] px-4 py-3.5 transition hover:bg-[var(--hover-overlay)] sm:px-5"
                             x-show="tab === 'all' || tab === '{{ $tabKey }}'"
                             x-cloak>
                            <div class="flex flex-wrap items-start justify-between gap-x-3 gap-y-2">
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                        {{ strtoupper(substr($ticket->customer->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="block truncate text-[13.5px] font-medium text-[var(--foreground)] transition hover:text-[var(--accent-text)]">{{ $ticket->title }}</a>

                                        {{-- Customer + assignee tag --}}
                                        <div class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-[11.5px] text-[var(--muted)]">
                                            <span class="truncate">{{ $ticket->customer->name ?? 'Unknown' }}</span>
                                            <span class="font-mono text-[10.5px]">{{ $ticket->ticket_number }}</span>
                                            <span class="text-[var(--border-strong)]">·</span>

                                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="inline-flex">
                                                @csrf
                                                @method('PATCH')
                                                <select name="assigned_to" onchange="this.form.submit()"
                                                        class="select-chip inline-flex max-w-[170px] items-center gap-1 rounded-full border px-2.5 py-[3px] text-[11px] font-semibold transition
                                                        {{ $ticket->assigned_to === null
                                                            ? 'border-dashed border-[var(--amber-text-50)] bg-[var(--amber-text-06)] text-[var(--amber-text)] fx-unassigned-pulse'
                                                            : 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--foreground)]' }}">
                                                    <option value="" @selected($ticket->assigned_to === null)>Unassigned</option>
                                                    @foreach ($assignableUsers as $agent)
                                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-wrap items-center gap-1.5">
                                    <span class="badge" style="background: {{ $imp['bg'] }}; color: {{ $imp['text'] }}; box-shadow: {{ $imp['glow'] }};">
                                        {{ $impact }}
                                    </span>
                                    <span class="badge" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['text'] }};">{{ $status }}</span>
                                </div>
                            </div>

                            <div class="mt-2.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px]">
                                <span class="flex items-center gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $priorityColor }}"></span>
                                    <span class="font-mono uppercase tracking-[0.04em]" style="color: {{ $priorityColor }}">{{ $priority }}</span>
                                </span>
                                @if ($ticket->category)
                                    <span class="text-[var(--border-strong)]">·</span>
                                    <span class="text-[var(--muted)]">{{ $ticket->category }}</span>
                                @endif
                                <span class="ml-auto font-mono text-[var(--muted)]">{{ $ticket->created_at->diffForHumans() }}</span>
                                @if ($ticket->messages->count() > 0)
                                    <span class="flex items-center gap-1 font-mono text-[var(--muted)]">
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                            <path d="M2 4a2 2 0 012-2h8a2 2 0 012 2v5a2 2 0 01-2 2H6l-3 3v-3H4a2 2 0 01-2-2V4z" fill="currentColor" opacity=".6"/>
                                        </svg>
                                        {{ $ticket->messages->count() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-16 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[var(--surface-3)]">
                                <svg width="22" height="22" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <rect x="1" y="2.5" width="14" height="11" rx="2" stroke="var(--muted)" stroke-width="1.3"/>
                                    <path d="M1 5l7-3 7 3" stroke="var(--muted)" stroke-width="1.3"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-medium text-[var(--muted)]">No tickets yet</p>
                            <a href="{{ route('tickets.create') }}" class="mt-3 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Create the first ticket →</a>
                        </div>
                    @endforelse
                    
                    <div class="px-5 py-3 border-t border-[var(--border)]">
                        {{ $tickets->links() }}
                    </div>

                    {{-- Per-tab empty states --}}
                    @if ($stats['unassigned'] === 0)
                        <div class="px-5 py-10 text-center" x-show="tab === 'unassigned'" x-cloak>
                            <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-[var(--green-text-08)]">
                                <svg class="h-4.5 w-4.5 text-[var(--green-text)]" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M2.5 8.5l3.5 3.5 7.5-8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <p class="text-[13px] font-medium text-[var(--foreground)]">All tickets have a responsible agent</p>
                            <p class="mt-0.5 text-[12px] text-[var(--muted)]">Nothing waiting for placement.</p>
                        </div>
                    @endif
                    @if ($stats['mine'] === 0)
                        <div class="px-5 py-10 text-center" x-show="tab === 'mine'" x-cloak>
                            <p class="text-[13px] font-medium text-[var(--foreground)]">No tickets assigned to you</p>
                            <p class="mt-0.5 text-[12px] text-[var(--muted)]">Grab one from the Unassigned tab.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Latest tickets --}}
            <div class="card card-3d h-fit overflow-hidden rounded-lg">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Latest Tickets</h3>
                </div>
                <div class="divide-y divide-[var(--border)]">
                    @forelse ($tickets->take(5) as $ticket)
                        @php($imp = $impactBadge[$ticket->impact ?? 'Medium'] ?? $impactBadge['Medium'])
                        <a href="{{ route('tickets.show', $ticket) }}" class="block px-5 py-3 transition hover:bg-[var(--hover-overlay)]">
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 font-mono text-[11px] font-medium text-[var(--accent)]">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $imp['text'] }}; box-shadow: 0 0 8px {{ $imp['text'] }}"></span>
                                    {{ $ticket->ticket_number }}
                                </span>
                                <span class="font-mono text-[10.5px] text-[var(--muted)]">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="mt-1 truncate text-[12.5px] font-medium text-[var(--foreground)]">{{ $ticket->title }}</div>
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
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-[12.5px] text-[var(--muted)]">No tickets yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
