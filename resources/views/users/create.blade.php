<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Invite User</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Kirim link undangan via email agar user bisa mendaftar sendiri</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn-secondary">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <form action="{{ route('invitations.store') }}" method="POST" class="card space-y-5 p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="email" class="label">Email <span class="text-[var(--red-text)]">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="e.g. sari@ayyanet.id" class="input">
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <div>
                    <label for="role" class="label">Role</label>
                    <select name="role" id="role" class="input">
                        @foreach (['cs' => 'CS — Customer Support', 'lapangan' => 'Staff — Lapangan / Teknisi', 'admin' => 'Admin'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', 'cs') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
                </div>

                <div class="flex items-end pb-1.5 sm:col-span-2">
                    <p class="text-[12px] leading-relaxed text-[var(--muted)]">
                        Sistem tidak akan langsung membuat akun. Sebuah link pendaftaran akan dikirim ke email di atas, dan user harus mengisikan nama serta password mereka sendiri. Link berlaku selama 5 jam.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-[var(--border)] pt-5">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Kirim Undangan</button>
            </div>
        </form>
    </div>
</x-app-layout>
