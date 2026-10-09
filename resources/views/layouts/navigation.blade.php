<nav x-data="{ open: false }" class="border-b border-[var(--border)] bg-[var(--surface)]">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <x-application-logo class="block h-7 w-7 fill-current" />
                        <span class="font-display text-[15px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Ayyanet</span>
                        <span class="mt-0.5 hidden text-[11px] font-medium text-[var(--muted)] sm:block">Support Desk</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:-my-px sm:ms-10 sm:flex items-center gap-1 h-full">
                    @auth
                        @if (Auth::user()->isStaff())
                            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*') && !request()->routeIs('tickets.create')">
                                {{ __('Tickets') }}
                            </x-nav-link>
                            <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*') && !request()->routeIs('customers.create')">
                                {{ __('Customers') }}
                            </x-nav-link>
                        @endif
                        @if (Auth::user()->hasRole('admin', 'cs'))
                            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                                {{ __('Laporan') }}
                            </x-nav-link>
                            <x-nav-link :href="route('status-banners.index')" :active="request()->routeIs('status-banners.*')">
                                {{ __('Status') }}
                            </x-nav-link>
                        @endif
                        @if (Auth::user()->isAdmin())
                            <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                                {{ __('Users') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown / Auth -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2">
                @auth
                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button type="button" class="relative inline-flex items-center justify-center p-2 rounded-md text-[var(--muted)] hover:text-[var(--foreground)] hover:bg-[var(--surface-2)] focus:outline-none transition">
                                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                    <path d="M8 1.5a3.5 3.5 0 00-3.5 3.5v2.2L3.2 8.7A1 1 0 004 10.2h8a1 1 0 00.8-1.5L11.5 7.2V5A3.5 3.5 0 008 1.5zM6 11.5a2 2 0 004 0" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                @if (Auth::user()->unreadNotifications->count() > 0)
                                    <span class="absolute top-1 right-1 flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-[var(--border)] text-xs font-semibold text-[var(--foreground)] flex items-center justify-between">
                                <span>Notifikasi</span>
                                <span class="font-mono text-[10px] text-[var(--muted)]">{{ Auth::user()->unreadNotifications->count() }} unread</span>
                            </div>
                            <div class="max-h-60 overflow-y-auto divide-y divide-[var(--border)]">
                                @forelse (Auth::user()->unreadNotifications->take(5) as $notification)
                                    <a href="{{ route('tickets.show', $notification->data['ticket_id'] ?? '#') }}" class="block px-4 py-2.5 text-xs hover:bg-[var(--hover-overlay)] transition">
                                        <div class="font-semibold text-[var(--foreground)]">{{ $notification->data['ticket_number'] ?? '' }}</div>
                                        <div class="text-[11px] text-[var(--muted)] truncate">{{ $notification->data['message'] ?? '' }}</div>
                                    </a>
                                @empty
                                    <div class="px-4 py-3 text-center text-xs text-[var(--muted)]">Tidak ada notifikasi baru</div>
                                @endforelse
                            </div>
                        </x-slot>
                    </x-dropdown>
                @endauth

                @include('partials.theme-toggle')
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm font-medium rounded-md text-[var(--muted)] bg-transparent hover:text-[var(--foreground)] focus:outline-none transition ease-in-out duration-150">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--accent-soft)] text-[11px] font-semibold text-[var(--accent-text)]">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </div>
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

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
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-secondary !py-1.5 text-xs">Log in</a>
                    @endif
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                @include('partials.theme-toggle')
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[var(--muted)] hover:text-[var(--foreground)] hover:bg-[var(--surface-2)] focus:outline-none focus:bg-[var(--surface-2)] focus:text-[var(--foreground)] transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-[var(--border)]">
        <div class="pt-2 pb-3 space-y-1">
            @auth
                @if (Auth::user()->isStaff())
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*') && !request()->routeIs('tickets.create')">
                        {{ __('Tickets') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*') && !request()->routeIs('customers.create')">
                        {{ __('Customers') }}
                    </x-responsive-nav-link>
                @endif
                @if (Auth::user()->hasRole('admin', 'cs'))
                    <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('Laporan') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('status-banners.index')" :active="request()->routeIs('status-banners.*')">
                        {{ __('Status') }}
                    </x-responsive-nav-link>
                @endif
                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-[var(--border)]">
                <div class="px-4">
                    <div class="font-medium text-base text-[var(--foreground)]">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-[var(--muted)]">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
