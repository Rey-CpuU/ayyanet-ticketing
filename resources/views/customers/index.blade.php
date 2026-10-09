<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Customers</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $customers->total() }} customer ditemukan</p>
            </div>
            @can('create', App\Models\Customer::class)
                <a href="{{ route('customers.create') }}" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    Tambah Customer
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]" role="status">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--red-bright)]" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <form method="GET" action="{{ route('customers.index') }}" role="search" aria-label="Cari customer"
                class="border-b border-[var(--border)] bg-[var(--surface-3)] px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="min-w-[180px] flex-1">
                        <input type="search" name="q" value="{{ $search }}" aria-label="Cari customer"
                            placeholder="Cari nama, email, no HP, atau ID customer..."
                            class="w-full rounded-md border border-[var(--border-strong)] bg-[var(--surface)] px-2.5 py-1.5 text-[12px] text-[var(--foreground)] placeholder-[var(--muted)] focus:border-[var(--accent)] focus:outline-none" />
                    </div>
                    @if (auth()->user()->isAdmin())
                        <x-custom-select name="trashed" :value="$trashed" placeholder="Customer aktif" width="w-44"
                            :options="['' => 'Customer aktif', 'with' => 'Termasuk terhapus', 'only' => 'Hanya terhapus']" />
                    @endif
                    <button type="submit" class="rounded-lg border border-[var(--border-strong)] bg-[var(--surface)] px-3.5 py-2 text-[12.5px] font-semibold text-[var(--foreground)] transition hover:border-[var(--accent)] hover:bg-[var(--surface-3)]">
                        Cari
                    </button>
                    @if ($search !== '' || $trashed !== '')
                        <a href="{{ route('customers.index') }}" class="text-[11.5px] font-semibold text-[var(--accent)] transition hover:text-[var(--accent-text)]">Reset</a>
                    @endif
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Customer</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">ID</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Phone</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Address</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Package</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Tiket</th>
                            <th class="px-5 py-3 text-right text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse ($customers as $customer)
                            <tr class="transition hover:bg-[var(--hover-overlay)] {{ $customer->trashed() ? 'opacity-70' : '' }}">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            @if ($customer->trashed())
                                                <span class="text-[13.5px] font-medium text-[var(--foreground)]">{{ $customer->name }}</span>
                                                <span class="badge badge-red ml-1">Terhapus</span>
                                            @else
                                                <a href="{{ route('customers.show', $customer) }}" class="text-[13.5px] font-medium text-[var(--foreground)] hover:text-[var(--accent-text)]">{{ $customer->name }}</a>
                                            @endif
                                            @if ($customer->email)
                                                <div class="text-[11.5px] text-[var(--muted)]">{{ $customer->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-[11.5px] text-[var(--muted)]">{{ $customer->customer_id }}</td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--foreground)]">{{ $customer->phone }}</td>
                                <td class="max-w-[260px] truncate px-5 py-4 text-[12.5px] text-[var(--muted)]">{{ $customer->address }}</td>
                                <td class="px-5 py-4">
                                    <span class="badge border border-[var(--accent-30)] bg-[var(--accent-soft)] text-[var(--accent-text)]">{{ $customer->package ?? '—' }}</span>
                                </td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--muted)]">{{ $customer->tickets_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-3 text-[11.5px] font-semibold">
                                        @if ($customer->trashed())
                                            @can('restore', $customer)
                                                <form method="POST" action="{{ route('customers.restore', $customer->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-[var(--accent)] hover:text-[var(--accent-text)]">Pulihkan</button>
                                                </form>
                                            @endcan
                                            @can('forceDelete', $customer)
                                                <form method="POST" action="{{ route('customers.force-delete', $customer->id) }}"
                                                    onsubmit="return confirm({{ Js::from('Hapus permanen '.$customer->name.'? Tindakan ini tidak dapat dibatalkan.') }})">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[var(--red-bright)] hover:underline">Hapus permanen</button>
                                                </form>
                                            @endcan
                                        @else
                                            <a href="{{ route('customers.show', $customer) }}" class="text-[var(--accent)] hover:text-[var(--accent-text)]">Lihat</a>
                                            @can('update', $customer)
                                                <a href="{{ route('customers.edit', $customer) }}" class="text-[var(--muted)] hover:text-[var(--foreground)]">Edit</a>
                                            @endcan
                                            @can('delete', $customer)
                                                <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                                                    onsubmit="return confirm({{ Js::from('Hapus customer '.$customer->name.'?') }})">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[var(--red-bright)] hover:underline">Hapus</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    @if ($search !== '' || $trashed !== '')
                                        <p class="text-[13.5px] font-medium text-[var(--muted)]">Customer tidak ditemukan.</p>
                                        <a href="{{ route('customers.index') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Reset pencarian →</a>
                                    @else
                                        <p class="text-[13.5px] font-medium text-[var(--muted)]">Belum ada customer.</p>
                                        @can('create', App\Models\Customer::class)
                                            <a href="{{ route('customers.create') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Tambah customer pertama →</a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="border-t border-[var(--border)] px-5 py-3">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
