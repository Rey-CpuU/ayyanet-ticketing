<div x-data="inactivityManager()" x-init="init()" class="relative z-50">
    <div x-show="showWarning" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        
        <div class="card max-w-sm w-full p-6 text-center border border-[var(--border-strong)] shadow-2xl bg-[var(--surface)]">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[var(--amber-text-10)] text-[var(--amber-text)] mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h3 class="text-base font-bold text-[var(--foreground)]">Peringatan Sesi Tidak Aktif</h3>
            <p class="mt-2 text-xs text-[var(--muted)] leading-relaxed">
                Anda tidak melakukan aktivitas selama beberapa menit. Untuk alasan keamanan, sesi Anda akan otomatis keluar dalam:
            </p>
            
            <div class="my-4 text-2xl font-mono font-bold text-[var(--amber-text)]" x-text="countdown + ' detik'"></div>

            <div class="flex gap-2.5 mt-2">
                <button type="button" @click="logoutNow()" class="btn-secondary flex-1 justify-center text-xs">
                    Keluar
                </button>
                <button type="button" @click="stayLoggedIn()" class="btn-primary flex-1 justify-center text-xs">
                    Tetap Masuk
                </button>
            </div>
        </div>
    </div>

    <form id="auto-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>

<script>
    function inactivityManager() {
        return {
            idleTime: 0,
            maxIdle: 10 * 60, // 10 menit total
            warningTime: 60,  // Peringatan 60 detik sebelum auto-logout
            showWarning: false,
            countdown: 60,
            timer: null,

            init() {
                const events = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'];
                events.forEach(event => {
                    window.addEventListener(event, () => this.resetActivity(), { passive: true });
                });

                this.timer = setInterval(() => this.checkIdle(), 1000);
            },

            lastActivityReset: 0,

            resetActivity() {
                const now = Date.now();
                if (!this.showWarning && (now - this.lastActivityReset > 2000)) {
                    this.idleTime = 0;
                    this.lastActivityReset = now;
                }
            },

            checkIdle() {
                this.idleTime++;
                const remaining = this.maxIdle - this.idleTime;

                if (remaining <= this.warningTime && remaining > 0) {
                    this.showWarning = true;
                    this.countdown = remaining;
                } else if (remaining <= 0) {
                    this.logoutNow();
                }
            },

            stayLoggedIn() {
                this.idleTime = 0;
                this.showWarning = false;
                this.countdown = this.warningTime;
                // Ping server to keep session alive
                fetch('/up', { method: 'GET' }).catch(() => {});
            },

            logoutNow() {
                clearInterval(this.timer);
                document.getElementById('auto-logout-form').submit();
            }
        };
    }
</script>
