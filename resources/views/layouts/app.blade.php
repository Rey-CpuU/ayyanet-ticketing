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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[var(--background)] text-[var(--foreground)]">
        <div class="min-h-screen bg-[var(--background)]">
            <div class="fx-ambient" aria-hidden="true"></div>
            @include('partials.status-banner', ['statusBanners' => \App\Models\StatusBanner::active()->latest()->get()])
            @include('layouts.navigation')

            @isset($header)
                <header class="relative z-10 border-b border-[var(--border)] bg-[var(--surface-90)] backdrop-blur-sm">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="relative z-10">
                {{ $slot }}
            </main>

            @include('partials.inactivity-modal')
        </div>
    </body>
</html>
