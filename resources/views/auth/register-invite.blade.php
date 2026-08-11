<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Silakan lengkapi data berikut untuk membuat akun Anda.') }}
    </div>

    <!-- Email Address (Read Only) -->
    <div class="mb-4">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" type="email" name="email" :value="$invitation->email" readonly />
    </div>

    <!-- Role (Read Only) -->
    <div class="mb-4">
        <x-input-label for="role" :value="__('Role')" />
        <x-text-input id="role" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" type="text" name="role" :value="ucfirst($invitation->role)" readonly />
    </div>

    <form method="POST" action="{{ route('register.invite', $invitation->token) }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-4">
                {{ __('Daftar Akun') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>