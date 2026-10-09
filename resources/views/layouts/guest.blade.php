<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Apply saved theme before first paint to avoid flash --}}
        <script>
            (function () {
                try {
                    var theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
                    document.documentElement.dataset.theme = theme;
                } catch (e) {}
            })();
        </script>
        <meta name="theme-color" content="#f9f6f0" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#0c0e14" media="(prefers-color-scheme: dark)">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[var(--foreground)]">
        <div class="fixed top-4 right-4 z-50">
            @include('partials.theme-toggle')
        </div>
        <div class="flex min-h-screen flex-col items-center justify-center bg-[var(--background)] px-4 pt-6 sm:pt-0">
            <div class="mb-6 flex items-center gap-2.5">
                <x-application-logo class="h-8 w-8 fill-current" />
                <span class="font-display text-lg font-bold tracking-[-0.01em] text-[var(--foreground)]">Ayyanet</span>
                <span class="mt-1 text-[11px] font-medium text-[var(--muted)]">Support Desk</span>
            </div>

            <div class="w-full max-w-md rounded-lg border border-[var(--border)] bg-[var(--surface)] px-6 py-6 shadow-[var(--card-shadow)] sm:px-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
