<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2 bg-[var(--accent-cta)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[var(--accent-cta-hover)] active:bg-[var(--accent-cta-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-[var(--background)] transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
