<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[var(--foreground)]">Laporan</h2>
                <p class="mt-0.5 text-[12.5px] text-[var(--muted)]">Ringkasan tiket per periode</p>
            </div>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-2">
                <div>
                    <label for="from" class="label">Dari Tanggal</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" class="input py-1.5">
                </div>
                <div>
                    <label for="to" class="label">Sampai Tanggal</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" class="input py-1.5">
                </div>
                <button type="submit" class="btn-primary">Filter</button>
                @if ($from || $to)
                    <a href="{{ route('reports.index') }}" class="btn-secondary">Reset</a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        @php
            $cards = [
                ['label' => 'Total Tickets', 'value' => $metrics['total'], 'class' => 'text-[var(--foreground)]', 'dot' => 'bg-[var(--foreground)]'],
                ['label' => 'Open', 'value' => $metrics['open'], 'class' => 'text-[var(--red-bright)]', 'dot' => 'bg-[var(--red-bright)]'],
                ['label' => 'In Progress', 'value' => $metrics['in_progress'], 'class' => 'text-[var(--violet-text)]', 'dot' => 'bg-[var(--violet-text)]'],
                ['label' => 'Escalated', 'value' => $metrics['escalated'], 'class' => 'text-[var(--orange-text)]', 'dot' => 'bg-[var(--orange-text)]'],
                ['label' => 'Solved', 'value' => $metrics['solved'], 'class' => 'text-[var(--green-text)]', 'dot' => 'bg-[var(--green-text)]'],
                ['label' => 'Closed', 'value' => $metrics['closed'], 'class' => 'text-[var(--slate-text)]', 'dot' => 'bg-[var(--slate-text)]'],
                ['label' => 'High Priority', 'value' => $metrics['high_priority'], 'class' => 'text-[var(--amber-text)]', 'dot' => 'bg-[var(--amber-text)]'],
                ['label' => 'Avg Resolution (h)', 'value' => number_format($metrics['avg_resolution'], 1), 'class' => 'text-[var(--accent)]', 'dot' => 'bg-[var(--accent)]'],
            ];
            $breakdowns = [
                'Tickets by Channel' => $byChannel,
                'Tickets by Priority' => $byPriority,
                'Tickets by Status' => $byStatus,
            ];
        @endphp

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($cards as $card)
                <div class="card rounded-lg p-4">
                    <div class="mb-2 flex items-center gap-1.5">
                        <span class="h-1.5 w-1.5 rounded-full {{ $card['dot'] }}"></span>
                        <span class="truncate text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[var(--muted)]">{{ $card['label'] }}</span>
                    </div>
                    <div class="fx-glow-text font-display text-[24px] font-bold leading-none {{ $card['class'] }}">{{ $card['value'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            @foreach ($breakdowns as $title => $rows)
                @php $max = $rows->max() ?: 1; @endphp
                <div class="card overflow-hidden">
                    <div class="border-b border-[var(--border)] px-5 py-4">
                        <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">{{ $title }}</h3>
                    </div>
                    <div class="space-y-3 px-5 py-4">
                        @forelse ($rows as $label => $count)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-[12px]">
                                    <span class="text-[var(--foreground)]">{{ $label !== '' ? ucfirst($label) : '—' }}</span>
                                    <span class="font-mono font-semibold text-[var(--muted)]">{{ $count }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-[var(--surface-3)]">
                                    <div class="h-full rounded-full bg-[var(--accent)]" style="width: {{ round($count / $max * 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-[12.5px] text-[var(--muted)]">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
