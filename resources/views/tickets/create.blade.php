<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">New Ticket</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Create a support ticket for a customer</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Tickets
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        @php($hasOutage = \App\Models\StatusBanner::active()->where('type', 'outage')->exists())
        @if ($hasOutage)
            <div class="mb-5 flex items-start gap-3 rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3.5">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-[var(--red-bright)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <p class="text-[13px] font-semibold text-[var(--red-bright)]">Known outage active</p>
                    <p class="mt-0.5 text-[12.5px] text-[var(--pink-text)]">There's an active outage banner. Check the status banner above before submitting — duplicate tickets slow down response time.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('tickets.store') }}" method="POST" class="card space-y-5 p-6" x-data="ticketForm()">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="customer_id" class="label">Customer <span class="text-[var(--red-text)]">*</span></label>
                    <x-custom-select
                        name="customer_id"
                        id="customer_id"
                        :value="old('customer_id', '')"
                        placeholder="Select customer…"
                        :options="$customers->mapWithKeys(fn($c) => [$c->id => $c->name . ' — ' . $c->phone . ' (' . ($c->package ?? 'no package') . ')'])->toArray()" />
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="label">Title <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Internet sering putus di malam hari" class="input" x-on:input.debounce.300ms="classify()">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">Description <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="description" id="description" rows="5" required placeholder="Describe the problem in detail…" class="input resize-none" x-on:input.debounce.300ms="classify()">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                </div>

                <div>
                    <label for="category" class="label">Category</label>
                    <x-custom-select
                        name="category"
                        id="category"
                        :value="old('category', '')"
                        placeholder="— Select —"
                        :options="[
                            'Internet' => 'Internet',
                            'Hardware' => 'Hardware',
                            'Billing' => 'Billing',
                            'Layanan' => 'Layanan',
                            'Other' => 'Other'
                        ]" />
                </div>

                <div>
                    <label for="priority" class="label">Priority</label>
                    <x-custom-select
                        name="priority"
                        id="priority"
                        :value="old('priority', 'Medium')"
                        :options="[
                            'Low' => 'Low',
                            'Medium' => 'Medium',
                            'High' => 'High'
                        ]" />
                </div>

                <div class="sm:col-span-2" x-show="detected" x-cloak>
                    <div class="flex items-center gap-2.5 rounded-md border border-[var(--accent-40)] bg-[var(--accent-soft)] px-3.5 py-2.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-[var(--accent-text)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 2l2.4 7.2L22 12l-7.6 2.8L12 22l-2.4-7.2L2 12l7.6-2.8L12 2z"/>
                        </svg>
                        <p class="text-[12.5px] text-[var(--accent-text-strong)]">
                            Detected:
                            <span class="font-semibold" x-text="detected.category"></span>
                            <span class="mx-1 text-[var(--muted)]">·</span>
                            <span class="font-semibold" x-text="detected.priority"></span>
                            <span class="text-[var(--muted)]">— auto-filled, you can adjust below</span>
                        </p>
                    </div>
                </div>

                <div>
                    <label for="olt" class="label">OLT</label>
                    <input type="text" name="olt" id="olt" value="{{ old('olt') }}" placeholder="e.g. OLT-01" class="input">
                </div>

                <div>
                    <label for="location" class="label">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" placeholder="e.g. Port 12" class="input">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('tickets.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M13.5 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                    </svg>
                    Create Ticket
                </button>
            </div>
        </form>
    </div>

    <script>
        function ticketForm() {
            return {
                detected: null,
                manual: false,
                async classify() {
                    const title = document.getElementById('title').value.trim();
                    if (!title) {
                        this.detected = null;
                        return;
                    }
                    const description = document.getElementById('description').value;
                    try {
                        const res = await fetch('/tickets/classify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ title, description }),
                        });
                        if (!res.ok) return;
                        const data = await res.json();
                        this.detected = data;
                        if (!this.manual) {
                            document.getElementById('category').value = data.category;
                            document.getElementById('priority').value = data.priority;
                        }
                    } catch (e) {}
                },
            };
        }
    </script>
</x-app-layout>
