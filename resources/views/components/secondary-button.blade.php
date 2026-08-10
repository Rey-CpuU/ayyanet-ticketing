<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--surface-2)] border border-[var(--border-strong)] rounded-md font-semibold text-xs text-[var(--muted)] uppercase tracking-widest shadow-sm hover:bg-[var(--surface-3)] hover:text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-[var(--background)] disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
