<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Tickets</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $tickets->count() }} ticket(s) in the system</p>
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

        <div class="card card-3d overflow-hidden rounded-lg" x-data="{ search: '', status: '', priority: '', assignee: '', customer: '' }">
            {{-- Filter bar --}}
            <div class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="min-w-[160px] flex-1">
                        <input type="text" id="ticket_search" name="search" aria-label="Search title or number" x-model="search" placeholder="Search title or number..."
                               class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2.5 py-1 text-[11.5px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />
                    </div>
                    {{-- Status Dropdown --}}
                    <div class="relative w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between gap-2.5 rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1.5 text-[12px] font-medium text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)] focus:border-[var(--accent)] focus:outline-none sm:w-auto min-w-[125px]">
                            <span class="truncate" x-text="status ? status : 'All Statuses'">All Statuses</span>
                            <svg class="h-3.5 w-3.5 shrink-0 text-[var(--muted)] transition-transform duration-200" :class="{ 'rotate-180 text-[var(--accent)]': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute left-0 top-full z-50 mt-1.5 w-44 overflow-hidden rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl backdrop-blur-md py-1">
                            <template x-for="opt in ['', 'Open', 'Checking', 'Waiting Customer', 'Escalated', 'Solved', 'Closed']" :key="opt">
                                <button type="button" @click="status = opt; open = false"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                        :class="status === opt ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                    <span x-text="opt ? opt : 'All Statuses'"></span>
                                    <span x-show="status === opt" class="text-[var(--accent)] shrink-0">✓</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Priority Dropdown --}}
                    <div class="relative w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between gap-2.5 rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1.5 text-[12px] font-medium text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)] focus:border-[var(--accent)] focus:outline-none sm:w-auto min-w-[125px]">
                            <span class="truncate" x-text="priority ? priority : 'All Priorities'">All Priorities</span>
                            <svg class="h-3.5 w-3.5 shrink-0 text-[var(--muted)] transition-transform duration-200" :class="{ 'rotate-180 text-[var(--accent)]': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute left-0 top-full z-50 mt-1.5 w-40 overflow-hidden rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl backdrop-blur-md py-1">
                            <template x-for="opt in ['', 'High', 'Medium', 'Low']" :key="opt">
                                <button type="button" @click="priority = opt; open = false"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                        :class="priority === opt ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                    <span x-text="opt ? opt : 'All Priorities'"></span>
                                    <span x-show="priority === opt" class="text-[var(--accent)] shrink-0">✓</span>
                                </button>
                            </template>
                        </div>
                    </div>
                    {{-- Assignee Dropdown --}}
                    <div class="relative w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between gap-2.5 rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1.5 text-[12px] font-medium text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)] focus:border-[var(--accent)] focus:outline-none sm:w-auto min-w-[130px]">
                            <span class="truncate" x-text="assignee ? (assignee === 'unassigned' ? 'Unassigned' : document.querySelector(`[data-agent-id='${assignee}']`)?.dataset?.agentName || 'Assignee') : 'All Assignees'">All Assignees</span>
                            <svg class="h-3.5 w-3.5 shrink-0 text-[var(--muted)] transition-transform duration-200" :class="{ 'rotate-180 text-[var(--accent)]': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute left-0 top-full z-50 mt-1.5 w-48 max-h-60 overflow-y-auto rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl backdrop-blur-md py-1 divide-y divide-[var(--border-60)]">
                            <button type="button" @click="assignee = ''; open = false"
                                    class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                    :class="assignee === '' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                <span>All Assignees</span>
                                <span x-show="assignee === ''" class="text-[var(--accent)] shrink-0">✓</span>
                            </button>
                            <button type="button" @click="assignee = 'unassigned'; open = false"
                                    class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                    :class="assignee === 'unassigned' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                <span>Unassigned</span>
                                <span x-show="assignee === 'unassigned'" class="text-[var(--accent)] shrink-0">✓</span>
                            </button>
                            @foreach ($assignableUsers as $agent)
                                <button type="button" @click="assignee = '{{ $agent->id }}'; open = false"
                                        data-agent-id="{{ $agent->id }}" data-agent-name="{{ $agent->name }}"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                        :class="assignee === '{{ $agent->id }}' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                    <span class="truncate">{{ $agent->name }}</span>
                                    <span x-show="assignee === '{{ $agent->id }}'" class="text-[var(--accent)] shrink-0">✓</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Customer Dropdown --}}
                    <div class="relative w-full sm:w-auto" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex w-full items-center justify-between gap-2.5 rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3 py-1.5 text-[12px] font-medium text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)] focus:border-[var(--accent)] focus:outline-none sm:w-auto min-w-[130px]">
                            <span class="truncate" x-text="customer ? (document.querySelector(`[data-cust-id='${customer}']`)?.dataset?.custName || 'Customer') : 'All Customers'">All Customers</span>
                            <svg class="h-3.5 w-3.5 shrink-0 text-[var(--muted)] transition-transform duration-200" :class="{ 'rotate-180 text-[var(--accent)]': open }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                             class="absolute left-0 top-full z-50 mt-1.5 w-52 max-h-60 overflow-y-auto rounded-xl border border-[var(--border-strong)] bg-[var(--surface-2)] shadow-2xl backdrop-blur-md py-1 divide-y divide-[var(--border-60)]">
                            <button type="button" @click="customer = ''; open = false"
                                    class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                    :class="customer === '' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                <span>All Customers</span>
                                <span x-show="customer === ''" class="text-[var(--accent)] shrink-0">✓</span>
                            </button>
                            @foreach ($uniqueCustomers as $cust)
                                <button type="button" @click="customer = '{{ $cust->id }}'; open = false"
                                        data-cust-id="{{ $cust->id }}" data-cust-name="{{ $cust->name }}"
                                        class="flex w-full items-center justify-between px-3.5 py-2 text-left text-[12px] transition hover:bg-[var(--hover-overlay)] hover:pl-4.5"
                                        :class="customer === '{{ $cust->id }}' ? 'font-semibold text-[var(--accent)] bg-[var(--surface-3)]' : 'font-medium text-[var(--foreground)]'">
                                    <span class="truncate">{{ $cust->name }}</span>
                                    <span x-show="customer === '{{ $cust->id }}'" class="text-[var(--accent)] shrink-0">✓</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" @click="search = ''; status = ''; priority = ''; assignee = ''; customer = ''"
                            x-show="search !== '' || status !== '' || priority !== '' || assignee !== '' || customer !== ''"
                            class="text-[11.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)] transition" x-cloak>
                        Clear
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Ticket</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Customer</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Priority</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Status</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Assignee</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Messages</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse ($tickets as $ticket)
                            @php
                                $priorityColor = match ($ticket->priority) {
                                    'High' => 'var(--amber-text)',
                                    'Medium' => 'var(--blue-text)',
                                    default => 'var(--muted)',
                                };
                                $statusBadge = match ($ticket->status) {
                                    'Open' => ['bg' => 'color-mix(in srgb, var(--red-text) 12%, transparent)', 'text' => 'var(--red-text)'],
                                    'Checking' => ['bg' => 'color-mix(in srgb, var(--violet-text) 15%, transparent)', 'text' => 'var(--violet-text)'],
                                    'Waiting Customer' => ['bg' => 'color-mix(in srgb, var(--amber-text) 12%, transparent)', 'text' => 'var(--amber-text)'],
                                    'Escalated' => ['bg' => 'color-mix(in srgb, var(--orange-text) 14%, transparent)', 'text' => 'var(--orange-text)'],
                                    'Solved' => ['bg' => 'color-mix(in srgb, var(--green-text) 12%, transparent)', 'text' => 'var(--green-text)'],
                                    default => ['bg' => 'color-mix(in srgb, var(--slate-text) 12%, transparent)', 'text' => 'var(--slate-text)'],
                                };
                            @endphp
                            <tr class="transition hover:bg-[var(--hover-overlay)]"
                                x-show="(search === '' || 
                                         '{{ strtolower(addslashes($ticket->title)) }}'.includes(search.toLowerCase()) || 
                                         '{{ strtolower($ticket->ticket_number) }}'.includes(search.toLowerCase()) ||
                                         '{{ strtolower(addslashes($ticket->customer->name ?? '')) }}'.includes(search.toLowerCase()) ||
                                         '{{ strtolower($ticket->customer->phone ?? '') }}'.includes(search.toLowerCase()) ||
                                         '{{ strtolower($ticket->customer->customer_id ?? '') }}'.includes(search.toLowerCase()) ||
                                         '{{ strtolower($ticket->category ?? '') }}'.includes(search.toLowerCase())) &&
                                        (status === '' || '{{ $ticket->status }}' === status) &&
                                        (priority === '' || '{{ $ticket->priority }}' === priority) &&
                                        (assignee === '' || (assignee === 'unassigned' ? {{ $ticket->assigned_to === null ? 'true' : 'false' }} : '{{ $ticket->assigned_to }}' === assignee)) &&
                                        (customer === '' || '{{ $ticket->customer_id }}' === customer)"
                                x-cloak>
                                <td class="px-5 py-4">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="group block">
                                        <span class="font-mono text-[11px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                                        <span class="mt-0.5 block max-w-[340px] truncate text-[13.5px] font-medium text-[var(--foreground)] group-hover:text-[var(--accent-text)]">{{ $ticket->title }}</span>
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-[13px] text-[var(--foreground)]">{{ $ticket->customer->name ?? 'Unknown' }}</div>
                                    <div class="text-[11.5px] text-[var(--muted)]">{{ $ticket->customer->phone ?? '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="flex items-center gap-1.5 text-[11px] font-medium">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $priorityColor }}"></span>
                                        <span class="font-mono uppercase tracking-[0.04em]" style="color: {{ $priorityColor }}">{{ $ticket->priority }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['text'] }};">{{ $ticket->status }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($ticket->assignee)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-2 py-[3px]">
                                            <span class="flex h-4.5 w-4.5 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[8.5px] font-bold text-[var(--accent-text)]">{{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}</span>
                                            <span class="text-[11px] font-semibold text-[var(--accent-text-strong)]">{{ $ticket->assignee->name }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full border border-dashed border-[var(--amber-text-40)] px-2 py-[3px] text-[10.5px] font-semibold uppercase tracking-[0.04em] text-[var(--amber-text)]">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--muted)]">{{ $ticket->messages->count() }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-mono text-[11.5px] text-[var(--muted)]">{{ $ticket->created_at->format('d M Y') }}</div>
                                    <div class="text-[10.5px] text-[var(--muted-strong)]">{{ $ticket->created_at->format('H:i') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <p class="text-[13.5px] font-medium text-[var(--muted)]">No tickets yet</p>
                                    <a href="{{ route('tickets.create') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Create the first ticket →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
