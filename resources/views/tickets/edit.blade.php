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
                Kembali ke Tiket
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data" class="card space-y-5 p-6"
            x-data="{ status: @js(old('status', $ticket->status)), current: @js($ticket->status) }"
            @change="if ($event.target.name === 'status') status = $event.target.value">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="customer_search" class="label">Customer <span class="text-[var(--red-text)]">*</span></label>
                    <x-customer-picker :selected="$selectedCustomer" />
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="title" class="label">Judul <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $ticket->title) }}" required maxlength="255" class="input">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="label">Deskripsi <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="description" id="description" rows="5" required class="input resize-none">{{ old('description', $ticket->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
                </div>

                <div>
                    <label for="status" class="label">Status</label>
                    {{-- Only the current status and the transitions the workflow allows are offered. --}}
                    <x-custom-select
                        name="status"
                        id="status"
                        :value="old('status', $ticket->status)"
                        :options="collect($statusOptions)->mapWithKeys(fn ($s) => [$s->value => $s->value])->all()" />
                    <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                </div>

                <div>
                    <label for="category" class="label">Kategori</label>
                    {{-- Keep a legacy topic category (e.g. Internet, from the former Telegram bot) selectable. --}}
                    <x-custom-select
                        name="category"
                        id="category"
                        :value="old('category', $ticket->category)"
                        placeholder="— Pilih —"
                        :options="collect([...App\Models\Ticket::CATEGORIES, $ticket->category])->filter()->unique()->mapWithKeys(fn ($c) => [$c => $c])->all()" />
                    <x-input-error :messages="$errors->get('category')" class="mt-1.5" />
                </div>

                <div>
                    <label for="priority" class="label">Prioritas</label>
                    <x-custom-select
                        name="priority"
                        id="priority"
                        :value="old('priority', $ticket->priority)"
                        :options="['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High']" />
                    <x-input-error :messages="$errors->get('priority')" class="mt-1.5" />
                </div>

                <div>
                    <label for="olt" class="label">OLT</label>
                    <input type="text" name="olt" id="olt" value="{{ old('olt', $ticket->olt) }}" maxlength="255" placeholder="mis. OLT-01" class="input">
                    <x-input-error :messages="$errors->get('olt')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="location" class="label">Lokasi</label>
                    <input type="text" name="location" id="location" value="{{ old('location', $ticket->location) }}" maxlength="255" placeholder="mis. Port 12" class="input">
                    <x-input-error :messages="$errors->get('location')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2" x-show="['Solved', 'Closed'].includes(status) || {{ $errors->has('resolution_note') ? 'true' : 'false' }}">
                    <label for="resolution_note" class="label">Catatan Penyelesaian</label>
                    <textarea name="resolution_note" id="resolution_note" rows="3" maxlength="2000" class="input resize-none"
                        placeholder="Wajib diisi saat status Solved (atau Closed tanpa penyelesaian)">{{ old('resolution_note', $ticket->resolution_note) }}</textarea>
                    <x-input-error :messages="$errors->get('resolution_note')" class="mt-1.5" />
                </div>

                <x-file-dropzone
                    name="attachment"
                    class="sm:col-span-2"
                    :current-name="$ticket->attachment_path ? basename($ticket->attachment_path) : null"
                    :current-url="$ticket->attachment_path ? route('tickets.attachment', $ticket) : null" />
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('tickets.show', $ticket) }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M13.5 2L1 7l5 2.5L8.5 15l5-13Z" fill="currentColor" opacity=".85"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
