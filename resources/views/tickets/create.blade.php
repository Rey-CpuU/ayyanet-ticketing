<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">New Ticket</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Buat tiket support untuk customer</p>
            </div>
            <a href="{{ route('tickets.index') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali ke Tickets
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        @php $hasOutage = \App\Models\StatusBanner::active()->where('type', 'outage')->exists(); @endphp
        @if ($hasOutage)
            <div class="mb-5 flex items-start gap-3 rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3.5">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-[var(--red-bright)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <p class="text-[13px] font-semibold text-[var(--red-bright)]">Ada gangguan (outage) aktif</p>
                    <p class="mt-0.5 text-[12.5px] text-[var(--pink-text)]">Cek banner status di atas sebelum membuat tiket — tiket duplikat memperlambat waktu respon.</p>
                </div>
            </div>
        @endif

        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="card space-y-5 p-6"
            x-data="ticketForm(@js(route('tickets.classify')))">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="customer_search" class="label">Customer <span class="text-[var(--red-text)]">*</span></label>
                    <x-customer-picker :selected="$selectedCustomer" />
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="label">Judul <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255" placeholder="mis. Internet sering putus di malam hari" class="input" x-on:input.debounce.400ms="classify()">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">Deskripsi <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="description" id="description" rows="5" required placeholder="Jelaskan masalah pelanggan secara detail…" class="input resize-none" x-on:input.debounce.400ms="classify()">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                </div>

                <div>
                    <label for="category" class="label">Kategori (saluran)</label>
                    <x-custom-select
                        name="category"
                        id="category"
                        :value="old('category', 'Email')"
                        :options="array_combine(App\Models\Ticket::CATEGORIES, App\Models\Ticket::CATEGORIES)" />
                    <x-input-error :messages="$errors->get('category')" class="mt-1.5" />
                </div>

                <div>
                    <label for="priority" class="label">Prioritas</label>
                    <x-custom-select
                        name="priority"
                        id="priority"
                        :value="old('priority', 'Medium')"
                        :options="['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High']" />
                    <x-input-error :messages="$errors->get('priority')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2" x-show="detected" x-cloak>
                    <div class="flex flex-wrap items-center gap-2.5 rounded-md border border-[var(--accent-40)] bg-[var(--accent-soft)] px-3.5 py-2.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-[var(--accent-text)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 2l2.4 7.2L22 12l-7.6 2.8L12 22l-2.4-7.2L2 12l7.6-2.8L12 2z"/>
                        </svg>
                        <p class="flex-1 text-[12.5px] text-[var(--accent-text-strong)]">
                            Terdeteksi:
                            <span class="font-semibold" x-text="detected?.category"></span>
                            <span class="mx-1 text-[var(--muted)]">·</span>
                            prioritas <span class="font-semibold" x-text="detected?.priority"></span>
                        </p>
                        <button type="button" @click="applyPriority()" class="text-[12px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Terapkan prioritas</button>
                    </div>
                </div>

                <div>
                    <label for="olt" class="label">OLT</label>
                    <input type="text" name="olt" id="olt" value="{{ old('olt') }}" maxlength="255" placeholder="mis. OLT-01" class="input">
                    <x-input-error :messages="$errors->get('olt')" class="mt-1.5" />
                </div>

                <div>
                    <label for="location" class="label">Lokasi</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}" maxlength="255" placeholder="mis. Port 12" class="input">
                    <x-input-error :messages="$errors->get('location')" class="mt-1.5" />
                </div>

                <x-file-dropzone name="attachment" class="sm:col-span-2" />
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('tickets.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M13.5 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                    </svg>
                    Buat Tiket
                </button>
            </div>
        </form>
    </div>

    <script>
        function ticketForm(classifyUrl) {
            return {
                detected: null,
                async classify() {
                    const title = document.getElementById('title').value.trim();
                    if (title.length < 5) {
                        this.detected = null;
                        return;
                    }
                    try {
                        const res = await fetch(classifyUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ title, description: document.getElementById('description').value }),
                        });
                        if (res.ok) this.detected = await res.json();
                    } catch (e) {}
                },
                applyPriority() {
                    if (!this.detected) return;
                    // The priority select is an Alpine custom-select; drive it through its own state.
                    const input = document.getElementById('priority');
                    const root = input?.closest('[x-data]');
                    if (root && window.Alpine) {
                        window.Alpine.$data(root).selectOption(this.detected.priority, this.detected.priority);
                    }
                },
            };
        }
    </script>
</x-app-layout>
