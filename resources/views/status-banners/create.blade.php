<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">New Banner</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Announce a status, maintenance, or outage alert</p>
            </div>
            <a href="{{ route('status-banners.index') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Banners
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('status-banners.store') }}" method="POST" class="card space-y-5 p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="label">Title <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Maintenance Terjadwal Malam Ini" class="input">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="message" class="label">Message <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="message" id="message" rows="3" required placeholder="What users should know…" class="input resize-none">{{ old('message') }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-1.5" />
                </div>

                <div>
                    <label for="type" class="label">Type</label>
                    <select name="type" id="type" class="input">
                        @foreach (['outage', 'maintenance', 'warning', 'info'] as $type)
                            <option value="{{ $type }}" @selected(old('type', 'info') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end pb-1.5">
                    <label class="flex cursor-pointer items-center gap-2.5 text-[13px] font-medium text-[var(--foreground)]">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="h-4 w-4 rounded border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--accent)] focus:ring-[var(--accent)]">
                        Active (show on portal)
                    </label>
                </div>

                <div>
                    <label for="starts_at" class="label">Starts at <span class="text-[var(--muted-strong)]">(optional)</span></label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at') }}" class="input">
                    <x-input-error :messages="$errors->get('starts_at')" class="mt-1.5" />
                </div>

                <div>
                    <label for="ends_at" class="label">Ends at <span class="text-[var(--muted-strong)]">(optional)</span></label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at') }}" class="input">
                    <x-input-error :messages="$errors->get('ends_at')" class="mt-1.5" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('status-banners.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Banner</button>
            </div>
        </form>
    </div>
</x-app-layout>
