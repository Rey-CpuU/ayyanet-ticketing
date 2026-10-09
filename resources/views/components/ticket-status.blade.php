@props(['status'])

@php
    $badgeClass = match ($status) {
        'Open' => 'badge-red',
        'Checking' => 'badge-violet',
        'Waiting Customer' => 'badge-amber',
        'Escalated' => 'badge-orange',
        'Solved' => 'badge-green',
        default => 'badge-slate',
    };
@endphp

<span {{ $attributes->class(['badge', $badgeClass]) }}>{{ $status }}</span>
