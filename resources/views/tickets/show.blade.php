<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}" class="flex h-8 w-8 items-center justify-center rounded-md border border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--muted)] transition hover:text-[var(--foreground)]">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-[11.5px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
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
                        <span class="badge" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['text'] }};">{{ $ticket->status }}</span>
                        <span class="flex items-center gap-1.5 text-[11px] font-medium font-mono uppercase tracking-[0.04em]" style="color: {{ $priorityColor }}">
                            <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $priorityColor }}"></span>
                            {{ $ticket->priority }}
                        </span>
                        @php
                            $impact = $ticket->impact ?? 'Medium';
                            $impactBadge = [
                                'Critical' => ['bg' => 'color-mix(in srgb, var(--red-text) 14%, transparent)', 'text' => 'var(--red-text)', 'glow' => 'var(--red-glow)'],
                                'High'     => ['bg' => 'color-mix(in srgb, var(--amber-text) 13%, transparent)', 'text' => 'var(--amber-text)', 'glow' => 'var(--amber-glow)'],
                                'Medium'   => ['bg' => 'color-mix(in srgb, var(--blue-text) 13%, transparent)', 'text' => 'var(--blue-text)', 'glow' => 'var(--blue-glow)'],
                                'Low'      => ['bg' => 'color-mix(in srgb, var(--slate-text) 14%, transparent)', 'text' => 'var(--slate-text)', 'glow' => 'none'],
                            ];
                            $imp = $impactBadge[$impact] ?? $impactBadge['Medium'];
                        @endphp
                        <span class="badge" style="background: {{ $imp['bg'] }}; color: {{ $imp['text'] }}; box-shadow: {{ $imp['glow'] }};">{{ $impact }}</span>
                    </div>
                    <h2 class="mt-1 font-display text-[15px] font-semibold leading-snug text-[var(--foreground)]">{{ $ticket->title }}</h2>

                    {{-- Assignee widget --}}
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        @auth
                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="inline-flex">
                                @csrf
                                @method('PATCH')
                                <select name="assigned_to" onchange="this.form.submit()"
                                        class="select-chip inline-flex items-center gap-1 rounded-full border px-2.5 py-[3px] text-[11px] font-semibold transition
                                        {{ $ticket->assigned_to === null
                                            ? 'border-dashed border-[var(--amber-text-50)] bg-[var(--amber-text-06)] text-[var(--amber-text)]'
                                            : 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--foreground)]' }}">
                                    <option value="" @selected($ticket->assigned_to === null)>Unassigned</option>
                                    @foreach ($assignableUsers as $agent)
                                        <option value="{{ $agent->id }}" @selected($ticket->assigned_to === $agent->id)>{{ $agent->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            @if ($ticket->assignee)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-2.5 py-[3px] text-[11px] font-semibold text-[var(--accent-text-strong)]">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[8px] font-bold">{{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}</span>
                                    {{ $ticket->assignee->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-dashed border-[var(--amber-text-40)] px-2.5 py-[3px] text-[10.5px] font-semibold uppercase tracking-[0.04em] text-[var(--amber-text)]">Unassigned</span>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            {{-- Conversation --}}
            <div class="card flex flex-col overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Conversation</h3>
                </div>

                <div class="ticket-detail-scroll max-h-[52vh] flex-1 space-y-4 overflow-y-auto px-5 py-4">
                    @forelse ($visibleMessages as $msg)
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                {{ strtoupper(substr($msg->user->name ?? 'CS', 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1.5 flex items-center gap-2">
                                    <span class="text-[13px] font-semibold text-[var(--foreground)]">{{ $msg->user->name ?? 'CS Ayyanet' }}</span>
                                    @if ($msg->is_internal)
                                        <span class="badge" style="background: color-mix(in srgb, var(--amber-text) 12%, transparent); color: var(--amber-text);">
                                            <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                <rect x="3.5" y="7.5" width="9" height="5.5" rx="1" stroke="currentColor" stroke-width="1.4"/>
                                                <path d="M6 7.5V5.5a2 2 0 114 0v2" stroke="currentColor" stroke-width="1.4"/>
                                            </svg>
                                            Internal
                                        </span>
                                    @endif
                                    <span class="font-mono text-[11px] text-[var(--muted)]">{{ $msg->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <div class="rounded-md rounded-tl-none border border-[var(--border)] bg-[var(--surface-2)] p-3 text-[13px] leading-relaxed text-[var(--foreground)] whitespace-pre-wrap">
                                    {{ $msg->message }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center">
                            <p class="text-[13.5px] font-medium text-[var(--muted)]">Belum ada riwayat pesan.</p>
                            <p class="mt-1 text-[12px] text-[var(--muted-strong)]">Start the conversation below.</p>
                        </div>
                    @endforelse
                </div>

                @auth
                <form action="{{ route('tickets.messages.store', $ticket->id) }}" method="POST" class="border-t border-[var(--border)] px-5 py-4">
                    @csrf
                    <label for="message" class="label">Reply</label>
                    <textarea name="message" id="message" rows="3" required placeholder="Tulis balasan pesan di sini…" class="input resize-none"></textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-1.5" />
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <label class="flex cursor-pointer items-center gap-2 text-[12.5px] font-medium text-[var(--muted)]">
                            <input type="checkbox" name="is_internal" value="1" class="h-3.5 w-3.5 rounded border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--amber-text)] focus:ring-[var(--amber-text)]">
                            Internal note (staff only)
                        </label>
                        <button type="submit" class="btn-primary">
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M14 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                            </svg>
                            Kirim Pesan
                        </button>
                    </div>
                </form>
                @endauth
            </div>

            {{-- Details sidebar --}}
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Details</h3>
                    </div>
                    <dl class="divide-y divide-[var(--border)] text-[12.5px]">
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Created</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Updated</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->updated_at->format('d M Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Category</dt>
                            <dd class="text-[var(--foreground)]">{{ $ticket->category ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">OLT</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->olt ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Location</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->location ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                @auth
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Update Status</h3>
                    </div>
                    <form action="{{ route('tickets.update-status', $ticket) }}" method="POST" class="px-5 py-4">
                        @csrf
                        @method('PATCH')
                        <label for="status" class="label">Status</label>
                        <select name="status" id="status" class="input">
                            @foreach (['Open', 'Checking', 'Waiting Customer', 'Escalated', 'Solved', 'Closed'] as $status)
                                <option value="{{ $status }}" @selected($ticket->status === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                        <button type="submit" class="btn-primary mt-3 w-full justify-center">Save Status</button>
                    </form>
                </div>
                @endauth

                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Customer</h3>
                    </div>
                    <div class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[var(--accent-40)] bg-[var(--accent-soft)] text-[12px] font-semibold text-[var(--accent-text)]">
                                {{ strtoupper(substr($ticket->customer->name ?? '?', 0, 2)) }}
                            </div>
                            <div>
                                <div class="text-[13.5px] font-semibold text-[var(--foreground)]">{{ $ticket->customer->name ?? 'Unknown' }}</div>
                                <div class="font-mono text-[11px] text-[var(--muted)]">{{ $ticket->customer->customer_id ?? '' }}</div>
                            </div>
                        </div>
                        <dl class="mt-4 space-y-2 text-[12.5px]">
                            <div class="flex justify-between gap-3">
                                <dt class="text-[var(--muted)]">Phone</dt>
                                <dd class="font-mono text-[var(--foreground)]">{{ $ticket->customer->phone ?? '—' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-[var(--muted)]">Package</dt>
                                <dd class="text-[var(--foreground)]">{{ $ticket->customer->package ?? '—' }}</dd>
                            </div>
                        </dl>
                        @if ($ticket->customer?->address)
                            <p class="mt-3 border-t border-[var(--border)] pt-3 text-[12px] leading-relaxed text-[var(--muted)]">{{ $ticket->customer->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Description</h3>
                    </div>
                    <p class="px-5 py-4 text-[13px] leading-relaxed text-[var(--foreground)] whitespace-pre-wrap">{{ $ticket->description }}</p>
                </div>

                @auth
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Activity Log</h3>
                    </div>
                    <div class="ticket-detail-scroll max-h-[46vh] overflow-y-auto">
                        @forelse ($ticket->activities()->with('user')->latest()->limit(50)->get() as $activity)
                            @php
                                $actionStyle = match ($activity->action) {
                                    'status_change' => ['bg' => 'color-mix(in srgb, var(--blue-text) 14%, transparent)', 'text' => 'var(--blue-text)'],
                                    'internal_note' => ['bg' => 'color-mix(in srgb, var(--amber-text) 12%, transparent)', 'text' => 'var(--amber-text)'],
                                    'assignment' => ['bg' => 'color-mix(in srgb, var(--cyan-text) 14%, transparent)', 'text' => 'var(--cyan-text)'],
                                    default => ['bg' => 'color-mix(in srgb, var(--violet-text) 15%, transparent)', 'text' => 'var(--violet-text)'],
                                };
                                $actionLabel = match ($activity->action) {
                                    'status_change' => 'Status',
                                    'internal_note' => 'Internal Note',
                                    'assignment' => 'Assignment',
                                    default => 'Reply',
                                };
                            @endphp
                            <div class="flex items-start gap-3 border-b border-[var(--border-60)] px-5 py-3.5 last:border-0">
                                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[10px] font-semibold text-[var(--foreground)]">
                                    {{ strtoupper(substr($activity->user->name ?? '?', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="badge" style="background: {{ $actionStyle['bg'] }}; color: {{ $actionStyle['text'] }};">{{ $actionLabel }}</span>
                                        <span class="text-[12.5px] font-semibold text-[var(--foreground)]">{{ $activity->user->name ?? 'Unknown' }}</span>
                                        <span class="font-mono text-[10.5px] text-[var(--muted)]">{{ $activity->created_at->format('d M H:i') }}</span>
                                    </div>
                                    @if ($activity->action === 'status_change')
                                        <p class="mt-1 text-[12px] text-[var(--muted)]">
                                            <span class="font-mono text-[var(--red-bright)]">{{ $activity->old_value }}</span>
                                            <span class="mx-1 text-[var(--muted-strong)]">→</span>
                                            <span class="font-mono text-[var(--green-text)]">{{ $activity->new_value }}</span>
                                        </p>
                                    @elseif ($activity->action === 'assignment')
                                        <p class="mt-1 text-[12px] text-[var(--muted)]">
                                            <span class="font-mono">{{ $activity->old_value }}</span>
                                            <span class="mx-1 text-[var(--muted-strong)]">→</span>
                                            <span class="font-mono font-semibold text-[var(--cyan-text)]">{{ $activity->new_value }}</span>
                                        </p>
                                    @elseif ($activity->new_value)
                                        <p class="mt-1 truncate text-[12px] text-[var(--muted)]">{{ $activity->new_value }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-[12.5px] font-medium text-[var(--muted)]">Belum ada aktivitas tercatat.</p>
                        @endforelse
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>
