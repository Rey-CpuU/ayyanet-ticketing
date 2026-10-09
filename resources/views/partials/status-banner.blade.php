@php
    $initialBanners = ($statusBanners ?? \App\Models\StatusBanner::active()->latest()->get())->map(fn ($b) => [
        'id' => $b->id,
        'title' => $b->title,
        'message' => $b->message,
        'type' => $b->type,
    ])->values();
@endphp

{{-- Active status banners (outage/maintenance announcements) as a carousel strip; refreshed every minute. --}}
<div x-data="statusBannerComponent(@js(route('status-banners.active')))" x-show="banners.length > 0" x-cloak>
    <div
        class="border-b relative transition-colors duration-300"
        :style="{
            backgroundColor: currentBanner ? BANNER_STYLES[currentBanner.type]?.bg : 'transparent',
            borderColor: currentBanner ? BANNER_STYLES[currentBanner.type]?.border : 'transparent',
        }"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:px-8 gap-3">
            {{-- Left Navigation Arrow (shown if multiple banners) --}}
            <button type="button"
                    @click="prev()"
                    x-show="banners.length > 1"
                    aria-label="Previous banner"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-black/10 dark:border-white/15 bg-black/5 dark:bg-white/10 text-[var(--foreground)] hover:bg-black/10 dark:hover:bg-white/20 transition">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>

            {{-- Banner content --}}
            <div class="flex flex-1 flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 text-center sm:text-left min-w-0 py-0.5">
                <span
                    class="font-mono text-[10px] font-bold uppercase tracking-[0.12em] px-2 py-0.5 rounded border border-black/10 dark:border-white/10"
                    :style="{
                        color: currentBanner ? BANNER_STYLES[currentBanner.type]?.accent : 'inherit',
                        backgroundColor: 'rgba(0,0,0,0.06)'
                    }"
                    x-text="currentBanner?.type"
                ></span>
                <span class="text-[13px] font-bold text-[var(--foreground)]" x-text="currentBanner?.title"></span>
                <span class="min-w-0 text-[12.5px] truncate max-w-2xl" :style="{ color: currentBanner ? BANNER_STYLES[currentBanner.type]?.muted : 'inherit' }" x-text="currentBanner?.message"></span>
            </div>

            {{-- Right Controls: Indicator & Next Arrow --}}
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="banners.length > 1">
                    <span class="font-mono text-[11px] font-medium text-[var(--muted)] opacity-80"
                          x-text="(currentIndex + 1) + ' / ' + banners.length">
                    </span>
                </template>

                <button type="button"
                        @click="next()"
                        x-show="banners.length > 1"
                        aria-label="Next banner"
                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-black/10 dark:border-white/15 bg-black/5 dark:bg-white/10 text-[var(--foreground)] hover:bg-black/10 dark:hover:bg-white/20 transition">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script id="status-banners-data" type="application/json">
    {!! json_encode($initialBanners, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>

<script>
    window.BANNER_STYLES = {
        outage:      { bg: 'var(--banner-outage-bg)',      border: 'var(--banner-outage-border)',      accent: 'var(--banner-outage-accent)',      muted: 'var(--banner-outage-muted)' },
        maintenance: { bg: 'var(--banner-maintenance-bg)', border: 'var(--banner-maintenance-border)', accent: 'var(--banner-maintenance-accent)', muted: 'var(--banner-maintenance-muted)' },
        warning:     { bg: 'var(--banner-warning-bg)',     border: 'var(--banner-warning-border)',     accent: 'var(--banner-warning-accent)',     muted: 'var(--banner-warning-muted)' },
        info:        { bg: 'var(--banner-info-bg)',        border: 'var(--banner-info-border)',        accent: 'var(--banner-info-accent)',        muted: 'var(--banner-info-muted)' },
    };

    function statusBannerComponent(activeUrl) {
        let initial = [];
        const el = document.getElementById('status-banners-data');
        if (el) {
            try {
                initial = JSON.parse(el.textContent || '[]');
            } catch (e) {}
        }

        return {
            banners: initial,
            currentIndex: 0,
            get currentBanner() {
                if (this.banners.length === 0) return null;
                return this.banners[this.currentIndex % this.banners.length];
            },
            next() {
                if (this.banners.length > 1) {
                    this.currentIndex = (this.currentIndex + 1) % this.banners.length;
                }
            },
            prev() {
                if (this.banners.length > 1) {
                    this.currentIndex = (this.currentIndex - 1 + this.banners.length) % this.banners.length;
                }
            },
            init() {
                this.refresh();
                setInterval(() => this.refresh(), 60000);
            },
            async refresh() {
                try {
                    const res = await fetch(activeUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (res.ok) {
                        const data = await res.json();
                        if (Array.isArray(data)) this.banners = data;
                        if (this.currentIndex >= this.banners.length) {
                            this.currentIndex = 0;
                        }
                    }
                } catch (e) {}
            }
        };
    }
</script>
