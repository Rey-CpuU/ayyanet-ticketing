{{-- One item of the mobile bottom tab bar: icon (slot) + small label. Renders a link when href is given, otherwise a button. --}}
@props(['href' => null, 'active' => false, 'label', 'badge' => 0])

@php
$classes = 'relative flex h-full min-w-0 flex-1 flex-col items-center justify-center gap-0.5 px-1 text-[10.5px] leading-none transition-colors duration-150 focus:outline-none focus-visible:bg-[var(--hover-overlay)] '
    .($active ? 'font-semibold text-[var(--accent)]' : 'font-medium text-[var(--muted)] hover:text-[var(--foreground)]');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} @if ($active) aria-current="page" @endif>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes]) }}>
@endif
        <span class="relative inline-flex h-6 w-6 items-center justify-center">
            {{ $slot }}
            @if ($badge > 0)
                <span class="absolute -right-2 -top-1 min-w-[16px] rounded-full bg-[var(--red-solid)] px-1 text-center font-mono text-[9.5px] font-bold leading-[16px] text-white">{{ $badge > 99 ? '99+' : $badge }}</span>
            @endif
        </span>
        <span class="max-w-full truncate">{{ $label }}</span>
@if ($href)
    </a>
@else
    </button>
@endif
