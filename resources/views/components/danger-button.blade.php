<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[var(--red-solid)] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[var(--red-solid-hover)] active:bg-[var(--red-solid-hover)] focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-[var(--background)] transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
