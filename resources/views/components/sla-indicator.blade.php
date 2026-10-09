@props(['ticket', 'compact' => false])

{{-- SLA state chip with a live countdown while the clock runs (see Ticket::slaSummary). --}}
@php $sla = $ticket->slaSummary(); @endphp
@if ($sla)
    @php
        $tone = match ($sla['state']) {
            'met' => 'border-[var(--green-text-30)] bg-[var(--green-text-06)] text-[var(--green-text)]',
            'breached' => 'border-[var(--red-text-40)] bg-[var(--red-solid-07)] text-[var(--red-bright)]',
            'paused' => 'border-[var(--border-strong)] bg-[var(--surface-3)] text-[var(--muted)]',
            default => 'border-[var(--amber-text-40)] bg-[var(--amber-text-07)] text-[var(--amber-text)]',
        };
        $remaining = $sla['remaining'] !== null ? max($sla['remaining'], 0) : null;
    @endphp

    @if ($compact)
        <span {{ $attributes->class(['inline-flex items-center gap-1 rounded-full border px-1.5 py-[1px] font-mono text-[10px] font-semibold', $tone]) }}
              title="SLA {{ $sla['label'] }} · deadline {{ $sla['deadline']->format('d M Y, H:i') }}">
            <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6.3" stroke="currentColor" stroke-width="1.4"/><path d="M8 4.5V8l2.3 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
            @if ($sla['running'])
                <span data-sla-deadline="{{ $sla['deadline']->toIso8601String() }}" data-sla-short>{{ $sla['label'] }}</span>
            @else
                {{ $sla['label'] }}
            @endif
        </span>
    @else
        <div {{ $attributes->class(['rounded-md border px-3.5 py-3', $tone]) }} id="sla-box">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10.5px] font-semibold uppercase tracking-[0.08em]">SLA Deadline</span>
                <span class="rounded-full border border-current px-2 py-[1px] text-[10.5px] font-semibold">{{ $sla['label'] }}</span>
            </div>
            <div class="mt-1.5 font-mono text-[13px] font-semibold text-[var(--foreground)]">{{ $sla['deadline']->format('d M Y, H:i') }}</div>
            @if ($sla['running'])
                <div class="mt-1 font-mono text-[11.5px]" id="sla-countdown" data-sla-deadline="{{ $sla['deadline']->toIso8601String() }}" data-deadline="{{ $sla['deadline']->toIso8601String() }}" aria-live="polite"></div>
            @elseif ($remaining !== null)
                <div class="mt-1 font-mono text-[11.5px]">Menunggu customer · sisa {{ intdiv($remaining, 3600) }}j {{ intdiv($remaining % 3600, 60) }}m</div>
            @endif
        </div>
    @endif

    @once
        <script>
            // Live SLA countdowns: every [data-sla-deadline] element shows the time left (or overdue).
            (function () {
                const format = function (ms) {
                    const minutes = Math.floor(ms / 60000);
                    const days = Math.floor(minutes / 1440);
                    const hours = Math.floor((minutes % 1440) / 60);
                    return (days ? days + 'h ' : '') + hours + 'j ' + (minutes % 60) + 'm';
                };
                const tick = function () {
                    document.querySelectorAll('[data-sla-deadline]').forEach(function (el) {
                        const diff = new Date(el.dataset.slaDeadline).getTime() - Date.now();
                        const short = el.hasAttribute('data-sla-short');
                        el.textContent = diff >= 0
                            ? (short ? format(diff) : 'Sisa waktu ' + format(diff))
                            : (short ? '-' + format(-diff) : 'Terlambat ' + format(-diff));
                    });
                };
                document.addEventListener('DOMContentLoaded', tick);
                setInterval(tick, 30000);
            })();
        </script>
    @endonce
@endif
