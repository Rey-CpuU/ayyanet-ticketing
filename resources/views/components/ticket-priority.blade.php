@props(['priority'])

@php
    $key = match ($priority) {
        'High' => 'high',
        'Medium' => 'medium',
        default => 'default',
    };
@endphp

<span {{ $attributes->class(['flex items-center gap-1.5 text-[11px] font-medium']) }}>
    <span class="h-1.5 w-1.5 rounded-full priority-dot-{{ $key }}"></span>
    <span class="font-mono uppercase tracking-[0.04em] priority-{{ $key }}">{{ $priority }}</span>
</span>
