<x-guest-layout>
    <div class="mb-4 text-sm text-red-600 dark:text-red-400">
        {{ __('Link undangan tidak valid atau telah kedaluwarsa. Silakan hubungi admin untuk mengirimkan undangan baru.') }}
    </div>

    <div class="mt-4 flex items-center justify-between">
        <a href="{{ route('login') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
            {{ __('Kembali ke Login') }}
        </a>
    </div>
</x-guest-layout>