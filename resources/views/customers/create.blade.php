<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Add Customer</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Register a new customer</p>
            </div>
            <a href="{{ route('customers.index') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Customers
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('customers.store') }}" method="POST" class="card space-y-5 p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">Name <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Customer name" class="input">
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="phone" class="label">Phone <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="e.g. 0812-3456-7890" class="input">
                    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="label">Address <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="address" id="address" rows="3" required placeholder="Customer address" class="input resize-none">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="package" class="label">Package</label>
                    <input type="text" name="package" id="package" value="{{ old('package') }}" placeholder="e.g. Home 10Mbps" class="input">
                    <x-input-error :messages="$errors->get('package')" class="mt-1.5" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('customers.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</x-app-layout>
