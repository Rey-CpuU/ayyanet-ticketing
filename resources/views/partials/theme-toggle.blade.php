<button
    type="button"
    aria-label="Toggle light/dark mode"
    title="Toggle light/dark mode"
    x-data="{ theme: document.documentElement.dataset.theme || 'light' }"
    @click="
        theme = theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = theme;
        try { localStorage.setItem('theme', theme); } catch (e) {}
    "
    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-[var(--muted)] transition hover:bg-[var(--surface-2)] hover:text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--accent-40)]"
>
    <svg x-cloak x-show="theme === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.22 4.22l1.06 1.06M18.72 18.72l1.06 1.06M3 12h1.5M19.5 12H21M4.22 19.78l1.06-1.06M18.72 5.28l1.06-1.06M12 8a4 4 0 100 8 4 4 0 000-8z" />
    </svg>
    <svg x-cloak x-show="theme === 'light'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
    </svg>
</button>
