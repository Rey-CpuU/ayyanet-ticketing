<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0; }
        .wrap { max-width: 1100px; margin: 40px auto; padding: 24px; }
        .card { background: #111827; border: 1px solid #2b3448; border-radius: 16px; padding: 24px; margin-top: 20px; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 16px; }
        .metric { background: #1f2937; border: 1px solid #374151; border-radius: 12px; padding: 18px; }
        .small { color: #94a3b8; font-size: 12px; }
        .big { font-size: 28px; font-weight: 700; margin-top: 10px; }
        a { color: #c4b5fd; text-decoration: none; font-weight: 700; }

        .filter-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .filter-form label {
            display: block;
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .filter-form input {
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #374151;
            background: #0f172a;
            color: #e2e8f0;
            font-size: 14px;
        }

        .filter-form button {
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid rgba(139, 92, 246, 0.4);
            background: rgba(139, 92, 246, 0.2);
            color: #e9ddff;
            font-weight: 700;
            cursor: pointer;
        }

        .bar-grid {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            height: 160px;
            margin-top: 16px;
        }

        .bar {
            flex: 1;
            background: linear-gradient(180deg, #8b5cf6, #6d58d1);
            border-radius: 8px 8px 0 0;
            min-height: 8px;
            position: relative;
        }

        .bar-label {
            text-align: center;
            margin-top: 8px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h2>Reports</h2>
        <a href="{{ route('dashboard') }}">← Kembali ke Dashboard</a>

        <div class="card">
            <form method="GET" action="{{ route('reports.index') }}" class="filter-form">
                <div>
                    <label for="from">Dari Tanggal</label>
                    <input type="date" id="from" name="from" value="{{ $from }}">
                </div>
                <div>
                    <label for="to">Sampai Tanggal</label>
                    <input type="date" id="to" name="to" value="{{ $to }}">
                </div>
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="card">
            <div class="grid">
                <div class="metric">
                    <div class="small">Total Tickets</div>
                    <div class="big">{{ $metrics['total'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">Open</div>
                    <div class="big">{{ $metrics['open'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">In Progress</div>
                    <div class="big">{{ $metrics['in_progress'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">Solved</div>
                    <div class="big">{{ $metrics['solved'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">Closed</div>
                    <div class="big">{{ $metrics['closed'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">Escalated</div>
                    <div class="big">{{ $metrics['escalated'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">High Priority</div>
                    <div class="big">{{ $metrics['high_priority'] }}</div>
                </div>
                <div class="metric">
                    <div class="small">Avg Resolution (h)</div>
                    <div class="big">{{ number_format($metrics['avg_resolution'], 1) }}</div>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">Tickets by Channel</h3>
            @php
                $maxChannel = $byChannel->max() ?: 1;
            @endphp
            <div class="bar-grid">
                @foreach($byChannel as $channel => $count)
                    <div>
                        <div class="bar" style="height: {{ ($count / $maxChannel) * 100 }}%;"></div>
                        <div class="bar-label">{{ ucfirst($channel) }}<br><strong>{{ $count }}</strong></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">Tickets by Priority</h3>
            @php
                $maxPriority = $byPriority->max() ?: 1;
            @endphp
            <div class="bar-grid">
                @foreach($byPriority as $priority => $count)
                    <div>
                        <div class="bar" style="height: {{ ($count / $maxPriority) * 100 }}%;"></div>
                        <div class="bar-label">{{ ucfirst($priority) }}<br><strong>{{ $count }}</strong></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">Tickets by Status</h3>
            @php
                $maxStatus = $byStatus->max() ?: 1;
            @endphp
            <div class="bar-grid">
                @foreach($byStatus as $status => $count)
                    <div>
                        <div class="bar" style="height: {{ ($count / $maxStatus) * 100 }}%;"></div>
                        <div class="bar-label">{{ ucfirst($status) }}<br><strong>{{ $count }}</strong></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
