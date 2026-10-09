<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}" aria-label="Kembali ke daftar tiket" class="flex h-8 w-8 items-center justify-center rounded-md border border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--muted)] transition hover:text-[var(--foreground)]">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="font-mono text-[11.5px] font-medium text-[var(--accent)]">{{ $ticket->ticket_number }}</span>
                        <x-ticket-status :status="$ticket->status" />
                        <x-ticket-priority :priority="$ticket->priority" />
                        <span class="badge badge-slate">{{ $ticket->category ?? 'Email' }}</span>
                    </div>
                    <h2 class="mt-1 font-display text-[15px] font-semibold leading-snug text-[var(--foreground)]">{{ $ticket->title }}</h2>

                    {{-- Assignee widget --}}
                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                        @can('assign', $ticket)
                            <form method="POST" action="{{ route('tickets.assignee.update', $ticket) }}" class="inline-flex">
                                @csrf
                                @method('PATCH')
                                <select name="assigned_to" id="assigned_to" aria-label="Penanggung jawab" onchange="this.form.submit()"
                                    class="select-chip inline-flex items-center gap-1.5 rounded-full border text-[13px] font-semibold transition
                                    {{ $ticket->assigned_to === null
                                        ? 'border-dashed border-[var(--amber-text-50)] bg-[var(--amber-text-06)] text-[var(--amber-text)]'
                                        : 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--foreground)]' }}">
                                    <option value="" @selected($ticket->assigned_to === null)>Unassigned</option>
                                    @foreach ($assignableUsers as $staff)
                                        <option value="{{ $staff->id }}" @selected((int) $ticket->assigned_to === $staff->id)>{{ $staff->name }} ({{ $staff->role }})</option>
                                    @endforeach
                                </select>
                                <noscript><button type="submit" class="btn-secondary ml-2">Simpan</button></noscript>
                            </form>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-1.5" />
                        @else
                            @if ($ticket->assignee)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] px-2.5 py-[3px] text-[11px] font-semibold text-[var(--accent-text-strong)]">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[8px] font-bold">{{ strtoupper(substr($ticket->assignee->name, 0, 2)) }}</span>
                                    {{ $ticket->assignee->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full border border-dashed border-[var(--amber-text-40)] px-2.5 py-[3px] text-[10.5px] font-semibold uppercase tracking-[0.04em] text-[var(--amber-text)]">Unassigned</span>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tickets.audit-log', $ticket) }}" class="btn-secondary">Audit Log</a>
                @can('update', $ticket)
                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn-secondary">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M11 2l3 3-9 9H2v-3l9-9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Edit Ticket
                    </a>
                @endcan
                @can('delete', $ticket)
                    <form method="POST" action="{{ route('tickets.destroy', $ticket) }}"
                        onsubmit="return confirm({{ Js::from('Hapus tiket '.$ticket->ticket_number.'?') }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Hapus</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]" role="status">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                {{-- Conversation --}}
                <div class="card flex flex-col overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Conversation</h3>
                    </div>

                    <div class="ticket-detail-scroll max-h-[52vh] flex-1 space-y-4 overflow-y-auto px-5 py-4">
                        @forelse ($ticket->messages as $msg)
                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                    {{ strtoupper(substr($msg->user->name ?? 'CU', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="text-[13px] font-semibold text-[var(--foreground)]">{{ $msg->user->name ?? 'Customer' }}</span>
                                        @if ($msg->is_internal)
                                            <span class="badge badge-amber">
                                                <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                                    <rect x="3.5" y="7.5" width="9" height="5.5" rx="1" stroke="currentColor" stroke-width="1.4"/>
                                                    <path d="M6 7.5V5.5a2 2 0 114 0v2" stroke="currentColor" stroke-width="1.4"/>
                                                </svg>
                                                Internal
                                            </span>
                                        @endif
                                        <span class="font-mono text-[11px] text-[var(--muted)]">{{ $msg->created_at?->format('d M Y H:i') }}</span>
                                    </div>
                                    <div class="whitespace-pre-wrap rounded-md rounded-tl-none border p-3 text-[13px] leading-relaxed text-[var(--foreground)] {{ $msg->is_internal ? 'border-[var(--amber-text-40)] bg-[var(--amber-text-07)]' : 'border-[var(--border)] bg-[var(--surface-2)]' }}">{{ $msg->message }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-[13.5px] font-medium text-[var(--muted)]">Belum ada riwayat pesan.</p>
                                <p class="mt-1 text-[12px] text-[var(--muted-strong)]">Mulai percakapan di bawah.</p>
                            </div>
                        @endforelse
                    </div>

                    @can('reply', $ticket)
                        <form action="{{ route('tickets.messages.store', $ticket) }}" method="POST" class="border-t border-[var(--border)] px-5 py-4">
                            @csrf
                            <label for="message" class="label">Balasan</label>
                            <textarea name="message" id="message" rows="3" required maxlength="2000" placeholder="Tulis balasan pesan di sini…" class="input resize-none"
                                onkeydown="if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); if (this.value.trim()) this.form.requestSubmit(); }">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-1.5" />
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                @can('addInternalNote', $ticket)
                                    <label class="flex cursor-pointer items-center gap-2 text-[12.5px] font-medium text-[var(--muted)]">
                                        <input type="checkbox" name="is_internal" value="1" class="h-3.5 w-3.5 rounded border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--amber-text)] focus:ring-[var(--amber-text)]">
                                        Catatan internal (hanya terlihat oleh staf)
                                    </label>
                                @else
                                    <span></span>
                                @endcan
                                <button type="submit" class="btn-primary">
                                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                        <path d="M14 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                                    </svg>
                                    Kirim Pesan
                                </button>
                            </div>
                        </form>
                    @endcan
                </div>

                {{-- Activity log --}}
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Activity Log</h3>
                    </div>
                    <div class="ticket-detail-scroll max-h-[46vh] overflow-y-auto">
                        @forelse ($ticket->activities as $activity)
                            @php
                                $actionBadgeClass = match ($activity->action) {
                                    'status_change', 'priority_change' => 'badge-blue',
                                    'internal_note' => 'badge-amber',
                                    'assignment' => 'badge-cyan',
                                    'sla_breached' => 'badge-red',
                                    'created' => 'badge-green',
                                    default => 'badge-violet',
                                };
                            @endphp
                            <div class="flex items-start gap-3 border-b border-[var(--border-60)] px-5 py-3.5 last:border-0">
                                <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[10px] font-semibold text-[var(--foreground)]">
                                    {{ strtoupper(substr($activity->user->name ?? 'SI', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="badge {{ $actionBadgeClass }}">{{ $activity->label() }}</span>
                                        <span class="text-[12.5px] font-semibold text-[var(--foreground)]">{{ $activity->user->name ?? 'Sistem' }}</span>
                                        <span class="font-mono text-[10.5px] text-[var(--muted)]" title="{{ $activity->created_at?->format('d M Y, H:i') }}">{{ $activity->created_at?->diffForHumans() }}</span>
                                    </div>
                                    @if ($activity->isTransition())
                                        <p class="mt-1 text-[12px] text-[var(--muted)]">
                                            <span class="font-mono">{{ $activity->old_value ?? '-' }}</span>
                                            <span class="mx-1 text-[var(--muted-strong)]">→</span>
                                            <span class="font-mono font-semibold text-[var(--foreground)]">{{ $activity->new_value ?? '-' }}</span>
                                        </p>
                                    @elseif ($activity->action === 'sla_breached')
                                        <p class="mt-1 text-[12px] text-[var(--red-bright)]">Deadline {{ $activity->new_value ?? '-' }} terlewati.</p>
                                    @elseif ($activity->action === 'created')
                                        <p class="mt-1 text-[12px] text-[var(--muted)]">Status awal {{ $activity->new_value ?? 'Open' }}.</p>
                                    @elseif (filled($activity->new_value))
                                        <p class="mt-1 whitespace-pre-line text-[12px] text-[var(--muted)]">{{ Str::limit($activity->new_value, 300) }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-10 text-center text-[12.5px] font-medium text-[var(--muted)]">Belum ada aktivitas tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Details sidebar --}}
            <div class="space-y-6">
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Details</h3>
                    </div>
                    <dl class="divide-y divide-[var(--border)] text-[12.5px]">
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Dibuat</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->created_at?->format('d M Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Diperbarui</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->updated_at?->format('d M Y H:i') }}</dd>
                        </div>
                        @if ($ticket->resolved_at)
                            <div class="flex justify-between gap-3 px-5 py-3">
                                <dt class="text-[var(--muted)]">Diselesaikan</dt>
                                <dd class="font-mono text-[var(--foreground)]">{{ $ticket->resolved_at->format('d M Y H:i') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Kategori</dt>
                            <dd class="text-[var(--foreground)]">{{ $ticket->category ?? 'Email' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">OLT</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->olt ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 px-5 py-3">
                            <dt class="text-[var(--muted)]">Lokasi</dt>
                            <dd class="font-mono text-[var(--foreground)]">{{ $ticket->location ?? '—' }}</dd>
                        </div>
                        @if ($ticket->attachment_path)
                            <div class="flex justify-between gap-3 px-5 py-3">
                                <dt class="text-[var(--muted)]">Lampiran</dt>
                                <dd class="min-w-0 truncate text-right">
                                    @if ($attachmentAvailable ?? false)
                                        <a href="{{ route('tickets.attachment', $ticket) }}" class="font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Download ({{ basename($ticket->attachment_path) }})</a>
                                    @else
                                        <span class="text-[var(--muted)]">Lampiran tidak tersedia ({{ basename($ticket->attachment_path) }})</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                    @if ($ticket->sla_deadline)
                        <div class="border-t border-[var(--border)] px-5 py-4">
                            <x-sla-indicator :ticket="$ticket" />
                        </div>
                    @endif
                </div>

                @if ($ticket->resolution_note)
                    <div class="card overflow-hidden">
                        <div class="border-b border-[var(--border)] px-5 py-4">
                            <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Catatan Penyelesaian</h3>
                        </div>
                        <p class="whitespace-pre-wrap px-5 py-4 text-[13px] leading-relaxed text-[var(--foreground)]">{{ $ticket->resolution_note }}</p>
                    </div>
                @endif

                @can('update', $ticket)
                    <div class="card relative z-20">
                        <div class="border-b border-[var(--border)] px-5 py-4">
                            <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Update Status</h3>
                        </div>
                        <form action="{{ route('tickets.status.update', $ticket) }}" method="POST" class="space-y-3 px-5 py-4"
                            x-data="{ status: @js(old('status', $allowedStatuses[0]->value ?? '')) }">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="status" class="label">Status baru</label>
                                <select name="status" id="status" required x-model="status" class="input">
                                    @foreach ($allowedStatuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->value }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                            </div>
                            <div x-show="['Solved', 'Closed'].includes(status)">
                                <label for="resolution_note" class="label">Catatan penyelesaian</label>
                                <textarea name="resolution_note" id="resolution_note" rows="3" maxlength="2000" class="input resize-none"
                                    placeholder="Wajib untuk Solved, atau Closed tanpa penyelesaian sebelumnya">{{ old('resolution_note') }}</textarea>
                                <x-input-error :messages="$errors->get('resolution_note')" class="mt-1.5" />
                            </div>
                            <button type="submit" class="btn-primary w-full justify-center">Simpan Status</button>
                        </form>
                    </div>
                @endcan

                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Customer</h3>
                        @if ($ticket->customer)
                            <a href="{{ route('customers.show', $ticket->customer) }}" class="text-[11.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Lihat →</a>
                        @endif
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
                    <p class="whitespace-pre-wrap px-5 py-4 text-[13px] leading-relaxed text-[var(--foreground)]">{{ $ticket->description }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
