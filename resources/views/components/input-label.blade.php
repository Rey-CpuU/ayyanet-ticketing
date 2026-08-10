@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs uppercase tracking-[0.06em] text-[var(--muted)] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
