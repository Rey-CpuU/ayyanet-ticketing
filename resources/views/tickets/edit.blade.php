<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Edit Ticket</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $ticket->ticket_number }} — {{ $ticket->title }}</p>
            </div>
            <a href="{{ route('tickets.show', $ticket) }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Ticket
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('tickets.update', $ticket) }}" method="POST" class="card space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="customer_id" class="label">Customer <span class="text-[var(--red-text)]">*</span></label>
                    <x-custom-select
                        name="customer_id"
                        id="customer_id"
                        :value="old('customer_id', $ticket->customer_id)"
                        placeholder="Select customer…"
                        :options="$customers->mapWithKeys(fn($c) => [$c->id => $c->name . ' — ' . $c->phone . ' (' . ($c->package ?? 'no package') . ')'])->toArray()" />
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="label">Title <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $ticket->title) }}" required placeholder="e.g. Internet sering putus di malam hari" class="input">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">Description <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="description" id="description" rows="5" required placeholder="Describe the problem in detail…" class="input resize-none">{{ old('description', $ticket->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                </div>

                <div>
                    <label for="status" class="label">Status <span class="text-[var(--red-text)]">*</span></label>
                    <x-custom-select
                        name="status"
                        id="status"
                        :value="old('status', $ticket->status)"
                        :options="[
                            'Open' => 'Open',
                            'Checking' => 'Checking',
                            'Waiting Customer' => 'Waiting Customer',
                            'Escalated' => 'Escalated',
                            'Solved' => 'Solved',
                            'Closed' => 'Closed'
                        ]" />
                    <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                </div>

                <div>
                    <label for="category" class="label">Category</label>
                    <x-custom-select
                        name="category"
                        id="category"
                        :value="old('category', $ticket->category)"
                        placeholder="— Select —"
                        :options="[
                            'Internet' => 'Internet',
                            'Hardware' => 'Hardware',
                            'Billing' => 'Billing',
                            'Layanan' => 'Layanan',
                            'Other' => 'Other'
                        ]" />
                    <x-input-error :messages="$errors->get('category')" class="mt-1.5" />
                </div>

                <div>
                    <label for="priority" class="label">Priority <span class="text-[var(--red-text)]">*</span></label>
                    <x-custom-select
                        name="priority"
                        id="priority"
                        :value="old('priority', $ticket->priority)"
                        :options="[
                            'Low' => 'Low',
                            'Medium' => 'Medium',
                            'High' => 'High'
                        ]" />
                    <x-input-error :messages="$errors->get('priority')" class="mt-1.5" />
                </div>

                <div>
                    <label for="olt" class="label">OLT</label>
                    <input type="text" name="olt" id="olt" value="{{ old('olt', $ticket->olt) }}" placeholder="e.g. OLT-01" class="input">
                    <x-input-error :messages="$errors->get('olt')" class="mt-1.5" />
                </div>

                <div>
                    <label for="location" class="label">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $ticket->location) }}" placeholder="e.g. Port 12" class="input">
                    <x-input-error :messages="$errors->get('location')" class="mt-1.5" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('tickets.show', $ticket) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M13.5 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
