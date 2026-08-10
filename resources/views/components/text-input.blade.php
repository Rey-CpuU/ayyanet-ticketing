@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--foreground)] placeholder:text-[var(--muted)] focus:border-[var(--accent)] focus:ring-[var(--accent)] rounded-md shadow-sm']) }}>
