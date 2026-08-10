<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Customers</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $customers->count() }} customer(s) registered</p>
            </div>
            <a href="{{ route('customers.create') }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                Add Customer
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-5 rounded-md border border-[var(--green-text-30)] bg-[var(--green-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--green-text)]">
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Customer</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">ID</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Phone</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Address</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Package</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse ($customers as $customer)
                            <tr class="transition hover:bg-[var(--hover-overlay)]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[var(--border-strong)] bg-[var(--surface-3)] text-[11px] font-semibold text-[var(--foreground)]">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <span class="text-[13.5px] font-medium text-[var(--foreground)]">{{ $customer->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-mono text-[11.5px] text-[var(--muted)]">{{ $customer->customer_id }}</td>
                                <td class="px-5 py-4 font-mono text-[12px] text-[var(--foreground)]">{{ $customer->phone }}</td>
                                <td class="px-5 py-4 max-w-[280px] truncate text-[12.5px] text-[var(--muted)]">{{ $customer->address }}</td>
                                <td class="px-5 py-4">
                                    <span class="badge border border-[var(--accent-30)] bg-[var(--accent-soft)] text-[var(--accent-text)]">{{ $customer->package ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <p class="text-[13.5px] font-medium text-[var(--muted)]">Belum ada customer.</p>
                                    <a href="{{ route('customers.create') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Add the first customer →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
