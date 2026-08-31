@props(['active' => false])

@php
$classes = $active
    ? 'inline-flex items-center border-b-2 border-[var(--accent)] px-3 py-2 text-[13.5px] font-bold text-[var(--foreground)] transition-colors duration-150'
    : 'inline-flex items-center border-b-2 border-transparent px-3 py-2 text-[13.5px] font-medium text-[var(--muted)] hover:text-[var(--foreground)] hover:border-white/20 transition-colors duration-150';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
