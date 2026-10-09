<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Edit Customer</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]"><span class="font-mono">{{ $customer->customer_id }}</span> — {{ $customer->name }}</p>
            </div>
            <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali ke Customer
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('customers.update', $customer) }}" method="POST" class="card space-y-5 p-6">
            @csrf
            @method('PUT')

            @include('customers.partials.form', ['customer' => $customer])

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('customers.show', $customer) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-app-layout>
