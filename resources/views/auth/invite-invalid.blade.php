<x-guest-layout>
    <div class="text-center py-4">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-500/10 text-red-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-[var(--foreground)]">Undangan Tidak Valid / Kedaluwarsa</h3>
        <p class="mt-2 text-xs text-[var(--muted)] leading-relaxed">
            Link undangan ini sudah tidak berlaku (batas waktu 5 jam telah habis) atau telah digunakan. Silakan minta Admin untuk mengirimkan link undangan baru via email.
        </p>
        <div class="mt-6">
            <a href="{{ route('login') }}" class="btn-primary w-full justify-center !py-2">
                {{ __('Kembali ke Login') }}
            </a>
        </div>
    </div>
</x-guest-layout>