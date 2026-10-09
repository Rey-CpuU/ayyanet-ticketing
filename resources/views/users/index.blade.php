<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">User Management</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $users->count() }} account(s) — invite CS / staff agents</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                Invite User
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--red-bright)]">
                {{ session('error') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">User</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Role</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Tickets</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Joined</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @foreach ($users as $user)
                            @php
                                $roleStyle = match ($user->role) {
                                    'admin' => ['bg' => 'color-mix(in srgb, var(--red-text) 12%, transparent)', 'text' => 'var(--red-bright)'],
                                    'lapangan' => ['bg' => 'color-mix(in srgb, var(--amber-text) 12%, transparent)', 'text' => 'var(--amber-text)'],
                                    default => ['bg' => 'color-mix(in srgb, var(--violet-text) 15%, transparent)', 'text' => 'var(--violet-text)'],
                                };
                                $roleLabel = match ($user->role) {
                                    'admin' => 'Admin',
                                    'lapangan' => 'Staff',
                                    default => 'CS',
                                };
                            @endphp
                            <tr class="transition hover:bg-[var(--hover-overlay)]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-[var(--accent-40)] bg-[var(--accent-soft)] text-[11px] font-semibold text-[var(--accent-text)]">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-[13.5px] font-semibold text-[var(--foreground)]">
                                                {{ $user->name }}
                                                @if ($user->id === auth()->id())
                                                    <span class="text-[11px] font-medium text-[var(--muted)]">(you)</span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-[11.5px] text-[var(--muted)]">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge" style="background: {{ $roleStyle['bg'] }}; color: {{ $roleStyle['text'] }};">{{ $roleLabel }}</span>
                                </td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--muted)]">{{ $user->created_tickets_count }}</td>
                                <td class="px-5 py-4 font-mono text-[11.5px] text-[var(--muted)]">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('users.edit', $user) }}" class="btn-secondary !px-3 !py-1.5 text-xs">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm({{ Js::from('Hapus akun '.$user->name.'?') }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-danger !px-3 !py-1.5 text-xs">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
