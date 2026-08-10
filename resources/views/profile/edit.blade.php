<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">
                {{ __('Profile') }}
            </h2>
            <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Manage your account settings</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl space-y-6">
            <div class="card p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card border-[var(--red-text-20)] p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
