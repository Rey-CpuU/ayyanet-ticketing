<x-guest-layout>
    <!-- Tabbed Navigation Header -->
    <div class="mb-6 flex rounded-lg bg-[var(--surface-2)] p-1 border border-[var(--border)]">
        <a href="{{ route('login') }}" class="flex-1 rounded-md py-2 text-center text-sm font-medium text-[var(--muted)] hover:text-[var(--foreground)] transition">
            Log In
        </a>
        <div class="flex-1 rounded-md bg-[var(--accent)] py-2 text-center text-sm font-bold text-white shadow-sm">
            Sign Up
        </div>
    </div>

    <!-- Sign Up Title Header -->
    <div class="mb-5 text-center">
        <h2 class="text-lg font-bold text-[var(--foreground)] tracking-tight">Pendaftaran Akun Baru</h2>
        <p class="text-xs text-[var(--muted)] mt-1">Lengkapi data diri Anda untuk mengaktifkan akun Ayyanet</p>
    </div>

    <!-- Invitation Badge -->
    <div class="mb-5 rounded-lg border border-[var(--accent-40)] bg-[var(--accent-soft)] p-3.5 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--accent)] text-white text-sm font-bold shrink-0">
                ✓
            </div>
            <div>
                <p class="text-[13px] font-semibold text-[var(--foreground)]">Undangan Akses Resmi</p>
                <p class="text-[11.5px] text-[var(--muted)]">Role: <span class="font-bold text-[var(--accent-text-strong)]">{{ ucfirst($invitation->role) }}</span></p>
            </div>
        </div>
        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[var(--muted)] bg-[var(--surface)] px-2.5 py-1 rounded border border-[var(--border)] shrink-0">
            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            5 Jam
        </span>
    </div>

    <form method="POST" action="{{ route('register.invite', $invitation->token) }}" class="space-y-4">
        @csrf

        <!-- Email Address (Locked & Pre-filled) -->
        <div>
            <x-input-label for="email" :value="__('Email (Terunci)')" />
            <div class="relative mt-1">
                <input id="email" type="email" name="email" value="{{ $invitation->email }}" readonly
                    class="block w-full rounded-md border border-[var(--border)] bg-[var(--surface-2)] py-2 pl-3 pr-10 text-sm font-medium text-[var(--foreground)] opacity-90 cursor-not-allowed focus:outline-none" />
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="h-4 w-4 text-[var(--muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap Anda" />
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter (huruf besar/kecil, angka & simbol)" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full btn-primary justify-center !py-2.5 text-sm font-bold tracking-wide">
                Daftar & Ke Halaman Login
            </button>
        </div>
    </form>
</x-guest-layout>