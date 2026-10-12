<section class="space-y-3">
    <header>
        <h2 class="text-lg font-medium text-[var(--foreground)]">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-[var(--muted)]">
            Akun tidak dapat dihapus sendiri karena terhubung dengan riwayat tiket. Jika akun ini tidak lagi digunakan, hubungi admin untuk menonaktifkannya.
        </p>
    </header>

    @if (session('error'))
        <p class="rounded-md border border-[var(--red-text-30)] bg-[var(--red-text-10)] px-4 py-3 text-[13px] font-medium text-[var(--red-bright)]">
            {{ session('error') }}
        </p>
    @endif
</section>
