@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[var(--accent)] text-sm font-medium leading-5 text-[var(--foreground)] focus:outline-none focus:border-[var(--accent)] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-[var(--muted)] hover:text-[var(--foreground)] hover:border-[var(--border-strong)] focus:outline-none focus:text-[var(--foreground)] focus:border-[var(--border-strong)] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
