<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Status Banners</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">{{ $banners->count() }} banner(s) — shown at the top of the portal</p>
            </div>
            <a href="{{ route('status-banners.create') }}" class="btn-primary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                New Banner
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
                <table class="w-full min-w-[860px] text-left">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--surface)]">
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Type</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Title</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Message</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Active</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Window</th>
                            <th class="px-5 py-3 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)]">
                        @forelse ($banners as $banner)
                            @php
                                $typeStyle = match ($banner->type) {
                                    'outage' => ['bg' => 'color-mix(in srgb, var(--red-text) 12%, transparent)', 'text' => 'var(--red-bright)'],
                                    'maintenance' => ['bg' => 'color-mix(in srgb, var(--amber-text) 12%, transparent)', 'text' => 'var(--amber-text)'],
                                    'warning' => ['bg' => 'color-mix(in srgb, var(--orange-text) 14%, transparent)', 'text' => 'var(--orange-text)'],
                                    default => ['bg' => 'color-mix(in srgb, var(--violet-text) 15%, transparent)', 'text' => 'var(--violet-text)'],
                                };
                            @endphp
                            <tr class="transition hover:bg-[var(--hover-overlay)]">
                                <td class="px-5 py-4">
                                    <span class="badge font-mono uppercase tracking-[0.06em]" style="background: {{ $typeStyle['bg'] }}; color: {{ $typeStyle['text'] }};">{{ $banner->type === 'info' ? 'Tentang' : $banner->type }}</span>
                                </td>
                                <td class="px-5 py-4 text-[13.5px] font-medium text-[var(--foreground)]">{{ $banner->title }}</td>
                                <td class="px-5 py-4">
                                    <span class="block max-w-[340px] truncate text-[12.5px] text-[var(--muted)]">{{ $banner->message }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="flex items-center gap-1.5 text-[12px] font-medium {{ $banner->is_active ? 'text-[var(--green-text)]' : 'text-[var(--muted-strong)]' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $banner->is_active ? 'bg-[var(--green-text)]' : 'bg-[var(--muted-strong)]' }}"></span>
                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-[11.5px] text-[var(--muted)]">
                                    @if ($banner->starts_at || $banner->ends_at)
                                        {{ $banner->starts_at?->format('d M H:i') ?? '—' }} → {{ $banner->ends_at?->format('d M H:i') ?? '—' }}
                                    @else
                                        Always
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('status-banners.edit', $banner) }}" class="btn-secondary !px-3 !py-1.5 text-xs">Edit</a>
                                        <form method="POST" action="{{ route('status-banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger !px-3 !py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <p class="text-[13.5px] font-medium text-[var(--muted)]">No banners yet</p>
                                    <a href="{{ route('status-banners.create') }}" class="mt-2 inline-block text-[12.5px] font-semibold text-[var(--accent)] hover:text-[var(--accent-text)]">Create the first banner →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
