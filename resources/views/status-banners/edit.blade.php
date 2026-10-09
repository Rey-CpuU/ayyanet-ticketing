<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Edit Banner</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Update the alert shown at the top of the portal</p>
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
        <form action="{{ route('status-banners.update', $statusBanner) }}" method="POST" class="card space-y-5 p-6">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="label">Title <span class="text-[var(--red-text)]">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $statusBanner->title) }}" required placeholder="e.g. Maintenance Terjadwal Malam Ini" class="input">
                    <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="message" class="label">Message <span class="text-[var(--red-text)]">*</span></label>
                    <textarea name="message" id="message" rows="3" required placeholder="What users should know…" class="input resize-none">{{ old('message', $statusBanner->message) }}</textarea>
                    <x-input-error :messages="$errors->get('message')" class="mt-1.5" />
                </div>

                <div>
                    <label for="type" class="label">Type</label>
                    <select name="type" id="type" class="input">
                        @foreach (['outage', 'maintenance', 'warning', 'info'] as $type)
                            <option value="{{ $type }}" @selected(old('type', $statusBanner->type) === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end pb-1.5">
                    <label class="flex cursor-pointer items-center gap-2.5 text-[13px] font-medium text-[var(--foreground)]">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $statusBanner->is_active)) class="h-4 w-4 rounded border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--accent)] focus:ring-[var(--accent)]">
                        Active (show on portal)
                    </label>
                </div>

                <div>
                    <label for="starts_at" class="label">Starts at <span class="text-[var(--muted-strong)]">(optional)</span></label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ old('starts_at', $statusBanner->starts_at?->format('Y-m-d\TH:i')) }}" class="input">
                    <x-input-error :messages="$errors->get('starts_at')" class="mt-1.5" />
                </div>

                <div>
                    <label for="ends_at" class="label">Ends at <span class="text-[var(--muted-strong)]">(optional)</span></label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ old('ends_at', $statusBanner->ends_at?->format('Y-m-d\TH:i')) }}" class="input">
                    <x-input-error :messages="$errors->get('ends_at')" class="mt-1.5" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('status-banners.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
