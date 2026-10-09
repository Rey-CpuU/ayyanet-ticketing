<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Settings</h2>
            <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Tim, hak akses, SLA, customer, dan saluran</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--red-bright)]" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Team --}}
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Tim &amp; Agen</h3>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('users.create') }}" class="text-[12px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Undang Anggota →</a>
                    @endif
                </div>
                <div class="divide-y divide-[var(--border)]">
                    @forelse ($users as $user)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[11px] font-semibold text-[var(--accent-text)]">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                <div>
                                    <div class="text-[13px] font-medium text-[var(--foreground)]">{{ $user->name }}</div>
                                    <div class="text-[11.5px] text-[var(--muted)]">{{ $roles[$user->role] ?? 'Tanpa role' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if (auth()->user()->isAdmin() && ! $user->is(auth()->user()))
                                    <form method="POST" action="{{ route('settings.update-role', $user) }}" class="flex items-center gap-1.5">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" aria-label="Role {{ $user->name }}" class="input w-auto py-1 text-[12px]">
                                            @foreach ($roles as $key => $label)
                                                <option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-secondary !px-2.5 !py-1 text-[11.5px]">Simpan</button>
                                    </form>
                                @endif
                                <span class="badge {{ $user->email_verified_at ? 'badge-green' : 'badge-slate' }}">{{ $user->email_verified_at ? 'Aktif' : 'Belum verifikasi' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-6 text-center text-[12.5px] text-[var(--muted)]">Belum ada pengguna.</p>
                    @endforelse
                </div>
            </section>

            {{-- Roles --}}
            <section class="card overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Hak Akses (Roles)</h3>
                </div>
                <dl class="divide-y divide-[var(--border)] text-[12.5px]">
                    <div class="px-5 py-3"><dt class="font-semibold text-[var(--foreground)]">Admin</dt><dd class="mt-0.5 text-[var(--muted)]">Akses penuh, termasuk pengguna, hapus permanen, settings, dan laporan.</dd></div>
                    <div class="px-5 py-3"><dt class="font-semibold text-[var(--foreground)]">Customer Service</dt><dd class="mt-0.5 text-[var(--muted)]">Membuat, menugaskan, dan mengelola tiket serta customer.</dd></div>
                    <div class="px-5 py-3"><dt class="font-semibold text-[var(--foreground)]">Teknisi Lapangan</dt><dd class="mt-0.5 text-[var(--muted)]">Melihat dan memperbarui tiket yang ditugaskan kepadanya.</dd></div>
                </dl>
            </section>

            {{-- SLA --}}
            <section class="card overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Konfigurasi SLA</h3>
                </div>
                <dl class="divide-y divide-[var(--border)] text-[12.5px]">
                    @foreach (['high' => 'High Priority', 'medium' => 'Medium Priority', 'low' => 'Low Priority'] as $key => $label)
                        <div class="flex items-center justify-between px-5 py-3">
                            <dt class="text-[var(--foreground)]">{{ $label }}</dt>
                            <dd class="font-mono font-semibold text-[var(--accent)]">{{ $slaSettings[$key] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Operating hours --}}
            <section class="card overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Jam Operasional</h3>
                </div>
                <dl class="grid grid-cols-2 gap-3 p-5 text-[12.5px]">
                    @foreach (['Senin - Jumat' => '08:00 - 18:00', 'Sabtu' => '09:00 - 15:00', 'Minggu' => 'Tutup', 'Mode Luar Jam' => 'Auto-Route'] as $label => $value)
                        <div class="rounded-md border border-[var(--border)] bg-[var(--surface)] px-3 py-2">
                            <dt class="text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">{{ $label }}</dt>
                            <dd class="mt-0.5 font-mono text-[var(--foreground)]">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Customers --}}
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Data Customer</h3>
                    <a href="{{ route('customers.index') }}" class="text-[12px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Semua customer →</a>
                </div>
                <div class="max-h-60 overflow-y-auto">
                    <table class="w-full text-left text-[12.5px]">
                        <thead>
                            <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                                <th class="px-5 py-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">ID</th>
                                <th class="px-5 py-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Nama</th>
                                <th class="px-5 py-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">No HP</th>
                                <th class="px-5 py-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Paket</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)]">
                            @php $customers = \App\Models\Customer::latest()->limit(20)->get(); @endphp
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="px-5 py-2 font-mono text-[var(--accent)]">{{ $customer->customer_id }}</td>
                                    <td class="px-5 py-2 text-[var(--foreground)]">{{ $customer->name }}</td>
                                    <td class="px-5 py-2 font-mono text-[var(--muted)]">{{ $customer->phone }}</td>
                                    <td class="px-5 py-2 text-[var(--foreground)]">{{ $customer->package ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-4 text-center text-[var(--muted)]">Belum ada customer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @can('create', App\Models\Customer::class)
                    <form action="{{ route('customers.store') }}" method="POST" class="space-y-4 border-t border-[var(--border)] p-5">
                        @csrf
                        @include('customers.partials.form')
                        <button type="submit" class="btn-primary">+ Tambah Customer</button>
                    </form>
                @endcan
            </section>

            {{-- Channels --}}
            <section class="card overflow-hidden">
                <div class="border-b border-[var(--border)] px-5 py-4">
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Saluran (Omnichannel)</h3>
                </div>
                <div class="divide-y divide-[var(--border)]">
                    @foreach ($channels as $channel)
                        <div class="flex items-center justify-between px-5 py-3">
                            <span class="text-[13px] font-medium text-[var(--foreground)]">{{ $channel['label'] }}</span>
                            <span class="badge {{ $channel['enabled'] ? 'badge-green' : 'badge-slate' }}">{{ $channel['enabled'] ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
