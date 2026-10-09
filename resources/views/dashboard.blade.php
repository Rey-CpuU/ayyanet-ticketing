@php
    $navItems = [
        ['id' => 'queue', 'label' => 'Queue', 'count' => 6],
        ['id' => 'mine', 'label' => 'My Tickets', 'count' => 3],
        ['id' => 'all', 'label' => 'All Tickets', 'count' => 312],
        ['id' => 'reports', 'label' => 'Reports', 'count' => null],
    ];

    $tickets = [
        [
            'id' => 'TKT-4812', 'title' => 'Payment gateway returning 500 errors on checkout', 'customer' => 'Mara Okonkwo', 'email' => 'mara@vertexlabs.io', 'avatar' => 'MO', 'priority' => 'critical', 'status' => 'open', 'category' => 'bug', 'agent' => null, 'agentAvatar' => null, 'created' => '2026-08-04 09:14', 'updated' => '3 min ago', 'messages' => 3, 'sla' => '1h 47m', 'tags' => ['payments', 'production'], 'excerpt' => 'We started seeing 500 errors from the Stripe integration around 9 AM. All checkout flows are broken — approximately 40% of transactions failing.'
        ],
        [
            'id' => 'TKT-4811', 'title' => 'Cannot access admin dashboard after SSO migration', 'customer' => 'Tomás Herrera', 'email' => 'tomas@clarionworks.com', 'avatar' => 'TH', 'priority' => 'high', 'status' => 'in-progress', 'category' => 'access', 'agent' => 'Priya S.', 'agentAvatar' => 'PS', 'created' => '2026-08-04 08:52', 'updated' => '18 min ago', 'messages' => 7, 'sla' => '3h 12m', 'tags' => ['sso', 'auth'], 'excerpt' => 'After migrating to Okta SSO last Thursday, my team lead and I cannot access the admin panel. Regular users are unaffected.'
        ],
        [
            'id' => 'TKT-4810', 'title' => 'Bulk export exceeds row limit for enterprise plan', 'customer' => 'Yuki Nakamura', 'email' => 'yuki@dataflow.jp', 'avatar' => 'YN', 'priority' => 'medium', 'status' => 'pending', 'category' => 'feature', 'agent' => 'Jordan K.', 'agentAvatar' => 'JK', 'created' => '2026-08-03 16:30', 'updated' => '2h ago', 'messages' => 4, 'sla' => '8h 00m', 'tags' => ['export', 'limits', 'enterprise'], 'excerpt' => 'Our enterprise contract states 500k row exports but the system caps at 100k. We need this for our end-of-quarter reporting.'
        ],
        [
            'id' => 'TKT-4809', 'title' => 'Webhook payloads missing custom metadata fields', 'customer' => 'Asha Patel', 'email' => 'asha@nexbridge.co', 'avatar' => 'AP', 'priority' => 'high', 'status' => 'in-progress', 'category' => 'bug', 'agent' => 'Priya S.', 'agentAvatar' => 'PS', 'created' => '2026-08-03 14:17', 'updated' => '4h ago', 'messages' => 11, 'sla' => '5h 30m', 'tags' => ['webhooks', 'api'], 'excerpt' => 'The metadata object in our webhook payloads is coming back empty since the v2.3 API release. Our integrations are breaking silently.'
        ],
        [
            'id' => 'TKT-4808', 'title' => 'Invoice PDF generation fails for multi-currency accounts', 'customer' => 'Lars Eriksson', 'email' => 'lars@norden-solutions.se', 'avatar' => 'LE', 'priority' => 'medium', 'status' => 'open', 'category' => 'billing', 'agent' => null, 'agentAvatar' => null, 'created' => '2026-08-03 11:05', 'updated' => '6h ago', 'messages' => 2, 'sla' => '12h 00m', 'tags' => ['billing', 'pdf', 'multi-currency'], 'excerpt' => 'Generating invoices for our EUR-denominated accounts produces a blank PDF. USD accounts work fine. Started after the June billing update.'
        ],
        [
            'id' => 'TKT-4807', 'title' => 'Database replica lag causing stale reads in analytics', 'customer' => 'Chinonso Eze', 'email' => 'chinonso@qoratech.ng', 'avatar' => 'CE', 'priority' => 'critical', 'status' => 'in-progress', 'category' => 'infrastructure', 'agent' => 'Jordan K.', 'agentAvatar' => 'JK', 'created' => '2026-08-03 09:41', 'updated' => '8h ago', 'messages' => 9, 'sla' => '2h 15m', 'tags' => ['db', 'replica', 'analytics'], 'excerpt' => 'Our analytics dashboard is showing data that is 20-40 minutes stale. The read replica appears to be falling behind the primary significantly.'
        ],
    ];

    $priorityColors = [
        'critical' => ['label' => 'Critical', 'color' => '#ef4444', 'dot' => 'bg-red-500'],
        'high' => ['label' => 'High', 'color' => '#f59e0b', 'dot' => 'bg-amber-500'],
        'medium' => ['label' => 'Medium', 'color' => '#3b82f6', 'dot' => 'bg-blue-500'],
        'low' => ['label' => 'Low', 'color' => '#6b7280', 'dot' => 'bg-slate-500'],
    ];

    $statusColors = [
        'open' => ['label' => 'Open', 'bg' => 'rgba(239,68,68,0.12)', 'text' => '#ef4444'],
        'in-progress' => ['label' => 'In Progress', 'bg' => 'rgba(124,92,252,0.15)', 'text' => '#a78bfa'],
        'pending' => ['label' => 'Pending', 'bg' => 'rgba(245,158,11,0.12)', 'text' => '#fbbf24'],
        'resolved' => ['label' => 'Resolved', 'bg' => 'rgba(34,197,94,0.12)', 'text' => '#4ade80'],
        'closed' => ['label' => 'Closed', 'bg' => 'rgba(107,114,128,0.12)', 'text' => '#9ca3af'],
    ];

    $categoryIcons = [
        'bug' => ['label' => 'Bug', 'icon' => '⚠'],
        'billing' => ['label' => 'Billing', 'icon' => '₿'],
        'feature' => ['label' => 'Feature Request', 'icon' => '◆'],
        'access' => ['label' => 'Access', 'icon' => '⬡'],
        'infrastructure' => ['label' => 'Infrastructure', 'icon' => '⬟'],
    ];

    $selectedTicket = $tickets[0];
    $comments = [
        [
            'author' => 'Mara Okonkwo', 'avatar' => 'MO', 'time' => '09:14', 'body' => "We started seeing 500 errors from the Stripe integration around 9 AM. All checkout flows are broken — approximately 40% of transactions failing. Here's a sample error trace:\n\n`POST /api/v1/payments → 500 Internal Server Error`\n`StripeInvalidRequestError: No such PaymentIntent`\n\nThis is blocking all new sales.", 'internal' => false,
        ],
        [
            'author' => 'Mara Okonkwo', 'avatar' => 'MO', 'time' => '09:22', 'body' => 'Checked the Stripe dashboard — the webhook endpoint for payment_intent.created is returning errors. Could this be related to the deployment last night?', 'internal' => false,
        ],
        [
            'author' => 'Support Bot', 'avatar' => 'SB', 'time' => '09:14', 'body' => 'Ticket auto-assigned critical SLA: 2 hours. Escalation triggered to on-call team.', 'internal' => true,
        ],
    ];
@endphp

<div class="ticket-shell">
    <aside class="w-[220px] shrink-0 border-r border-[#1f2333] bg-[#12141c] px-0 py-5">
        <div class="px-5 pb-5 border-b border-[#1f2333]">
            <div class="flex items-center gap-3">
                <div class="flex h-[30px] w-[30px] items-center justify-center rounded-lg bg-[#7c5cfc]">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M2 3h12v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V3Z" fill="white" opacity=".9"/>
                        <path d="M2 3l6-2 6 2" stroke="white" stroke-width="1.2"/>
                    </svg>
                </div>
                <div>
                    <div class="font-display text-[14px] font-bold tracking-[-0.01em] text-[#e8eaf0]">Resolv</div>
                    <div class="text-[10px] text-[#8b90a7]">Support Desk</div>
                </div>
            </div>
        </div>

        <nav class="flex flex-1 flex-col gap-1 px-2 pt-4">
            @foreach ($navItems as $item)
                @php
                    $active = $item['id'] === 'queue';
                @endphp
                <button type="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-left transition {{ $active ? 'bg-[#7c5cfc]/10 text-[#7c5cfc]' : 'text-[#8b90a7] hover:bg-white/5' }}">
                    @if ($item['id'] === 'queue')
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <rect x="1" y="3" width="14" height="2" rx="1" fill="currentColor" opacity=".8"/>
                            <rect x="1" y="7" width="14" height="2" rx="1" fill="currentColor" opacity=".6"/>
                            <rect x="1" y="11" width="9" height="2" rx="1" fill="currentColor" opacity=".4"/>
                        </svg>
                    @elseif ($item['id'] === 'mine')
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <circle cx="8" cy="5" r="3" fill="currentColor" opacity=".8"/>
                            <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".8"/>
                        </svg>
                    @elseif ($item['id'] === 'all')
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <circle cx="3" cy="4" r="1.2" fill="currentColor" opacity=".7"/>
                            <rect x="6" y="3" width="8" height="2" rx="1" fill="currentColor" opacity=".7"/>
                            <circle cx="3" cy="8" r="1.2" fill="currentColor" opacity=".7"/>
                            <rect x="6" y="7" width="8" height="2" rx="1" fill="currentColor" opacity=".7"/>
                            <circle cx="3" cy="12" r="1.2" fill="currentColor" opacity=".7"/>
                            <rect x="6" y="11" width="8" height="2" rx="1" fill="currentColor" opacity=".7"/>
                        </svg>
                    @else
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <rect x="1" y="9" width="3" height="5" rx="1" fill="currentColor" opacity=".7"/>
                            <rect x="6" y="6" width="3" height="8" rx="1" fill="currentColor" opacity=".7"/>
                            <rect x="11" y="2" width="3" height="12" rx="1" fill="currentColor" opacity=".7"/>
                        </svg>
                    @endif
                    <span class="flex-1 text-[13px] font-medium">{{ $item['label'] }}</span>
                    @if ($item['count'] !== null)
                        <span class="rounded-full px-2 py-[1px] text-[10.5px] font-semibold font-mono {{ $active ? 'bg-[#7c5cfc]/20 text-[#7c5cfc]' : 'bg-[#1f2333] text-[#8b90a7]' }}">
                            {{ $item['count'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>

        <div class="border-t border-[#1f2333] px-4 pt-4">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="flex h-[30px] w-[30px] items-center justify-center rounded-full border border-[#7c5cfc]/40 bg-[#7c5cfc]/20 text-[11px] font-semibold text-[#8b5cf6]">PS</div>
                    <div class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-[1.5px] border-[#12141c] bg-[#22c55e]"></div>
                </div>
                <div>
                    <div class="text-[12.5px] font-semibold text-[#e8eaf0]">Priya Sharma</div>
                    <div class="text-[10.5px] text-[#4ade80]">Online</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="min-w-0 flex-1 overflow-hidden border-r border-[#1f2333] bg-[#0c0e14]">
        <div class="border-b border-[#1f2333] px-5 py-4">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="font-display text-[17px] font-bold tracking-[-0.02em] text-[#e8eaf0]">Ticket Queue</h1>
                <button type="button" class="inline-flex items-center gap-2 rounded-md bg-[#7c5cfc] px-3.5 py-2 text-[12.5px] font-semibold text-white">
                    + New Ticket
                </button>
            </div>

            <div class="mb-4 grid grid-cols-4 gap-3">
                <div class="rounded-lg border border-[#1f2333] bg-[#181b26] p-3.5">
                    <div class="mb-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[#8b90a7]">Open</div>
                    <div class="text-[22px] font-bold font-display text-[#ef4444]">{{ collect($tickets)->where('status', 'open')->count() }}</div>
                    <div class="mt-1 text-[11px] text-[#8b90a7]">needs attention</div>
                </div>
                <div class="rounded-lg border border-[#1f2333] bg-[#181b26] p-3.5">
                    <div class="mb-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[#8b90a7]">In Progress</div>
                    <div class="text-[22px] font-bold font-display text-[#a78bfa]">{{ collect($tickets)->where('status', 'in-progress')->count() }}</div>
                    <div class="mt-1 text-[11px] text-[#8b90a7]">being handled</div>
                </div>
                <div class="rounded-lg border border-[#1f2333] bg-[#181b26] p-3.5">
                    <div class="mb-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[#8b90a7]">Critical SLA</div>
                    <div class="text-[22px] font-bold font-display text-[#f59e0b]">2</div>
                    <div class="mt-1 text-[11px] text-[#8b90a7]">&lt; 2h remaining</div>
                </div>
                <div class="rounded-lg border border-[#1f2333] bg-[#181b26] p-3.5">
                    <div class="mb-2 text-[10.5px] font-semibold uppercase tracking-[0.08em] text-[#8b90a7]">Resolved Today</div>
                    <div class="text-[22px] font-bold font-display text-[#e8eaf0]">4</div>
                    <div class="mt-1 text-[11px] text-[#8b90a7]">vs 3 yesterday</div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#8b90a7]">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <circle cx="6.5" cy="6.5" r="4.5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M10 10l3.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <input type="text" value="" placeholder="Search tickets, customers, IDs..." class="w-full rounded-md border border-[#2a2f45] bg-[#181b26] py-2 pl-9 pr-3 text-[12.5px] text-[#e8eaf0] placeholder:text-[#8b90a7] outline-none focus:border-[#7c5cfc]" />
                </div>
                <button type="button" class="flex items-center gap-2 rounded-md border border-[#2a2f45] bg-[#181b26] px-3 py-2 text-[12.5px] text-[#8b90a7]">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M1 3h14M3 8h10M6 13h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Filter
                </button>
            </div>

            <div class="mt-4 flex gap-1">
                @foreach (['All', 'Open', 'In Progress', 'Pending', 'Resolved'] as $filter)
                    <button type="button" class="rounded px-3 py-1.5 text-[12px] font-medium {{ $filter === 'All' ? 'bg-[#7c5cfc]/15 text-[#7c5cfc]' : 'text-[#8b90a7]' }}">
                        {{ $filter }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="ticket-list h-[calc(100vh-248px)] overflow-y-auto">
            @foreach ($tickets as $ticket)
                @php
                    $priority = $priorityColors[$ticket['priority']];
                    $status = $statusColors[$ticket['status']];
                    $category = $categoryIcons[$ticket['category']];
                    $selected = $ticket['id'] === $selectedTicket['id'];
                @endphp
                <button type="button" class="flex w-full flex-col gap-2 border-b border-[#1f2333] px-4 py-3 text-left transition {{ $selected ? 'bg-[#7c5cfc]/8' : 'hover:bg-white/5' }}" style="border-left: {{ $selected ? '2px solid #7c5cfc' : '2px solid transparent' }};">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <div class="flex h-[28px] w-[28px] items-center justify-center rounded-full border border-[#2a2f45] bg-[#1d2230] text-[11px] font-semibold text-[#e8eaf0]">{{ $ticket['avatar'] }}</div>
                            <div class="min-w-0">
                                <div class="max-w-[280px] truncate text-[13.5px] font-medium text-[#e8eaf0]">{{ $ticket['title'] }}</div>
                                <div class="mt-1 text-[11.5px] text-[#8b90a7]">{{ $ticket['customer'] }} · {{ $ticket['email'] }}</div>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1">
                            <span class="rounded px-2 py-[2px] text-[10px] font-semibold uppercase tracking-[0.04em]" style="background: {{ $status['bg'] }}; color: {{ $status['text'] }};">{{ $status['label'] }}</span>
                            <span class="font-mono text-[10.5px] text-[#8b90a7]">{{ $ticket['updated'] }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px]">
                        <div class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $priority['color'] }}"></span>
                            <span class="font-mono uppercase tracking-[0.04em]" style="color: {{ $priority['color'] }};">{{ $priority['label'] }}</span>
                        </div>
                        <span class="text-[#2a2f45]">·</span>
                        <span class="font-mono text-[#8b90a7]">{{ $ticket['id'] }}</span>
                        <span class="text-[#2a2f45]">·</span>
                        <span class="text-[#8b90a7]">{{ $category['icon'] }} {{ $category['label'] }}</span>
                        @if ($ticket['agent'])
                            <span class="text-[#2a2f45]">·</span>
                            <span class="text-[#8b90a7]">→ {{ $ticket['agent'] }}</span>
                        @endif
                        <span class="ml-auto font-mono {{ $ticket['priority'] === 'critical' ? 'text-[#ef4444]' : 'text-[#8b90a7]' }}">SLA {{ $ticket['sla'] }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </main>

    <aside class="w-[400px] shrink-0 bg-[#12141c]">
        <div class="flex h-full flex-col bg-[#12141c]">
            <div class="border-b border-[#1f2333] px-5 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="mb-1.5 flex items-center gap-2">
                            <span class="font-mono text-[11.5px] font-medium text-[#7c5cfc]">{{ $selectedTicket['id'] }}</span>
                            <span class="rounded px-2 py-[2px] text-[10px] font-semibold uppercase tracking-[0.04em]" style="background: {{ $statusColors[$selectedTicket['status']]['bg'] }}; color: {{ $statusColors[$selectedTicket['status']]['text'] }};">{{ $statusColors[$selectedTicket['status']]['label'] }}</span>
                            <span class="flex items-center gap-1.5 text-[11px] font-medium font-mono uppercase tracking-[0.04em]" style="color: {{ $priorityColors[$selectedTicket['priority']]['color'] }};">
                                <span class="h-1.5 w-1.5 rounded-full" style="background: {{ $priorityColors[$selectedTicket['priority']]['color'] }};"></span>
                                {{ $priorityColors[$selectedTicket['priority']]['label'] }}
                            </span>
                        </div>
                        <h2 class="font-display text-[15px] font-semibold leading-[1.4] text-[#e8eaf0]">{{ $selectedTicket['title'] }}</h2>
                    </div>
                    <button type="button" class="flex items-center justify-center rounded-md p-1 text-[#8b90a7] hover:bg-white/5">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                            <path d="M3 3l8 8M11 3l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="mt-4 flex gap-0 border-b border-[#1f2333]">
                    <button type="button" class="border-b-2 border-[#7c5cfc] px-3.5 pb-2 pt-1 text-[12.5px] font-medium text-[#e8eaf0]">Conversation</button>
                    <button type="button" class="px-3.5 pb-2 pt-1 text-[12.5px] font-medium text-[#8b90a7]">Details</button>
                </div>
            </div>

            <div class="ticket-detail-scroll flex-1 overflow-y-auto px-5 py-4">
                @foreach ($comments as $comment)
                    @if (!empty($comment['internal']))
                        <div class="mb-4 rounded-md border border-[#f59e0b]/30 bg-[#f59e0b]/5 p-3">
                            <div class="mb-2 flex items-center gap-2.5">
                                <div class="flex h-[24px] w-[24px] items-center justify-center rounded-full bg-[#f59e0b]/15 text-[10px] font-semibold text-[#fbbf24]">{{ $comment['avatar'] }}</div>
                                <div class="font-mono text-[11px] uppercase tracking-[0.06em] text-[#fbbf24]">Internal Note · {{ $comment['time'] }}</div>
                            </div>
                            <div class="text-[12.5px] leading-6 text-[#8b90a7]">{{ $comment['body'] }}</div>
                        </div>
                    @else
                        <div class="mb-4 flex gap-3">
                            <div class="flex h-[32px] w-[32px] items-center justify-center rounded-full border border-[#2a2f45] bg-[#1d2230] text-[11px] font-semibold text-[#e8eaf0]">{{ $comment['avatar'] }}</div>
                            <div class="flex-1">
                                <div class="mb-1.5 flex items-center gap-2">
                                    <span class="text-[13px] font-semibold text-[#e8eaf0]">{{ $comment['author'] }}</span>
                                    <span class="font-mono text-[11px] text-[#8b90a7]">{{ $comment['time'] }}</span>
                                </div>
                                <div class="rounded-tr-md rounded-br-md rounded-bl-md border border-[#1f2333] bg-[#181b26] p-3 text-[13px] leading-[1.65] text-[#e8eaf0] whitespace-pre-wrap">
                                    {{ $comment['body'] }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="border-t border-[#1f2333] px-5 py-4">
                <div class="mb-2 flex gap-2">
                    <button type="button" class="rounded bg-[#7c5cfc] px-3 py-1.5 text-[11.5px] font-medium text-white">Reply</button>
                    <button type="button" class="rounded bg-[#181b26] px-3 py-1.5 text-[11.5px] font-medium text-[#8b90a7]">Internal Note</button>
                </div>
                <textarea rows="4" class="w-full resize-none rounded-md border border-[#2a2f45] bg-[#181b26] p-3 text-[13px] text-[#e8eaf0] placeholder:text-[#8b90a7] outline-none" placeholder="Write a reply..."></textarea>
                <div class="mt-2 flex items-center justify-between">
                    <button type="button" class="flex items-center gap-2 text-[12px] text-[#8b90a7]">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M13 7.5L7 13.5a4 4 0 0 1-5.657-5.657L8.5 1.5a2.5 2.5 0 0 1 3.535 3.535L5.5 11.5a1 1 0 0 1-1.414-1.414L10 4.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        Attach
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-md bg-[#7c5cfc] px-4 py-2 text-[12.5px] font-semibold text-white">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M14 2L1 7l5 2.5L8.5 15 14 2Z" fill="currentColor" opacity=".85"/>
                        </svg>
                        Send Reply
                    </button>
                </div>
            </div>
        </div>
    </aside>
</div>
