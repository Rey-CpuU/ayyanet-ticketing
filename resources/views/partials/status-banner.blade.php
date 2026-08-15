@php
    $initialBanners = ($statusBanners ?? collect())->map(fn ($b) => [
        'id' => $b->id,
        'title' => $b->title,
        'message' => $b->message,
        'type' => $b->type,
    ])->values();
@endphp

<div x-data="statusBannerComponent()">
    <template x-for="banner in banners" :key="banner.id">
        <div
            class="border-b"
            :style="{
                backgroundColor: BANNER_STYLES[banner.type].bg,
                borderColor: BANNER_STYLES[banner.type].border,
            }"
        >
            <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2.5 sm:px-6 lg:px-8">
                <span
                    class="font-mono text-[10px] font-bold uppercase tracking-[0.12em]"
                    :style="{ color: BANNER_STYLES[banner.type].accent }"
                    x-text="banner.type"
                ></span>
                <span class="text-[13px] font-semibold text-[var(--foreground)]" x-text="banner.title"></span>
                <span class="min-w-0 text-[12.5px]" :style="{ color: BANNER_STYLES[banner.type].muted }" x-text="banner.message"></span>
            </div>
        </div>
    </template>
</div>

<script id="status-banners-data" type="application/json">
    {!! json_encode($initialBanners) !!}
</script>

<script>
    window.BANNER_STYLES = {
        outage:      { bg: 'var(--banner-outage-bg)',      border: 'var(--banner-outage-border)',      accent: 'var(--banner-outage-accent)',      muted: 'var(--banner-outage-muted)' },
        maintenance: { bg: 'var(--banner-maintenance-bg)', border: 'var(--banner-maintenance-border)', accent: 'var(--banner-maintenance-accent)', muted: 'var(--banner-maintenance-muted)' },
        warning:     { bg: 'var(--banner-warning-bg)',     border: 'var(--banner-warning-border)',     accent: 'var(--banner-warning-accent)',     muted: 'var(--banner-warning-muted)' },
        info:        { bg: 'var(--banner-info-bg)',        border: 'var(--banner-info-border)',        accent: 'var(--banner-info-accent)',        muted: 'var(--banner-info-muted)' },
    };

    function statusBannerComponent() {
        let initial = [];
        const el = document.getElementById('status-banners-data');
        if (el) {
            try {
                initial = JSON.parse(el.textContent || '[]');
            } catch (e) {}
        }

        return {
            banners: initial,
            init() {
                this.refresh();
                setInterval(() => this.refresh(), 60000);
            },
            async refresh() {
                try {
                    const res = await fetch('/status-banners/active', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) this.banners = await res.json();
                } catch (e) {}
            }
        };
    }
</script>
