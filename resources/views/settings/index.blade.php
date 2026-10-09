<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111827;
            --panel-strong: #0f172a;
            --line: rgba(148, 163, 184, 0.18);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --purple: #8b5cf6;
            --green: #22c55e;
            --cyan: #2dd4bf;
            --amber: #f59e0b;
            --red: #f87171;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(180deg, #020817 0%, #0f172a 100%);
            color: var(--text);
        }

        .wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        h1 {
            margin: 0;
            font-size: 2.2rem;
        }

        .link {
            color: #c4b5fd;
            text-decoration: none;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: rgba(15, 23, 42, 0.92);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 18px 30px rgba(2, 6, 23, 0.25);
        }

        .card h2 {
            margin: 0 0 18px;
            font-size: 1.15rem;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .row:last-child { border-bottom: none; }

        .member {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge.online { background: rgba(34, 197, 94, 0.12); color: #86efac; }
        .status-badge.offline { background: rgba(148, 163, 184, 0.12); color: #cbd5e1; }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 88px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.12);
            color: var(--text);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .toggle {
            position: relative;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.22);
            border: 1px solid rgba(148, 163, 184, 0.24);
        }

        .toggle::after {
            content: "";
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #e2e8f0;
            transition: 0.2s ease;
        }

        .toggle.on {
            background: rgba(34, 197, 94, 0.26);
            border-color: rgba(34, 197, 94, 0.5);
        }

        .toggle.on::after {
            left: 24px;
            background: #86efac;
        }

        .integrations {
            display: grid;
            grid-template-columns: repeat(2, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .integration-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: rgba(17, 24, 39, 0.8);
        }

        .integration-card strong { display: block; margin-bottom: 10px; }

        .integration-card .meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: center;
            color: var(--muted);
            font-size: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid rgba(139, 92, 246, 0.4);
            background: rgba(139, 92, 246, 0.12);
            color: #e9ddff;
            font-weight: 700;
            font-size: 12px;
            text-decoration: none;
        }

        .btn.secondary {
            background: rgba(148, 163, 184, 0.08);
            border-color: var(--line);
            color: var(--text);
        }

        .hours {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .input-box {
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px;
        }

        .input-box small { color: var(--muted); }
        .input-box input {
            border: none;
            background: transparent;
            color: var(--text);
            font-size: 14px;
        }

        .channel-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 12px;
        }

        .channel-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        }

        .channel-item:last-child { border-bottom: none; }

        .channel-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .channel-copy small { color: var(--muted); }

        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1>Settings</h1>
            <a href="{{ route('dashboard') }}" class="link">← Kembali ke Dashboard</a>
        </div>

        @if(session('success'))
            <div role="status" style="padding: 12px 16px; margin-bottom: 16px; border-radius: 12px; border: 1px solid #22c55e; background: rgba(34, 197, 94, 0.12); color: #86efac; font-weight: 700;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" style="padding: 12px 16px; margin-bottom: 16px; border-radius: 12px; border: 1px solid #f87171; background: rgba(248, 113, 113, 0.12); color: #f87171; font-weight: 700;">{{ $errors->first() }}</div>
        @endif

        <div class="grid">
            <section class="card">
                <h2>Tim &amp; Agen</h2>
                @forelse($users as $user)
                    <div class="row">
                        <div class="member">
                            <div class="avatar">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                            <div>
                                <div>{{ $user->name }}</div>
                                <small style="color: var(--muted);">{{ $roles[$user->role] ?? 'Customer Service' }}</small>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if(auth()->user()->isAdmin() && ! $user->is(auth()->user()))
                                <form method="POST" action="{{ route('settings.update-role', $user) }}" style="display: flex; gap: 6px; align-items: center;">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" style="background: var(--panel-strong); color: var(--text); border: 1px solid var(--line); border-radius: 8px; padding: 6px 8px; font-size: 0.8rem;">
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" {{ $user->role === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn" style="padding: 6px 10px; font-size: 0.7rem;">Simpan</button>
                                </form>
                            @endif
                            <span class="status-badge {{ $user->email_verified_at ? 'online' : 'offline' }}">
                                {{ $user->email_verified_at ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="row" style="color: var(--muted);">Belum ada pengguna.</div>
                @endforelse
                @if(auth()->user()->isAdmin())
                    <div style="margin-top: 16px;">
                        <a href="{{ route('users.create') }}" class="btn">Undang Anggota</a>
                    </div>
                @endif
            </section>

            <section class="card">
                <h2>Hak Akses (Roles)</h2>
                <div class="row"><span>Admin</span> <span class="pill">Admin</span></div>
                <div class="row"><span>Customer Service</span> <span class="pill">CS</span></div>
                <div class="row"><span>Teknisi Lapangan</span> <span class="pill">Lapangan</span></div>
                <div style="margin-top: 12px; font-size: 0.8rem; color: var(--muted);">
                    <p><strong>Admin:</strong> Full access including settings and reports.</p>
                    <p><strong>Customer Service:</strong> Create and manage tickets.</p>
                    <p><strong>Teknisi Lapangan:</strong> View and update assigned tickets.</p>
                </div>
            </section>

            <section class="card">
                <h2>Konfigurasi SLA</h2>
                <div class="row"><span>High Priority</span> <span class="pill">{{ $slaSettings['high'] }}</span></div>
                <div class="row"><span>Medium Priority</span> <span class="pill">{{ $slaSettings['medium'] }}</span></div>
                <div class="row"><span>Low Priority</span> <span class="pill">{{ $slaSettings['low'] }}</span></div>
            </section>

            <section class="card">
                <h2>Data Customer</h2>
                <div style="max-height: 240px; overflow-y: auto; margin-bottom: 16px;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="text-align: left; color: var(--muted); border-bottom: 1px solid var(--line);">
                                <th style="padding: 8px 4px;">ID</th>
                                <th style="padding: 8px 4px;">Nama</th>
                                <th style="padding: 8px 4px;">No HP</th>
                                <th style="padding: 8px 4px;">Paket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $customers = \App\Models\Customer::latest()->limit(20)->get(); @endphp
                            @forelse($customers as $customer)
                                <tr style="border-bottom: 1px solid rgba(148,163,184,0.08);">
                                    <td style="padding: 8px 4px; color: #c4b5fd; font-weight: 700;">{{ $customer->customer_id }}</td>
                                    <td style="padding: 8px 4px;">{{ $customer->name }}</td>
                                    <td style="padding: 8px 4px; color: var(--muted);">{{ $customer->phone }}</td>
                                    <td style="padding: 8px 4px;">{{ $customer->package ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="padding: 12px 4px; color: var(--muted);">Belum ada customer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('customers.store') }}" method="POST" style="display: grid; gap: 10px;">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 6px;">Nama Customer <span style="color: var(--red);">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-strong); color: var(--text); font-size: 0.9rem;">
                            @error('name') <div style="color: var(--red); font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 6px;">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-strong); color: var(--text); font-size: 0.9rem;">
                            @error('email') <div style="color: var(--red); font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 6px;">No HP <span style="color: var(--red);">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-strong); color: var(--text); font-size: 0.9rem;">
                            @error('phone') <div style="color: var(--red); font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 6px;">Paket <span style="color: var(--red);">*</span></label>
                            <input type="text" name="package" value="{{ old('package') }}" required style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-strong); color: var(--text); font-size: 0.9rem;">
                            @error('package') <div style="color: var(--red); font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--muted); display: block; margin-bottom: 6px;">Alamat <span style="color: var(--red);">*</span></label>
                        <textarea name="address" required style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--line); background: var(--panel-strong); color: var(--text); font-size: 0.9rem; min-height: 70px;">{{ old('address') }}</textarea>
                        @error('address') <div style="color: var(--red); font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn" style="justify-content: center;">+ Tambah Customer</button>
                </form>
            </section>

            <section class="card">
                <h2>Jam Operasional</h2>
                <div class="hours">
                    <div class="input-box">
                        <small>Senin - Jumat</small>
                        <input type="text" value="08:00 - 18:00" />
                    </div>
                    <div class="input-box">
                        <small>Sabtu</small>
                        <input type="text" value="09:00 - 15:00" />
                    </div>
                    <div class="input-box">
                        <small>Minggu</small>
                        <input type="text" value="Tutup" />
                    </div>
                    <div class="input-box">
                        <small>Mode Luar Jam</small>
                        <input type="text" value="Auto-Route" />
                    </div>
                </div>
            </section>

            <section class="card">
                <h2>Saluran (Omnichannel)</h2>
                <div class="channel-list">
                    @foreach($channels as $key => $channel)
                        <div class="channel-item">
                            <div class="channel-copy">
                                <strong>{{ $channel['label'] }}</strong>
                                <small>{{ $channel['enabled'] ? 'Aktif' : 'Nonaktif' }}</small>
                            </div>
                            <span class="toggle {{ $channel['enabled'] ? 'on' : '' }}" aria-label="{{ $channel['enabled'] ? 'active' : 'inactive' }}"></span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</body>
</html>
