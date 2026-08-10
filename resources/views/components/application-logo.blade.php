@props(['class' => 'h-8 w-auto'])

<svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $class]) }} aria-hidden="true">
    <rect x="1" y="2.5" width="14" height="11" rx="2" fill="var(--accent)"/>
    <path d="M1 5l7-3 7 3" fill="#fff" opacity=".92"/>
    <path d="M5.5 8.5l2 2 3-3.5" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
