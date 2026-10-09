@php
    $statusBanners = $statusBanners ?? \App\Models\StatusBanner::active()->latest()->get(['id', 'title', 'message', 'type']);
@endphp

{{-- Active status banners (outage/maintenance announcements) for staff. Refreshed every minute. --}}
<div id="status-banners" class="sb-wrap" data-url="{{ route('status-banners.active') }}" aria-live="polite">
    @foreach($statusBanners as $banner)
        <div class="sb sb-{{ $banner->type }}" role="status">
            <span class="sb-type">{{ $banner->type }}</span>
            <strong class="sb-title">{{ $banner->title }}</strong>
            <span class="sb-message">{{ $banner->message }}</span>
        </div>
    @endforeach
</div>

@once
    <style>
        .sb-wrap { display: flex; flex-direction: column; }
        .sb {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 4px 12px;
            padding: 10px 20px;
            border-bottom: 1px solid;
            font-family: inherit;
            font-size: 13px;
        }
        .sb-type { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
        .sb-message { opacity: 0.85; }
        .sb-outage { background: #450a0a; border-color: #7f1d1d; color: #fecaca; }
        .sb-maintenance { background: #1e1b4b; border-color: #3730a3; color: #c7d2fe; }
        .sb-warning { background: #451a03; border-color: #92400e; color: #fde68a; }
        .sb-info { background: #082f49; border-color: #075985; color: #bae6fd; }
    </style>
    <script>
        (function () {
            const wrap = document.getElementById('status-banners');
            if (!wrap) return;

            const types = ['outage', 'maintenance', 'warning', 'info'];
            const render = function (banners) {
                wrap.replaceChildren();
                banners.forEach(function (banner) {
                    const row = document.createElement('div');
                    row.className = 'sb sb-' + (types.includes(banner.type) ? banner.type : 'info');
                    row.setAttribute('role', 'status');
                    [['span', 'sb-type', banner.type], ['strong', 'sb-title', banner.title], ['span', 'sb-message', banner.message]]
                        .forEach(function (part) {
                            const el = document.createElement(part[0]);
                            el.className = part[1];
                            el.textContent = part[2];
                            row.appendChild(el);
                        });
                    wrap.appendChild(row);
                });
            };

            setInterval(function () {
                fetch(wrap.dataset.url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (res) { return res.ok ? res.json() : null; })
                    .then(function (data) { if (Array.isArray(data)) render(data); })
                    .catch(function () {});
            }, 60000);
        })();
    </script>
@endonce
