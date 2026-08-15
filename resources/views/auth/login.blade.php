<x-guest-layout>
    <!-- Tabbed Navigation Header -->
    <div class="mb-6 flex rounded-lg bg-[var(--surface-2)] p-1 border border-[var(--border)]">
        <div class="flex-1 rounded-md bg-[var(--surface)] py-2 text-center text-sm font-semibold text-[var(--foreground)] shadow-sm border border-[var(--border)]">
            Log In
        </div>
        <div class="flex-1 rounded-md py-2 text-center text-sm font-medium text-[var(--muted)] opacity-70 flex items-center justify-center gap-1 cursor-default" title="Pendaftaran memerlukan link undangan email dari Admin">
            Sign Up
            <span class="text-[10px] bg-[var(--surface-3)] px-1.5 py-0.5 rounded text-[var(--muted)]">Undangan</span>
        </div>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[var(--border-strong)] bg-[var(--surface-2)] text-[var(--accent)] shadow-sm focus:ring-[var(--accent)]" name="remember">
                <span class="ms-2 text-sm text-[var(--muted)]">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-[var(--muted)] underline hover:text-[var(--foreground)] focus:outline-none focus:ring-2 focus:ring-[var(--accent-40)]" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
