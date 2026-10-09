<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Tickets' }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --bg: #0b1120;
            --panel: #111827;
            --panel-alt: #0f172a;
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

        .btn .spinner {
            display: none;
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn.loading .spinner {
            display: inline-block;
            margin-right: 8px;
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

        .ticket-id {
            font-weight: 700;
            color: #c4b5fd;
        }

        .customer-name {
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .status-open { background: var(--blue-soft); color: var(--blue); }
        .status-checking { background: var(--amber-soft); color: var(--amber); }
        .status-waiting-customer { background: var(--amber-soft); color: var(--amber); }
        .status-solved { background: var(--green-soft); color: var(--green); }
        .status-closed { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; }
        .status-escalated { background: rgba(248, 113, 113, 0.12); color: var(--red); }

        .priority-low { color: #7dd3fc; }
        .priority-medium { color: #fbbf24; }
        .priority-high { color: #f87171; }

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
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr;
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

        .spinner.dark {
            border-color: rgba(139, 92, 246, 0.2);
            border-top-color: #8b5cf6;
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

        .pagination {
            padding: 16px 18px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(148, 163, 184, 0.08);
            color: var(--text);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .pagination .active {
            background: var(--primary);
            color: white;
        }

        .pagination ul {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .pagination .active span {
            background: var(--primary);
            color: white;
        }

        .pagination .disabled span {
            opacity: 0.45;
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
            background: var(--panel-alt);
            color: var(--text);
            font-size: 14px;
        }

        .filters .filter-search {
            flex: 1 1 260px;
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
        }

        .flash {
            padding: 14px 18px;
            margin-bottom: 16px;
            border-radius: 12px;
            font-weight: 700;
        }

        .flash-success {
            background: var(--green-soft);
            border: 1px solid var(--green);
            color: var(--green);
        }

        tr.is-trashed td {
            opacity: 0.75;
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
    @include('partials.status-banner')
    <div class="wrap">
        <div class="topbar">
            <h2 class="title">{{ $pageTitle ?? 'Tickets' }}</h2>
            <div class="actions">
                <x-notification-bell />
                <a href="{{ route('dashboard') }}" class="btn secondary">Dashboard</a>
                <a href="{{ route('tickets.export.csv') }}" class="btn secondary" id="export-csv-btn">
                    <span class="spinner dark" style="display:none;"></span>
                    Export CSV
                </a>
                <a href="{{ route('tickets.export.pdf') }}" class="btn secondary" target="_blank" id="export-pdf-btn">
                    <span class="spinner dark" style="display:none;"></span>
                    Export PDF
                </a>
                @can('create', App\Models\Ticket::class)
                    <a href="{{ route('tickets.create') }}" class="btn">+ New Ticket</a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="flash flash-success" role="status">{{ session('success') }}</div>
        @endif

        @php
            $hasFilters = collect($filters)->except('sort')->filter(fn ($value) => $value !== '')->isNotEmpty();
        @endphp

        <form method="GET" action="{{ url()->current() }}" class="filters" role="search" aria-label="Filter tiket">
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nomor tiket, judul, atau customer..." aria-label="Cari tiket" class="filter-search">
            <select name="status" aria-label="Filter status">
                <option value="">Semua status</option>
                @foreach(App\Models\Ticket::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="priority" aria-label="Filter prioritas">
                <option value="">Semua prioritas</option>
                @foreach(App\Models\Ticket::PRIORITIES as $priority)
                    <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ $priority }}</option>
                @endforeach
            </select>
            <select name="category" aria-label="Filter kategori">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <select name="assigned_to" aria-label="Filter penanggung jawab">
                <option value="">Semua penanggung jawab</option>
                <option value="unassigned" @selected($filters['assigned_to'] === 'unassigned')>Belum ditugaskan</option>
                @foreach($assignees as $assignee)
                    <option value="{{ $assignee->id }}" @selected($filters['assigned_to'] === (string) $assignee->id)>{{ $assignee->name }}</option>
                @endforeach
            </select>
            @if(auth()->user()->isAdmin())
                <select name="trashed" aria-label="Filter tiket terhapus">
                    <option value="">Tiket aktif</option>
                    <option value="with" @selected($filters['trashed'] === 'with')>Termasuk terhapus</option>
                    <option value="only" @selected($filters['trashed'] === 'only')>Hanya terhapus</option>
                </select>
            @endif
            <select name="sort" aria-label="Urutkan">
                @foreach($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>Urut: {{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Terapkan</button>
            @if($hasFilters || $filters['sort'] !== 'newest')
                <a href="{{ url()->current() }}" class="btn secondary">Reset</a>
            @endif
        </form>

        <div class="result-count muted">{{ $tickets->total() }} tiket ditemukan</div>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Penanggung Jawab</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ticket-table-body">
                    @forelse($tickets as $ticket)
                        <tr class="{{ $ticket->trashed() ? 'is-trashed' : '' }}">
                            <td>
                                <div class="ticket-id">{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string)$ticket->id, 4, '0', STR_PAD_LEFT) }}</div>
                                <div class="muted">{{ $ticket->title }}</div>
                            </td>
                            <td class="customer-name">{{ $ticket->customer->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge status-{{ Str::slug(strtolower($ticket->status)) }}">
                                    {{ $ticket->status }}
                                </span>
                                @if($ticket->trashed())
                                    <span class="badge status-escalated">Terhapus</span>
                                @endif
                            </td>
                            <td class="priority-{{ strtolower($ticket->priority ?? 'medium') }}">{{ $ticket->priority ?? 'Medium' }}</td>
                            <td class="muted">{{ $ticket->assignee->name ?? '-' }}</td>
                            <td class="muted">{{ $ticket->created_at?->format('d M Y') ?? '-' }}</td>
                            <td>
                                @if($ticket->trashed())
                                    <div class="row-actions">
                                        @can('restore', $ticket)
                                            <form method="POST" action="{{ route('tickets.restore', $ticket->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="link-btn">Pulihkan</button>
                                            </form>
                                        @endcan
                                        @can('forceDelete', $ticket)
                                            <form method="POST" action="{{ route('tickets.force-delete', $ticket->id) }}" id="force-delete-ticket-{{ $ticket->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="link-btn danger" onclick="openDeleteModal({{ Js::from($ticket->ticket_number) }}, 'force-delete-ticket-{{ $ticket->id }}')">Hapus permanen</button>
                                            </form>
                                        @endcan
                                    </div>
                                @else
                                    <a class="link" href="{{ route('tickets.show', $ticket->id) }}">View</a>
                                    @if(!empty($showMyTicketsOnly) && $showMyTicketsOnly)
                                        <span class="muted" style="font-size: 0.8rem;">(Milik saya)</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon">🎫</div>
                                    @if($hasFilters)
                                        <div class="empty-title">Tidak Ada Tiket yang Cocok</div>
                                        <div class="empty-subtitle">Coba ubah kata kunci atau filter pencarian.</div>
                                        <a href="{{ url()->current() }}" class="btn secondary empty-action">Reset filter</a>
                                    @else
                                        <div class="empty-title">Belum Ada Tiket</div>
                                        <div class="empty-subtitle">
                                            @if(!empty($showMyTicketsOnly) && $showMyTicketsOnly)
                                                Anda belum memiliki tiket. Buat tiket baru untuk mulai menerima request pelanggan.
                                            @else
                                                Belum ada tiket untuk ditampilkan. Buat tiket baru untuk mulai menerima request pelanggan.
                                            @endif
                                        </div>
                                        @can('create', App\Models\Ticket::class)
                                            <a href="{{ route('tickets.create') }}" class="btn empty-action">+ Buat Tiket Baru</a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Skeleton loading (shown while page loads) -->
            <div id="skeleton-loading" style="display: none;">
                @for($i = 0; $i < 5; $i++)
                    <div class="skeleton-row">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line tiny"></div>
                        <div class="skeleton-line short"></div>
                        <div class="skeleton-line tiny"></div>
                    </div>
                @endfor
            </div>

            @if($tickets->hasPages())
                <div class="pagination">
                    {{ $tickets->links('pagination::bootstrap-3') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div id="confirm-delete-modal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <div class="modal-icon">⚠️</div>
            <div class="modal-title">Apakah Anda yakin?</div>
            <div class="modal-text">
                Anda yakin ingin menghapus <strong id="delete-item-name">item ini</strong>? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-actions">
                <button class="btn secondary" onclick="closeDeleteModal()">Batal</button>
                <button class="btn danger" onclick="submitDelete()">Hapus</button>
            </div>
        </div>
    </div>

    <script>
        // Export button loading spinners
        document.getElementById('export-csv-btn')?.addEventListener('click', function (e) {
            const spinner = this.querySelector('.spinner');
            if (spinner) {
                spinner.style.display = 'inline-block';
                this.classList.add('loading');
            }
        });

        document.getElementById('export-pdf-btn')?.addEventListener('click', function (e) {
            const spinner = this.querySelector('.spinner');
            if (spinner) {
                spinner.style.display = 'inline-block';
                this.classList.add('loading');
            }
        });

        // Skeleton loading simulation (shows briefly on page load)
        window.addEventListener('DOMContentLoaded', function () {
            const skeleton = document.getElementById('skeleton-loading');
            const tableBody = document.getElementById('ticket-table-body');

            // Only show skeleton if table is empty (no tickets yet)
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