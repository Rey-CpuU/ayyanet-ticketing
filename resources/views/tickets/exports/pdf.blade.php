<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tickets Export - {{ $generatedAt }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #1f2937;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 4px;
        }
        .meta {
            color: #6b7280;
            margin-bottom: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .status-open { background: #dbeafe; color: #1e40af; }
        .status-checking { background: #fef3c7; color: #92400e; }
        .status-solved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #f3f4f6; color: #374151; }
        .status-escalated { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Tickets Report</h1>
    <p class="meta">Generated at {{ $generatedAt }}</p>
    <table>
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Customer</th>
                <th>Title</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Category</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string)$ticket->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $ticket->customer->name ?? 'N/A' }}</td>
                    <td>{{ $ticket->title }}</td>
                    <td><span class="badge status-{{ Str::slug(strtolower($ticket->status)) }}">{{ $ticket->status }}</span></td>
                    <td>{{ $ticket->priority }}</td>
                    <td>{{ $ticket->category }}</td>
                    <td>{{ $ticket->created_at?->format('d M Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <script>window.print();</script>
</body>
</html>
