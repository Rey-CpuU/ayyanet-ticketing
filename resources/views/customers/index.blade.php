<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Customer</title>
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111827;
            --line: #243044;
            --text: #e5e7eb;
            --muted: #94a3b8;
            --primary: #8b5cf6;
            --primary-soft: rgba(139, 92, 246, 0.15);
            --green: #22c55e;
            --green-soft: rgba(34, 197, 94, 0.15);
            --amber: #f59e0b;
            --amber-soft: rgba(245, 158, 11, 0.15);
            --blue: #60a5fa;
            --blue-soft: rgba(96, 165, 250, 0.15);
            --red: #f87171;
            --red-soft: rgba(248, 113, 113, 0.12);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: linear-gradient(180deg, #020817 0%, #0f172a 100%);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
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

        .title {
            margin: 0;
            font-size: 2rem;
            letter-spacing: -0.03em;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 18px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,0.08);
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn.secondary {
            background: rgba(148, 163, 184, 0.08);
            color: var(--text);
        }

        .btn.danger {
            background: var(--red-soft);
            color: var(--red);
            border-color: rgba(248, 113, 113, 0.3);
        }

        .panel {
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
        }

        th {
            background: rgba(30, 41, 59, 0.9);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        tbody tr:hover {
            background: rgba(148, 163, 184, 0.03);
        }

        .customer-id {
            font-weight: 700;
            color: #c4b5fd;
        }

        .muted {
            color: var(--muted);
        }

        .link {
            color: #c4b5fd;
            text-decoration: none;
            font-weight: 700;
        }

        .empty {
            padding: 28px 18px;
            color: var(--muted);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 48px 24px;
            text-align: center;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.2);
            font-size: 1.6rem;
            color: #c4b5fd;
        }

        .empty-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
        }

        .empty-subtitle {
            color: var(--muted);
            font-size: 0.9rem;
            max-width: 320px;
        }

        .empty-action {
            margin-top: 8px;
        }

        /* Skeleton loading */
        .skeleton-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            gap: 12px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            align-items: center;
        }

        .skeleton-line {
            height: 14px;
            border-radius: 8px;
            background: linear-gradient(90deg, rgba(148, 163, 184, 0.08) 25%, rgba(148, 163, 184, 0.18) 50%, rgba(148, 163, 184, 0.08) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s ease-in-out infinite;
        }

        .skeleton-line.short { width: 60%; }
        .skeleton-line.tiny { width: 40%; height: 10px; }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Confirm delete modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 8, 23, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-box {
            background: #111827;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 28px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--red-soft);
            color: var(--red);
            font-size: 1.4rem;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .modal-text {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .modal-actions .btn {
            padding: 10px 16px;
            font-size: 0.85rem;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .filters input,
        .filters select {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #0f172a;
            color: var(--text);
            font-size: 14px;
        }

        .filters .filter-search {
            flex: 1 1 280px;
            min-width: 0;
        }

        .filters input:focus,
        .filters select:focus {
            outline: 2px solid rgba(139, 92, 246, 0.4);
            border-color: var(--primary);
        }

        .result-count {
            margin-bottom: 12px;
            font-size: 0.85rem;
            color: var(--muted);
        }

        tr.is-trashed td {
            opacity: 0.75;
        }

        .trashed-badge {
            display: inline-block;
            margin-left: 6px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: var(--red-soft);
            color: var(--red);
        }

        .row-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .row-actions form {
            margin: 0;
        }

        .link-btn {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #c4b5fd;
            font-weight: 700;
            font-size: inherit;
        }

        .link-btn.danger {
            color: var(--red);
        }

        .pagination {
            padding: 16px 18px;
            display: flex;
            justify-content: center;
        }

        .pagination ul {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pagination a,
        .pagination span {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(148, 163, 184, 0.08);
            color: var(--text);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .pagination .active span {
            background: var(--primary);
            color: white;
        }

        .pagination .disabled span {
            opacity: 0.45;
        }

        @media (max-width: 720px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            th, td {
                padding: 12px 10px;
            }

            .skeleton-row {
                grid-template-columns: 1fr 1fr;
            }

            .skeleton-line:nth-child(3),
            .skeleton-line:nth-child(4),
            .skeleton-line:nth-child(5) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h2 class="title">Daftar Customer</h2>
            <div class="actions">
                <x-notification-bell />
                <a href="{{ route('dashboard') }}" class="btn secondary">Dashboard</a>
                @can('create', App\Models\Customer::class)
                    <a href="{{ route('customers.create') }}" class="btn">+ New Customer</a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div style="padding: 16px 18px; margin-bottom: 16px; background: var(--green-soft); border: 1px solid var(--green); border-radius: 12px; color: var(--green); font-weight: 700;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div role="alert" style="padding: 16px 18px; margin-bottom: 16px; background: rgba(248, 113, 113, 0.12); border: 1px solid #f87171; border-radius: 12px; color: #f87171; font-weight: 700;">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="{{ route('customers.index') }}" class="filters" role="search" aria-label="Cari customer">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama, email, no HP, atau ID customer..." aria-label="Cari customer" class="filter-search">
            @if(auth()->user()->isAdmin())
                <select name="trashed" aria-label="Filter customer terhapus">
                    <option value="">Customer aktif</option>
                    <option value="with" @selected($trashed === 'with')>Termasuk terhapus</option>
                    <option value="only" @selected($trashed === 'only')>Hanya terhapus</option>
                </select>
            @endif
            <button type="submit" class="btn">Cari</button>
            @if($search !== '' || $trashed !== '')
                <a href="{{ route('customers.index') }}" class="btn secondary">Reset</a>
            @endif
        </form>

        <div class="result-count">{{ $customers->total() }} customer ditemukan</div>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>Paket</th>
                        <th>Tiket</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    @forelse($customers as $customer)
                        <tr class="{{ $customer->trashed() ? 'is-trashed' : '' }}">
                            <td><span class="customer-id">{{ $customer->customer_id }}</span></td>
                            <td>
                                <strong>{{ $customer->name }}</strong>
                                @if($customer->trashed())
                                    <span class="trashed-badge">Terhapus</span>
                                @endif
                                @if($customer->email)
                                    <div style="color: var(--muted); font-size: 0.8rem; margin-top: 2px;">{{ $customer->email }}</div>
                                @endif
                            </td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->package ?? '-' }}</td>
                            <td>{{ $customer->tickets_count }}</td>
                            <td>
                                @if($customer->trashed())
                                    <div class="row-actions">
                                        @can('restore', $customer)
                                            <form method="POST" action="{{ route('customers.restore', $customer->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="link-btn">Pulihkan</button>
                                            </form>
                                        @endcan
                                        @can('forceDelete', $customer)
                                            <form method="POST" action="{{ route('customers.force-delete', $customer->id) }}" id="force-delete-customer-{{ $customer->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="link-btn danger" onclick="openDeleteModal({{ Js::from($customer->name) }}, 'force-delete-customer-{{ $customer->id }}')">Hapus permanen</button>
                                            </form>
                                        @endcan
                                    </div>
                                @else
                                <a class="link" href="{{ route('customers.show', $customer->id) }}">View</a>
                                @can('update', $customer)
                                    <span style="color: var(--muted);">·</span>
                                    <a class="link" href="{{ route('customers.edit', $customer->id) }}">Edit</a>
                                @endcan
                                @can('delete', $customer)
                                    <span style="color: var(--muted);">·</span>
                                    <button
                                        type="button"
                                        class="link"
                                        style="background: none; border: none; cursor: pointer; color: var(--red); font-weight: 700; padding: 0;"
                                        onclick="openDeleteModal({{ Js::from($customer->name) }}, 'delete-customer-{{ $customer->id }}')"
                                    >Hapus</button>
                                    <form method="POST" action="{{ route('customers.destroy', $customer->id) }}" id="delete-customer-{{ $customer->id }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon">👤</div>
                                    @if($search !== '' || $trashed !== '')
                                        <div class="empty-title">Customer Tidak Ditemukan</div>
                                        <div class="empty-subtitle">Coba kata kunci lain atau reset pencarian.</div>
                                        <a href="{{ route('customers.index') }}" class="btn secondary empty-action">Reset pencarian</a>
                                    @else
                                        <div class="empty-title">Belum Ada Customer</div>
                                        <div class="empty-subtitle">Tambahkan customer baru untuk mulai membuat tiket.</div>
                                        @can('create', App\Models\Customer::class)
                                            <a href="{{ route('customers.create') }}" class="btn empty-action">+ Tambah Customer</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($customers->hasPages())
                <div class="pagination">
                    {{ $customers->links('pagination::bootstrap-3') }}
                </div>
            @endif

            <!-- Skeleton loading (shown while page loads) -->
            <div id="skeleton-loading" style="display: none;">
                @for($i = 0; $i < 5; $i++)
                    <div class="skeleton-row">
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line tiny"></div>
                        <div class="skeleton-line tiny"></div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="confirm-delete-modal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <div class="modal-title">Apakah Anda yakin?</div>
            <div class="modal-text">
                Anda yakin ingin menghapus <strong id="delete-item-name">customer ini</strong>? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-actions">
                <button class="btn secondary" onclick="closeDeleteModal()">Batal</button>
                <button class="btn danger" onclick="submitDelete()">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // Skeleton loading simulation (shows briefly on page load)
        window.addEventListener('DOMContentLoaded', function () {
            const skeleton = document.getElementById('skeleton-loading');
            const tableBody = document.getElementById('customer-table-body');

            // Only show skeleton if table is empty (no customers yet)
            if (tableBody && tableBody.children.length === 0) {
                skeleton.style.display = 'block';
                setTimeout(function () {
                    skeleton.style.display = 'none';
                }, 800);
            }
        });

        // Confirm delete modal
        let deleteFormId = null;

        function openDeleteModal(itemName, formId) {
            deleteFormId = formId;
            document.getElementById('delete-item-name').textContent = itemName;
            document.getElementById('confirm-delete-modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('confirm-delete-modal').style.display = 'none';
            document.body.style.overflow = '';
        }

        function submitDelete() {
            if (deleteFormId) {
                document.getElementById(deleteFormId).requestSubmit();
            }
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });

        // Close modal on overlay click
        document.getElementById('confirm-delete-modal')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>