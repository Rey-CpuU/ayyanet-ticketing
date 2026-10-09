@props(['type' => 'card', 'lines' => 3])

@if($type === 'card')
    <div class="skeleton-card" aria-hidden="true">
        <div class="skeleton-line skeleton-line--title"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line skeleton-line--short"></div>
    </div>
@elseif($type === 'text')
    <div class="skeleton-text" aria-hidden="true">
        @for($i = 0; $i < $lines; $i++)
            <div class="skeleton-line" style="width: {{ 60 + rand(0, 30) }}%;"></div>
        @endfor
    </div>
@elseif($type === 'row')
    <div class="skeleton-row" aria-hidden="true">
        <div class="skeleton-line skeleton-line--circle"></div>
        <div class="skeleton-line" style="flex: 2;"></div>
        <div class="skeleton-line" style="flex: 1;"></div>
        <div class="skeleton-line" style="flex: 1;"></div>
        <div class="skeleton-line skeleton-line--short"></div>
    </div>
@endif

<style>
    .skeleton-card {
        background: rgba(23, 32, 48, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 18px;
        padding: 18px;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .skeleton-text {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 12px;
    }

    .skeleton-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 8px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
    }

    .skeleton-line {
        height: 14px;
        border-radius: 8px;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.08) 25%, rgba(148, 163, 184, 0.18) 50%, rgba(148, 163, 184, 0.08) 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s ease-in-out infinite;
    }

    .skeleton-line--title {
        width: 40%;
        height: 20px;
    }

    .skeleton-line--short {
        width: 25%;
    }

    .skeleton-line--circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
</style>