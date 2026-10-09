@php
    $navUser = Auth::user();
    $isManager = $navUser?->hasRole('admin', 'cs') ?? false;
    $isField = $navUser?->hasRole('lapangan') ?? false;
    $isAdmin = $navUser?->isAdmin() ?? false;
    $canCreateTicket = $navUser?->can('create', App\Models\Ticket::class) ?? false;
    $homeUrl = $isField ? route('my.tickets') : route('dashboard');

    $ticketsActive = request()->routeIs('tickets.*') && ! request()->routeIs('tickets.create');
    $customersActive = request()->routeIs('customers.*');
    $kelolaActive = request()->routeIs('reports.*', 'status-banners.*', 'users.*');
    $moreActive = $kelolaActive || request()->routeIs('settings.*', 'profile.*');
    $navUnread = $navUser ? $navUser->unreadNotifications()->count() : 0;
@endphp

<nav
    x-data="{
        moreOpen: false,
        openBell() {
            const bell = document.querySelector('details[data-nav-bell]');
            if (! bell) return;
            window.scrollTo({ top: 0, behavior: 'smooth' });
            bell.open = true;
        },
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', moreOpen)"
    @keydown.escape.window="moreOpen = false"
    class="border-b border-[var(--border)] bg-[var(--surface)]"
>
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 justify-between md:h-16">
            <div class="flex min-w-0">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ $homeUrl }}" class="flex items-center gap-2.5">
                        <x-application-logo class="block h-7 w-7 fill-current" />
                        <span class="font-display text-[15px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Ayyanet</span>
                        <span class="mt-0.5 hidden text-[11px] font-medium text-[var(--muted)] sm:block md:hidden lg:block">Support Desk</span>
                    </a>
                </div>

                <!-- Navigation Links (desktop) -->
                @auth
                    <div class="hidden h-full items-center gap-1 md:-my-px md:ms-6 md:flex lg:ms-10" data-nav="desktop">
                        @if ($isManager)
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('tickets.index')" :active="$ticketsActive">
                                {{ __('Tickets') }}
                            </x-nav-link>
                            <x-nav-link :href="route('customers.index')" :active="$customersActive">
                                {{ __('Customers') }}
                            </x-nav-link>
                            <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                                {{ __('Settings') }}
                            </x-nav-link>

                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button type="button" data-nav-kelola
                                        class="inline-flex items-center gap-1 border-b-2 px-3 py-2 text-[13.5px] transition-colors duration-150 focus:outline-none {{ $kelolaActive ? 'border-[var(--accent)] font-bold text-[var(--foreground)]' : 'border-transparent font-medium text-[var(--muted)] hover:text-[var(--foreground)]' }}">
                                        {{ __('Kelola') }}
                                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('reports.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('reports.*')])>
                                        {{ __('Laporan') }}
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('status-banners.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('status-banners.*')])>
                                        {{ __('Status Banner') }}
                                    </x-dropdown-link>
                                    @if ($isAdmin)
                                        <x-dropdown-link :href="route('users.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('users.*')])>
                                            {{ __('Users') }}
                                        </x-dropdown-link>
                                    @endif
                                </x-slot>
                            </x-dropdown>
                        @elseif ($isField)
                            <x-nav-link :href="route('my.tickets')" :active="request()->routeIs('my.tickets')">
                                {{ __('Tiket Saya') }}
                            </x-nav-link>
                        @endif
                    </div>
                @endauth
            </div>

            <!-- Right side -->
            <div class="flex shrink-0 items-center gap-1 md:ms-6 md:gap-2">
                @auth
                    @if ($canCreateTicket)
                        <a href="{{ route('tickets.create') }}" class="btn-primary hidden md:inline-flex" data-nav-new-ticket>
                            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                            </svg>
                            {{ __('Tiket Baru') }}
                        </a>
                    @endif

                    <x-notification-bell data-nav-bell />

                    <div class="hidden md:block">
                        @include('partials.theme-toggle')
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" aria-label="Menu akun" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-transparent py-2 ps-1 pe-0 text-sm font-medium text-[var(--muted)] transition duration-150 ease-in-out hover:text-[var(--foreground)] focus:outline-none md:px-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[11px] font-semibold text-[var(--accent-text)]">
                                    {{ strtoupper(substr($navUser->name, 0, 2)) }}
                                </div>
                                <div class="hidden max-w-[10rem] truncate lg:block">{{ $navUser->name }}</div>

                                <div class="ms-1 hidden md:block">
                                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="border-b border-[var(--border)] px-4 py-2.5">
                                <div class="truncate text-[13px] font-semibold text-[var(--foreground)]">{{ $navUser->name }}</div>
                                <div class="truncate text-[11.5px] text-[var(--muted)]">{{ $navUser->email }}</div>
                            </div>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Theme (mobile only; desktop has the icon in the bar) -->
                            <div class="flex items-center justify-between px-4 py-1.5 text-[13px] font-medium text-[var(--foreground)] md:hidden" @click.stop>
                                <span>{{ __('Tema') }}</span>
                                @include('partials.theme-toggle')
                            </div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    @include('partials.theme-toggle')
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-secondary !py-1.5 text-xs">Log in</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    @auth
        <!-- Mobile bottom tab bar -->
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[var(--border)] bg-[var(--surface)] pb-[env(safe-area-inset-bottom)] shadow-[0_-4px_16px_rgba(0,0,0,0.08)] md:hidden" data-nav="mobile-tabs">
            <div class="mx-auto flex h-16 max-w-lg items-stretch">
                @if ($isManager)
                    <x-bottom-tab :href="route('dashboard')" :active="request()->routeIs('dashboard')" label="Dashboard">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.75h6.5v6.5h-6.5zM13.75 4.75h6.5v6.5h-6.5zM3.75 14.75h6.5v4.5h-6.5zM13.75 14.75h6.5v4.5h-6.5z" />
                        </svg>
                    </x-bottom-tab>
                    <x-bottom-tab :href="route('tickets.index')" :active="$ticketsActive" label="Tickets">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A1.5 1.5 0 015.5 6h13A1.5 1.5 0 0120 7.5V10a2 2 0 000 4v2.5a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 16.5V14a2 2 0 000-4V7.5zM9 9.5h6M9 14.5h4" />
                        </svg>
                    </x-bottom-tab>

                    <div class="flex flex-1 items-center justify-center">
                        @if ($canCreateTicket)
                            <a href="{{ route('tickets.create') }}" aria-label="Tiket Baru" data-nav-new-ticket-mobile
                                class="-mt-5 flex h-12 w-12 items-center justify-center rounded-full bg-[var(--accent-cta)] text-white shadow-lg ring-4 ring-[var(--surface)] transition hover:bg-[var(--accent-cta-hover)] focus:outline-none focus-visible:ring-[var(--accent-40)]">
                                <svg width="20" height="20" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </a>
                        @endif
                    </div>

                    <x-bottom-tab :href="route('customers.index')" :active="$customersActive" label="Customers">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M9 10a3 3 0 100-6 3 3 0 000 6zM21 19v-1a4 4 0 00-3-3.87M16 4.13a3 3 0 010 5.74" />
                        </svg>
                    </x-bottom-tab>
                    <x-bottom-tab :active="$moreActive" label="Lainnya" @click="moreOpen = true" aria-haspopup="dialog" ::aria-expanded="moreOpen">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01" stroke-width="3" />
                        </svg>
                    </x-bottom-tab>
                @elseif ($isField)
                    <x-bottom-tab :href="route('my.tickets')" :active="request()->routeIs('my.tickets') || request()->routeIs('tickets.*')" label="Tiket Saya">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A1.5 1.5 0 015.5 6h13A1.5 1.5 0 0120 7.5V10a2 2 0 000 4v2.5a1.5 1.5 0 01-1.5 1.5h-13A1.5 1.5 0 014 16.5V14a2 2 0 000-4V7.5zM9 9.5h6M9 14.5h4" />
                        </svg>
                    </x-bottom-tab>
                    <x-bottom-tab label="Notifikasi" :badge="$navUnread" @click.stop="openBell()">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M8 1.5a3.5 3.5 0 00-3.5 3.5v2.2L3.2 8.7A1 1 0 004 10.2h8a1 1 0 00.8-1.5L11.5 7.2V5A3.5 3.5 0 008 1.5zM6 11.5a2 2 0 004 0" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </x-bottom-tab>
                    <x-bottom-tab :href="route('profile.edit')" :active="request()->routeIs('profile.*')" label="Profil">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4.5 20a7.5 7.5 0 0115 0" />
                        </svg>
                    </x-bottom-tab>
                @endif
            </div>
        </div>

        @if ($isManager)
            <!-- "Lainnya" sheet (mobile) -->
            <div x-show="moreOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Menu lainnya">
                <div x-show="moreOpen" x-transition.opacity class="absolute inset-0 bg-black/50" @click="moreOpen = false"></div>

                <div x-show="moreOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     class="absolute inset-x-0 bottom-0 rounded-t-2xl border-t border-[var(--border-strong)] bg-[var(--surface-2)] pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-2xl">
                    <div class="flex items-center justify-between px-5 pb-1 pt-3">
                        <span class="mx-auto h-1 w-10 rounded-full bg-[var(--border)]" aria-hidden="true"></span>
                    </div>
                    <div class="flex items-center justify-between px-5 pb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-[var(--muted)]">{{ __('Lainnya') }}</span>
                        <button type="button" @click="moreOpen = false" aria-label="Tutup" class="rounded-md p-1.5 text-[var(--muted)] hover:bg-[var(--hover-overlay)] hover:text-[var(--foreground)]">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="divide-y divide-[var(--border)]" data-nav="mobile-more">
                        <div class="py-1">
                            <x-dropdown-link :href="route('settings.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('settings.*')])>{{ __('Settings') }}</x-dropdown-link>
                        </div>
                        <div class="py-1">
                            <div class="px-4 pb-1 pt-2 text-[10.5px] font-semibold uppercase tracking-wide text-[var(--muted)]">{{ __('Kelola') }}</div>
                            <x-dropdown-link :href="route('reports.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('reports.*')])>{{ __('Laporan') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('status-banners.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('status-banners.*')])>{{ __('Status Banner') }}</x-dropdown-link>
                            @if ($isAdmin)
                                <x-dropdown-link :href="route('users.index')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('users.*')])>{{ __('Users') }}</x-dropdown-link>
                            @endif
                        </div>
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')" @class(['!text-[var(--accent)] font-semibold' => request()->routeIs('profile.*')])>{{ __('Profil') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2.5 text-start text-[13px] font-medium leading-5 text-[var(--foreground)] transition-all duration-200 ease-out hover:bg-[var(--hover-overlay)] hover:text-[var(--accent)] focus:bg-[var(--hover-overlay)] focus:outline-none">{{ __('Log Out') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</nav>
