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

        <div class="card card-3d overflow-hidden rounded-lg" x-data="{ search: '', status: '', priority: '', impact: '', assignee: '', customer: '' }">
            {{-- Filter bar --}}
            <div class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="min-w-[160px] flex-1">
                        <input type="text" x-model="search" placeholder="Search title or number..."
                               class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2.5 py-1 text-[11.5px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />
                    </div>
                    <div class="w-full sm:w-auto">
                        <select x-model="status" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                            <option value="">All Statuses</option>
                            <option value="Open">Open</option>
                            <option value="Checking">Checking</option>
                            <option value="Waiting Customer">Waiting Customer</option>
                            <option value="Escalated">Escalated</option>
                            <option value="Solved">Solved</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select x-model="priority" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                            <option value="">All Priorities</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select x-model="impact" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                            <option value="">All Impacts</option>
                            <option value="Critical">Critical</option>
                            <option value="High">High</option>
                            <option value="Medium">Medium</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select x-model="assignee" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                            <option value="">All Assignees</option>
                            <option value="unassigned">Unassigned</option>
                            @foreach ($assignableUsers as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-auto">
                        <select x-model="customer" class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2 py-1 text-[11.5px] text-[var(--foreground)] focus:border-[var(--accent)] focus:outline-none">
                            <option value="">All Customers</option>
                            @foreach ($uniqueCustomers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" @click="search = ''; status = ''; priority = ''; impact = ''; assignee = ''; customer = ''"
                            x-show="search !== '' || status !== '' || priority !== '' || impact !== '' || assignee !== '' || customer !== ''"
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
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Impact</th>
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
                                $impact = $ticket->impact ?? 'Medium';
                                $impactBadge = [
                                    'Critical' => ['bg' => 'color-mix(in srgb, var(--red-text) 14%, transparent)', 'text' => 'var(--red-text)', 'glow' => 'var(--red-glow)'],
                                    'High'     => ['bg' => 'color-mix(in srgb, var(--amber-text) 13%, transparent)', 'text' => 'var(--amber-text)', 'glow' => 'var(--amber-glow)'],
                                    'Medium'   => ['bg' => 'color-mix(in srgb, var(--blue-text) 13%, transparent)', 'text' => 'var(--blue-text)', 'glow' => 'var(--blue-glow)'],
                                    'Low'      => ['bg' => 'color-mix(in srgb, var(--slate-text) 14%, transparent)', 'text' => 'var(--slate-text)', 'glow' => 'none'],
                                ];
                                $imp = $impactBadge[$impact] ?? $impactBadge['Medium'];
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
                                x-show="(search === '' || '{{ strtolower(addslashes($ticket->title)) }}'.includes(search.toLowerCase()) || '{{ strtolower($ticket->ticket_number) }}'.includes(search.toLowerCase())) &&
                                        (status === '' || '{{ $ticket->status }}' === status) &&
                                        (priority === '' || '{{ $ticket->priority }}' === priority) &&
                                        (impact === '' || '{{ $ticket->impact ?? 'Medium' }}' === impact) &&
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
                                    <span class="badge" style="background: {{ $imp['bg'] }}; color: {{ $imp['text'] }}; box-shadow: {{ $imp['glow'] }};">{{ $impact }}</span>
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
