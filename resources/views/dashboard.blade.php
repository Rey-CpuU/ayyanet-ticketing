@php
    $channelOptions = [
        ['key' => 'All', 'label' => 'Semua Saluran', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/></svg>'],
        ['key' => 'Email', 'label' => 'Email', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'],
        ['key' => 'Live Chat', 'label' => 'Live Chat', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v6A2.5 2.5 0 0 1 14.5 13H9l-4.5 3v-3H5.5A2.5 2.5 0 0 1 3 10.5v-6Zm2 1.5h10v1H5V6Zm0 3h7v1H5v-1Z"/></svg>'],
        ['key' => 'WhatsApp', 'label' => 'WhatsApp', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 1.8A8.2 8.2 0 0 0 3.1 13.7L2 18l4.4-1.1A8.2 8.2 0 1 0 10 1.8Zm4.7 11.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1.3.2-4.1-1.2-3.5-1.7-5.8-6.1-6-6.4-.2-.4-.2-.9.1-1.3.1-.1.3-.2.5-.3l.5-.4c.2-.1.3-.1.5 0l.7.5c.2.2.4.5.5.8.1.2.3.5.1.6-.1.2-.2.3-.3.4-.2.2-.4.4-.6.6-.2.2-.1.4.1.6l.7.8c.3.3.7.5 1 .8.2.1.4.2.7.1.2-.1.7-.8.9-1.1.2-.3.4-.3.7-.2l.9.4c.2.1.4.3.4.5.1.2 0 .6-.2.8Z"/></svg>'],
        ['key' => 'Web Form', 'label' => 'Web Form', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v11A2.5 2.5 0 0 1 14.5 18h-9A2.5 2.5 0 0 1 3 15.5v-11Zm2.5-.5a.5.5 0 0 0-.5.5v1h10v-1a.5.5 0 0 0-.5-.5h-9Zm-.5 4v6h10v-6H5Zm2 1h4v1H7v-1Zm0 2h6v1H7v-1Z"/></svg>'],
        ['key' => 'Portal', 'label' => 'Portal', 'icon' => '<svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2.3A7.7 7.7 0 1 1 2.3 10 7.7 7.7 0 0 1 10 2.3Zm0 1.5a6.2 6.2 0 1 0 6.2 6.2A6.2 6.2 0 0 0 10 3.8Zm-1 2.2h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>'],
    ];

    $selectedChannel = $channel ?? 'All';

    $channelMeta = function ($ticket) {
        $raw = strtolower($ticket->category ?? 'Email');

        return match ($raw) {
            'email' => ['label' => 'Email', 'class' => 'email', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'],
            'live chat' => ['label' => 'Live Chat', 'class' => 'live-chat', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v6A2.5 2.5 0 0 1 14.5 13H9l-4.5 3v-3H5.5A2.5 2.5 0 0 1 3 10.5v-6Zm2 1.5h10v1H5V6Zm0 3h7v1H5v-1Z"/></svg>'],
            'whatsapp' => ['label' => 'WhatsApp', 'class' => 'whatsapp', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 1.8A8.2 8.2 0 0 0 3.1 13.7L2 18l4.4-1.1A8.2 8.2 0 1 0 10 1.8Zm4.7 11.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1.3.2-4.1-1.2-3.5-1.7-5.8-6.1-6-6.4-.2-.4-.2-.9.1-1.3.1-.1.3-.2.5-.3l.5-.4c.2-.1.3-.1.5 0l.7.5c.2.2.4.5.5.8.1.2.3.5.1.6-.1.2-.2.3-.3.4-.2.2-.4.4-.6.6-.2.2-.1.4.1.6l.7.8c.3.3.7.5 1 .8.2.1.4.2.7.1.2-.1.7-.8.9-1.1.2-.3.4-.3.7-.2l.9.4c.2.1.4.3.4.5.1.2 0 .6-.2.8Z"/></svg>'],
            'web form' => ['label' => 'Web Form', 'class' => 'web-form', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v11A2.5 2.5 0 0 1 14.5 18h-9A2.5 2.5 0 0 1 3 15.5v-11Zm2.5-.5a.5.5 0 0 0-.5.5v1h10v-1a.5.5 0 0 0-.5-.5h-9Zm-.5 4v6h10v-6H5Zm2 1h4v1H7v-1Zm0 2h6v1H7v-1Z"/></svg>'],
            'portal' => ['label' => 'Portal', 'class' => 'portal', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2.3A7.7 7.7 0 1 1 2.3 10 7.7 7.7 0 0 1 10 2.3Zm0 1.5a6.2 6.2 0 1 0 6.2 6.2A6.2 6.2 0 0 0 10 3.8Zm-1 2.2h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>'],
            default => ['label' => 'Email', 'class' => 'email', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'],
        };
    };

    $detailChannel = $selected ? $channelMeta($selected) : ['label' => 'Email', 'class' => 'email', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'];
@endphp

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Ticket Queue — AyyNet</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            /* ============================================
               PREMIUM ENTERPRISE DASHBOARD — AYYNET ISP
               Linear × Stripe × Vercel × Arc
               ============================================ */

            :root {
                --bg: #0B0D12;
                --surface: #141824;
                --card: #1A1F2D;
                --card-hover: #1E2434;
                --card-border: rgba(255, 255, 255, 0.05);
                --card-border-hover: rgba(124, 92, 255, 0.2);
                --primary: #7C5CFF;
                --primary-soft: rgba(124, 92, 255, 0.12);
                --primary-border: rgba(124, 92, 255, 0.3);
                --success: #34D399;
                --success-soft: rgba(52, 211, 153, 0.1);
                --warning: #FBBF24;
                --warning-soft: rgba(251, 191, 36, 0.1);
                --danger: #FF5D73;
                --danger-soft: rgba(255, 93, 115, 0.1);
                --blue: #7CB8FF;
                --blue-soft: rgba(124, 184, 255, 0.1);
                --cyan: #67E8F9;
                --cyan-soft: rgba(103, 232, 249, 0.1);
                --text: #FFFFFF;
                --text-2: #A0A6B4;
                --text-3: #6E7585;

                /* Premium layered shadows — subtle 3D */
                --shadow-card:
                    0 12px 40px rgba(0, 0, 0, 0.45),
                    0 4px 12px rgba(124, 92, 255, 0.06),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
                --shadow-hover:
                    0 20px 50px rgba(0, 0, 0, 0.55),
                    0 8px 20px rgba(124, 92, 255, 0.12),
                    inset 0 1px 0 rgba(255, 255, 255, 0.06);
                --shadow-btn:
                    0 4px 14px rgba(124, 92, 255, 0.25),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                --shadow-btn-hover:
                    0 8px 24px rgba(124, 92, 255, 0.4),
                    inset 0 1px 0 rgba(255, 255, 255, 0.14);
                --shadow-sidebar:
                    0 0 0 1px rgba(255, 255, 255, 0.04),
                    0 24px 60px rgba(0, 0, 0, 0.6),
                    0 8px 24px rgba(124, 92, 255, 0.05);

                --radius: 16px;
                --radius-lg: 20px;
                --radius-xl: 24px;

                --transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
                --spring: cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }

            html, body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: var(--bg);
                color: var(--text);
                font-size: 14px;
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
                text-rendering: optimizeLegibility;
            }

            body { min-height: 100vh; }

            button, input, select, textarea { font: inherit; color: inherit; }
            button { cursor: pointer; background: none; border: none; }
            a { text-decoration: none; color: inherit; }

            :focus-visible {
                outline: 2px solid var(--primary);
                outline-offset: 2px;
                border-radius: 6px;
            }

            /* ===== AMBIENT BACKGROUND ===== */
            .dashboard-shell {
                display: flex;
                min-height: 100vh;
                padding: 16px;
                gap: 16px;
                position: relative;
                overflow: hidden;
                background:
                    radial-gradient(ellipse 80% 50% at 100% 0%, rgba(124, 92, 255, 0.07), transparent 55%),
                    radial-gradient(ellipse 60% 40% at 0% 100%, rgba(124, 184, 255, 0.05), transparent 55%),
                    var(--bg);
            }

            /* ===== FLOATING SIDEBAR ===== */
            .sidebar {
                width: 232px;
                min-width: 232px;
                background: linear-gradient(180deg, #161A26 0%, #12151F 100%);
                border: 1px solid var(--card-border);
                border-radius: var(--radius-xl);
                padding: 20px 14px;
                display: flex;
                flex-direction: column;
                position: sticky;
                top: 16px;
                height: calc(100vh - 32px);
                z-index: 40;
                box-shadow: var(--shadow-sidebar);
                transition: width var(--transition), transform var(--transition), box-shadow var(--transition);
            }

            .sidebar.collapsed {
                width: 76px;
                min-width: 76px;
            }

            .sidebar.collapsed .brand-text,
            .sidebar.collapsed .brand-subtitle,
            .sidebar.collapsed .nav-label,
            .sidebar.collapsed .nav-badge,
            .sidebar.collapsed .user-meta,
            .sidebar.collapsed .sidebar-footer-text {
                display: none;
            }

            .sidebar.collapsed .nav-item {
                justify-content: center;
                padding: 12px;
            }

            .sidebar.collapsed .user-card {
                justify-content: center;
                padding: 10px 6px;
            }

            .sidebar.collapsed .profile-avatar {
                margin: 0;
            }

            .brand-wrap {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 4px 8px 18px;
            }

            .brand-icon {
                width: 38px;
                height: 38px;
                display: grid;
                place-items: center;
                border-radius: 12px;
                background: linear-gradient(135deg, #8B7CFF 0%, #6D4FFF 50%, #5C4FD8 100%);
                color: #fff;
                font-size: 18px;
                font-weight: 800;
                flex-shrink: 0;
                box-shadow:
                    0 8px 20px rgba(124, 92, 255, 0.35),
                    inset 0 1px 0 rgba(255, 255, 255, 0.25);
            }

            .brand-text {
                font-size: 1.1rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1;
            }

            .brand-subtitle {
                color: var(--text-3);
                font-size: 0.7rem;
                margin-top: 2px;
                letter-spacing: 0.02em;
            }

            .sidebar-toggle {
                margin-left: auto;
                width: 30px;
                height: 30px;
                display: grid;
                place-items: center;
                border-radius: 9px;
                color: var(--text-3);
                transition: background var(--transition), color var(--transition);
            }

            .sidebar-toggle:hover {
                background: rgba(255, 255, 255, 0.06);
                color: var(--text);
            }

            .side-nav {
                display: flex;
                flex-direction: column;
                gap: 4px;
                margin-top: 4px;
            }

            .nav-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 11px 12px;
                border-radius: 12px;
                color: var(--text-2);
                font-weight: 500;
                font-size: 0.88rem;
                position: relative;
                transition: background var(--transition), color var(--transition), transform var(--transition), box-shadow var(--transition);
            }

            .nav-item:hover {
                background: rgba(255, 255, 255, 0.04);
                color: var(--text);
                transform: translateX(4px);
            }

            .nav-item.active {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.16), rgba(124, 92, 255, 0.08));
                color: #fff;
                font-weight: 600;
                box-shadow:
                    0 4px 16px rgba(124, 92, 255, 0.12),
                    inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .nav-item.active::before {
                content: '';
                position: absolute;
                left: -14px;
                top: 22%;
                bottom: 22%;
                width: 3px;
                border-radius: 0 3px 3px 0;
                background: var(--primary);
                box-shadow: 0 0 12px rgba(124, 92, 255, 0.6);
            }

            .nav-icon {
                width: 20px;
                height: 20px;
                display: grid;
                place-items: center;
                flex-shrink: 0;
                opacity: 0.8;
                transition: opacity var(--transition), transform var(--transition);
            }

            .nav-item:hover .nav-icon,
            .nav-item.active .nav-icon {
                opacity: 1;
                transform: scale(1.1);
            }

            .nav-badge {
                margin-left: auto;
                min-width: 22px;
                height: 22px;
                padding: 0 7px;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: var(--primary);
                color: #fff;
                font-size: 0.66rem;
                font-weight: 700;
                box-shadow: 0 2px 8px rgba(124, 92, 255, 0.3);
            }

            .nav-badge.muted {
                background: rgba(255, 255, 255, 0.07);
                color: var(--text-2);
                box-shadow: none;
            }

            /* Floating profile card */
            .user-card {
                margin-top: auto;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: 14px;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02));
                border: 1px solid var(--card-border);
                box-shadow:
                    0 8px 20px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .profile-avatar {
                width: 38px;
                height: 38px;
                border-radius: 12px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #7C5CFF, #5C4FD8);
                font-size: 0.75rem;
                font-weight: 700;
                color: #fff;
                flex-shrink: 0;
                position: relative;
                box-shadow: 0 4px 12px rgba(124, 92, 255, 0.3);
            }

            .profile-avatar .online-dot {
                position: absolute;
                bottom: -2px;
                right: -2px;
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background: var(--success);
                border: 2px solid var(--card);
                box-shadow: 0 0 8px rgba(52, 211, 153, 0.6);
            }

            .user-meta {
                display: flex;
                flex-direction: column;
                line-height: 1.3;
                min-width: 0;
            }

            .user-meta strong {
                font-size: 0.84rem;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .user-meta span {
                color: var(--text-3);
                font-size: 0.72rem;
            }

            .sidebar-footer {
                padding: 10px 10px 0;
                color: var(--text-3);
                font-size: 0.68rem;
            }

            /* ===== MAIN PANEL ===== */
            .main-panel {
                flex: 1;
                display: flex;
                min-width: 0;
                gap: 16px;
                height: calc(100vh - 32px);
                overflow: hidden;
            }

            /* ===== QUEUE PANEL ===== */
            .queue-panel {
                flex: 1.2;
                min-width: 0;
                display: flex;
                flex-direction: column;
                overflow-y: auto;
                padding-right: 4px;
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.08) transparent;
            }

            .queue-panel::-webkit-scrollbar { width: 4px; }
            .queue-panel::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.08);
                border-radius: 4px;
            }

            /* ===== HEADER ===== */
            .queue-header {
                display: flex;
                align-items: center;
                gap: 16px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .header-left {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 0;
            }

            .mobile-menu-btn {
                display: none;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: var(--card);
                border: 1px solid var(--card-border);
                place-items: center;
                color: var(--text-2);
                box-shadow: var(--shadow-card);
            }

            .header-title h1 {
                font-size: 1.45rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                line-height: 1.1;
                background: linear-gradient(180deg, #fff, #B8BECB);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .header-title p {
                color: var(--text-3);
                font-size: 0.78rem;
                margin-top: 3px;
            }

            .header-actions {
                margin-left: auto;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .search-shortcut {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 9px 14px;
                border-radius: 12px;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-3);
                font-size: 0.78rem;
                box-shadow: var(--shadow-card);
                transition: border-color var(--transition), background var(--transition), transform var(--transition);
            }

            .search-shortcut:hover {
                border-color: var(--primary-border);
                background: var(--card-hover);
                transform: translateY(-1px);
            }

            .kbd {
                padding: 2px 6px;
                border-radius: 5px;
                background: rgba(255, 255, 255, 0.06);
                border: 1px solid rgba(255, 255, 255, 0.08);
                font-size: 0.66rem;
                font-weight: 600;
                color: var(--text-2);
            }

            .icon-btn {
                width: 40px;
                height: 40px;
                border-radius: 12px;
                display: grid;
                place-items: center;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-2);
                position: relative;
                box-shadow: var(--shadow-card);
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition);
            }

            .icon-btn:hover {
                background: var(--card-hover);
                color: var(--text);
                border-color: var(--card-border-hover);
                transform: translateY(-1px);
            }

            .icon-btn .notif-dot {
                position: absolute;
                top: 8px;
                right: 8px;
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: var(--danger);
                border: 2px solid var(--card);
                box-shadow: 0 0 8px rgba(255, 93, 115, 0.6);
            }

            .new-ticket-btn {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 9px;
                padding: 11px 22px;
                border-radius: 14px;
                background: linear-gradient(135deg, #8B7CFF 0%, #7C5CFF 50%, #6D4FFF 100%);
                color: #fff;
                font-weight: 600;
                font-size: 0.85rem;
                overflow: hidden;
                isolation: isolate;
                box-shadow:
                    0 4px 14px rgba(124, 92, 255, 0.3),
                    0 1px 0 rgba(255, 255, 255, 0.15) inset,
                    0 -1px 0 rgba(0, 0, 0, 0.2) inset;
                transition: transform var(--transition), box-shadow var(--transition), scale var(--transition);
            }

            /* Inner top highlight — glossy feel */
            .new-ticket-btn::before {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: inherit;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0) 45%);
                pointer-events: none;
                z-index: -1;
            }

            /* Shine sweep on hover */
            .new-ticket-btn::after {
                content: '';
                position: absolute;
                top: 0;
                left: -60%;
                width: 50%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
                transform: skewX(-20deg);
                transition: left 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                pointer-events: none;
                z-index: 1;
            }

            .new-ticket-btn:hover {
                transform: translateY(-2px) scale(1.02);
                box-shadow:
                    0 8px 24px rgba(124, 92, 255, 0.45),
                    0 2px 0 rgba(255, 255, 255, 0.2) inset,
                    0 -1px 0 rgba(0, 0, 0, 0.2) inset;
            }

            .new-ticket-btn:hover::after {
                left: 120%;
            }

            .new-ticket-btn:active {
                transform: translateY(0) scale(0.97);
                box-shadow:
                    0 2px 8px rgba(124, 92, 255, 0.3),
                    0 1px 0 rgba(255, 255, 255, 0.1) inset;
            }

            /* Animated plus icon */
            .new-ticket-btn .btn-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
                border-radius: 6px;
                background: rgba(255, 255, 255, 0.15);
                transition: transform var(--transition), background var(--transition);
            }

            .new-ticket-btn:hover .btn-icon {
                transform: rotate(90deg) scale(1.1);
                background: rgba(255, 255, 255, 0.25);
            }

            /* Soft pulsing glow ring */
            .new-ticket-btn .btn-glow {
                position: absolute;
                inset: -2px;
                border-radius: 16px;
                background: radial-gradient(circle at 30% 20%, rgba(124, 92, 255, 0.5), transparent 60%);
                filter: blur(8px);
                opacity: 0;
                z-index: -2;
                transition: opacity var(--transition);
            }

            .new-ticket-btn:hover .btn-glow {
                opacity: 1;
            }

            /* ===== STATS (KPI CARDS) ===== */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
                margin-bottom: 20px;
            }

            .stat-card {
                background: linear-gradient(180deg, var(--card) 0%, rgba(26, 31, 45, 0.6) 100%);
                border: 1px solid var(--card-border);
                border-radius: var(--radius-lg);
                padding: 20px;
                position: relative;
                overflow: hidden;
                box-shadow: var(--shadow-card);
                transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
            }

            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, var(--stat-color), transparent);
                opacity: 0.5;
            }

            .stat-card::after {
                content: '';
                position: absolute;
                top: -40px;
                right: -40px;
                width: 100px;
                height: 100px;
                border-radius: 50%;
                background: radial-gradient(circle, var(--stat-color) 0%, transparent 70%);
                opacity: 0.06;
                transition: opacity var(--transition), transform var(--transition);
            }

            .stat-card:hover {
                transform: translateY(-6px);
                border-color: var(--card-border-hover);
                box-shadow: var(--shadow-hover);
            }

            .stat-card:hover::after {
                opacity: 0.12;
                transform: scale(1.4);
            }

            .stat-card.open { --stat-color: var(--danger); }
            .stat-card.progress { --stat-color: var(--warning); }
            .stat-card.critical { --stat-color: var(--danger); }
            .stat-card.resolved { --stat-color: var(--success); }

            .stat-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 14px;
            }

            .stat-label {
                color: var(--text-3);
                text-transform: uppercase;
                font-size: 0.66rem;
                font-weight: 600;
                letter-spacing: 0.09em;
            }

            .stat-icon {
                width: 34px;
                height: 34px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
            }

            .stat-card.open .stat-icon { background: var(--danger-soft); color: var(--danger); box-shadow: 0 4px 12px rgba(255, 93, 115, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05); }
            .stat-card.progress .stat-icon { background: var(--warning-soft); color: var(--warning); box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05); }
            .stat-card.critical .stat-icon { background: var(--danger-soft); color: var(--danger); box-shadow: 0 4px 12px rgba(255, 93, 115, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05); }
            .stat-card.resolved .stat-icon { background: var(--success-soft); color: var(--success); box-shadow: 0 4px 12px rgba(52, 211, 153, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05); }

            .stat-value {
                font-size: 2.1rem;
                font-weight: 800;
                letter-spacing: -0.04em;
                line-height: 1;
                margin-bottom: 10px;
                background: linear-gradient(180deg, #fff, var(--stat-color));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .stat-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                margin-bottom: 10px;
            }

            .stat-trend {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-size: 0.68rem;
                font-weight: 600;
                padding: 3px 8px;
                border-radius: 999px;
            }

            .stat-trend.up { background: var(--success-soft); color: var(--success); }
            .stat-trend.down { background: var(--danger-soft); color: var(--danger); }

            .stat-sub {
                color: var(--text-3);
                font-size: 0.7rem;
            }

            /* Mini progress bar */
            .stat-progress {
                width: 100%;
                height: 3px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.05);
                overflow: hidden;
                position: relative;
            }

            .stat-progress-fill {
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, var(--stat-color), transparent);
                box-shadow: 0 0 8px var(--stat-color);
                animation: grow-bar 1.2s var(--spring) both;
            }

            @keyframes grow-bar {
                from { width: 0; }
            }

            /* ===== TOOLBAR ===== */
            .toolbar-row {
                display: flex;
                gap: 10px;
                margin-bottom: 14px;
                align-items: center;
            }

            .search-box {
                flex: 1;
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 0 16px;
                border: 1px solid var(--card-border);
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.7));
                border-radius: 14px;
                min-height: 46px;
                box-shadow: var(--shadow-card);
                transition: border-color var(--transition), box-shadow var(--transition);
            }

            .search-box:focus-within {
                border-color: var(--primary-border);
                box-shadow:
                    0 0 0 4px rgba(124, 92, 255, 0.1),
                    0 12px 30px rgba(0, 0, 0, 0.35),
                    inset 0 1px 0 rgba(255, 255, 255, 0.04);
            }

            .search-box svg { color: var(--text-3); flex-shrink: 0; }

            .search-box input {
                flex: 1;
                background: transparent;
                border: none;
                outline: none;
                color: var(--text);
                font-size: 0.85rem;
            }

            .search-box input::placeholder { color: var(--text-3); }

            .search-clear {
                width: 22px;
                height: 22px;
                border-radius: 50%;
                display: grid;
                place-items: center;
                color: var(--text-3);
                font-size: 0.7rem;
                transition: background var(--transition), color var(--transition);
            }

            .search-clear:hover {
                background: rgba(255, 255, 255, 0.08);
                color: var(--text);
            }

            .filter-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 0 18px;
                min-height: 46px;
                border-radius: 14px;
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.7));
                border: 1px solid var(--card-border);
                color: var(--text-2);
                font-weight: 500;
                font-size: 0.82rem;
                box-shadow: var(--shadow-card);
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition);
            }

            .filter-btn:hover {
                background: var(--card-hover);
                color: var(--text);
                border-color: var(--card-border-hover);
                transform: translateY(-1px);
            }

            .filter-btn .filter-count {
                background: var(--primary);
                color: #fff;
                border-radius: 999px;
                padding: 1px 7px;
                font-size: 0.64rem;
                font-weight: 700;
            }

            /* ===== QUICK FILTERS ===== */
            .quick-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 14px;
            }

            .quick-chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 14px;
                border-radius: 999px;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-2);
                font-size: 0.75rem;
                font-weight: 500;
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition), box-shadow var(--transition);
            }

            .quick-chip:hover {
                background: var(--card-hover);
                color: var(--text);
                transform: translateY(-1px);
            }

            .quick-chip.active {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.2), rgba(124, 92, 255, 0.1));
                border-color: var(--primary-border);
                color: #fff;
                font-weight: 600;
                box-shadow: 0 4px 16px rgba(124, 92, 255, 0.15);
            }

            .quick-chip .chip-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
            }

            .quick-chip .chip-dot.red { background: var(--danger); box-shadow: 0 0 6px rgba(255, 93, 115, 0.6); }
            .quick-chip .chip-dot.orange { background: var(--warning); box-shadow: 0 0 6px rgba(251, 191, 36, 0.6); }
            .quick-chip .chip-dot.green { background: var(--success); box-shadow: 0 0 6px rgba(52, 211, 153, 0.6); }
            .quick-chip .chip-dot.blue { background: var(--blue); box-shadow: 0 0 6px rgba(124, 184, 255, 0.6); }

            /* ===== CHANNEL FILTER ===== */
            .channel-row {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 18px;
            }

            .chip-chip {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 14px;
                border-radius: 999px;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-2);
                font-size: 0.78rem;
                font-weight: 500;
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition), box-shadow var(--transition);
            }

            .chip-chip:hover {
                background: var(--card-hover);
                color: var(--text);
                transform: translateY(-1px);
            }

            .chip-chip.selected {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.18), rgba(124, 92, 255, 0.08));
                border-color: var(--primary-border);
                color: #fff;
                font-weight: 600;
                box-shadow: 0 4px 16px rgba(124, 92, 255, 0.12);
            }

            /* ===== TICKET LIST ===== */
            .ticket-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .ticket-item {
                display: grid;
                grid-template-columns: 44px minmax(0, 1fr) auto;
                align-items: center;
                gap: 14px;
                padding: 16px;
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.7));
                border: 1px solid var(--card-border);
                border-radius: var(--radius-lg);
                position: relative;
                box-shadow: var(--shadow-card);
                transition: transform var(--transition), border-color var(--transition), box-shadow var(--transition), background var(--transition);
                overflow: hidden;
            }

            .ticket-item::before {
                content: '';
                position: absolute;
                left: 0;
                top: 18%;
                bottom: 18%;
                width: 3px;
                border-radius: 0 3px 3px 0;
                background: var(--priority-color, transparent);
                opacity: 0;
                transition: opacity var(--transition);
            }

            .ticket-item::after {
                content: '';
                position: absolute;
                top: -30px;
                right: -30px;
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: radial-gradient(circle, var(--priority-color, var(--primary)) 0%, transparent 70%);
                opacity: 0;
                transition: opacity var(--transition);
            }

            .ticket-item:hover {
                transform: translateY(-3px) scale(1.01);
                border-color: var(--card-border-hover);
                box-shadow: var(--shadow-hover);
            }

            .ticket-item:hover::before { opacity: 1; }
            .ticket-item:hover::after { opacity: 0.05; }

            .ticket-item.selected {
                border-color: var(--primary-border);
                background: linear-gradient(180deg, #1D2231, rgba(29, 34, 49, 0.7));
                box-shadow:
                    0 0 0 1px var(--primary-border),
                    0 16px 40px rgba(124, 92, 255, 0.15),
                    0 8px 20px rgba(0, 0, 0, 0.4);
            }

            .ticket-item.priority-high { --priority-color: var(--danger); }
            .ticket-item.priority-medium { --priority-color: var(--warning); }
            .ticket-item.priority-low { --priority-color: var(--blue); }

            .ticket-channel {
                width: 44px;
                height: 44px;
                display: grid;
                place-items: center;
                border-radius: 13px;
                font-size: 0.7rem;
                font-weight: 700;
                flex-shrink: 0;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .ticket-channel.email { background: var(--blue-soft); color: var(--blue); }
            .ticket-channel.live-chat { background: var(--cyan-soft); color: var(--cyan); }
            .ticket-channel.whatsapp { background: var(--success-soft); color: var(--success); }
            .ticket-channel.web-form { background: var(--primary-soft); color: var(--primary); }
            .ticket-channel.portal { background: var(--warning-soft); color: var(--warning); }

            .ticket-main { min-width: 0; }

            .ticket-title-row {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 5px;
            }

            .ticket-title {
                font-weight: 600;
                font-size: 0.9rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                color: var(--text);
            }

            .unread-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--primary);
                box-shadow: 0 0 8px rgba(124, 92, 255, 0.7);
                flex-shrink: 0;
                animation: pulse 2s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            .ticket-subtitle {
                color: var(--text-3);
                font-size: 0.76rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .ticket-subtitle .sep { color: var(--text-3); opacity: 0.4; }

            .ticket-subtitle .customer-chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }

            .ticket-subtitle .customer-chip .mini-avatar {
                width: 18px;
                height: 18px;
                border-radius: 6px;
                display: grid;
                place-items: center;
                background: var(--primary-soft);
                color: var(--primary);
                font-size: 0.55rem;
                font-weight: 700;
            }

            .ticket-right {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 6px;
                flex-shrink: 0;
            }

            .ticket-badge {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 0.66rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                text-transform: uppercase;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .ticket-badge.open { background: var(--blue-soft); color: var(--blue); }
            .ticket-badge.checking { background: var(--warning-soft); color: var(--warning); }
            .ticket-badge.waiting-customer { background: var(--warning-soft); color: var(--warning); }
            .ticket-badge.solved { background: var(--success-soft); color: var(--success); }
            .ticket-badge.closed { background: rgba(255, 255, 255, 0.06); color: var(--text-2); }
            .ticket-badge.escalated { background: var(--danger-soft); color: var(--danger); }

            .ticket-meta {
                color: var(--text-3);
                font-size: 0.7rem;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .sla-countdown {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                font-weight: 600;
            }

            .sla-countdown.breached { color: var(--danger); }
            .sla-countdown.near { color: var(--warning); }
            .sla-countdown.ok { color: var(--success); }

            /* ===== EMPTY STATE ===== */
            .empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 14px;
                padding: 60px 24px;
                text-align: center;
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.6));
                border: 1px dashed rgba(255, 255, 255, 0.08);
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-card);
            }

            .empty-icon {
                width: 68px;
                height: 68px;
                border-radius: 50%;
                display: grid;
                place-items: center;
                background: var(--primary-soft);
                color: var(--primary);
                box-shadow: 0 8px 24px rgba(124, 92, 255, 0.15);
            }

            .empty-title { font-size: 1rem; font-weight: 700; }
            .empty-subtitle { color: var(--text-3); font-size: 0.85rem; max-width: 320px; }

            /* ===== DETAIL PANEL ===== */
            .detail-panel {
                width: 38%;
                min-width: 360px;
                max-width: 480px;
                background: linear-gradient(180deg, var(--surface) 0%, rgba(20, 24, 36, 0.8) 100%);
                border: 1px solid var(--card-border);
                border-radius: var(--radius-xl);
                display: flex;
                flex-direction: column;
                overflow: hidden;
                box-shadow: var(--shadow-sidebar);
            }

            .detail-header {
                padding: 18px 20px;
                border-bottom: 1px solid var(--card-border);
                background: linear-gradient(180deg, rgba(26, 31, 45, 0.6), transparent);
                position: sticky;
                top: 0;
                z-index: 5;
            }

            .detail-header-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
            }

            .detail-title {
                font-size: 0.95rem;
                font-weight: 700;
                color: var(--text);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                background: linear-gradient(180deg, #fff, #B8BECB);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .detail-actions {
                display: flex;
                gap: 6px;
                flex-shrink: 0;
            }

            .mini-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 14px;
                border-radius: 10px;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-2);
                font-size: 0.72rem;
                font-weight: 600;
                box-shadow: var(--shadow-card);
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition);
            }

            .mini-btn:hover {
                background: var(--card-hover);
                color: var(--text);
                border-color: var(--card-border-hover);
                transform: translateY(-1px);
            }

            .mini-btn.primary {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.2), rgba(124, 92, 255, 0.1));
                border-color: var(--primary-border);
                color: #fff;
            }

            .mini-btn.primary:hover { background: rgba(124, 92, 255, 0.25); }

            .detail-status-row {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .tag {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 5px 10px;
                border-radius: 999px;
                font-size: 0.66rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                text-transform: uppercase;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .tag.open { background: var(--blue-soft); color: var(--blue); }
            .tag.checking { background: var(--warning-soft); color: var(--warning); }
            .tag.solved { background: var(--success-soft); color: var(--success); }
            .tag.escalated { background: var(--danger-soft); color: var(--danger); }
            .tag.closed { background: rgba(255, 255, 255, 0.06); color: var(--text-2); }
            .tag.priority-high { background: var(--danger-soft); color: var(--danger); }
            .tag.priority-medium { background: var(--warning-soft); color: var(--warning); }
            .tag.priority-low { background: var(--blue-soft); color: var(--blue); }

            .detail-body {
                flex: 1;
                overflow-y: auto;
                padding: 18px 20px;
                scrollbar-width: thin;
                scrollbar-color: rgba(255, 255, 255, 0.08) transparent;
            }

            .detail-body::-webkit-scrollbar { width: 4px; }
            .detail-body::-webkit-scrollbar-thumb {
                background: rgba(255, 255, 255, 0.08);
                border-radius: 4px;
            }

            /* ===== CUSTOMER CARD ===== */
            .customer-card {
                background: linear-gradient(180deg, var(--card) 0%, rgba(26, 31, 45, 0.6) 100%);
                border: 1px solid var(--card-border);
                border-radius: var(--radius-lg);
                padding: 16px;
                margin-bottom: 14px;
                box-shadow: var(--shadow-card);
            }

            .customer-card-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 14px;
            }

            .customer-avatar {
                width: 44px;
                height: 44px;
                border-radius: 13px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, #8B7CFF, #5C4FD8);
                color: #fff;
                font-size: 0.85rem;
                font-weight: 700;
                flex-shrink: 0;
                box-shadow: 0 4px 12px rgba(124, 92, 255, 0.3);
            }

            .customer-info { min-width: 0; flex: 1; }

            .customer-name {
                font-weight: 600;
                font-size: 0.9rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .customer-id { color: var(--text-3); font-size: 0.72rem; }

            .connection-status {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 10px;
                border-radius: 999px;
                font-size: 0.68rem;
                font-weight: 600;
                flex-shrink: 0;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }

            .connection-status.online { background: var(--success-soft); color: var(--success); box-shadow: 0 4px 12px rgba(52, 211, 153, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.05); }
            .connection-status.offline { background: var(--danger-soft); color: var(--danger); }
            .connection-status.degraded { background: var(--warning-soft); color: var(--warning); }

            .customer-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .detail-item {
                display: flex;
                flex-direction: column;
                gap: 2px;
                padding: 9px 10px;
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(255, 255, 255, 0.04);
                border-radius: 10px;
                transition: border-color var(--transition), background var(--transition);
            }

            .detail-item:hover {
                border-color: rgba(255, 255, 255, 0.08);
                background: rgba(255, 255, 255, 0.03);
            }

            .detail-item .label {
                color: var(--text-3);
                font-size: 0.62rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 600;
            }

            .detail-item .value {
                font-size: 0.78rem;
                font-weight: 500;
                color: var(--text);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .detail-item .value.green { color: var(--success); }
            .detail-item .value.red { color: var(--danger); }
            .detail-item .value.orange { color: var(--warning); }

            /* ===== GATEWAY ===== */
            .gateway-box {
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.6));
                border: 1px solid var(--card-border);
                border-radius: var(--radius-lg);
                padding: 16px;
                margin-bottom: 14px;
                box-shadow: var(--shadow-card);
            }

            .gateway-box h3 {
                font-size: 0.95rem;
                font-weight: 600;
                color: var(--text);
                line-height: 1.4;
                margin-bottom: 10px;
            }

            .gateway-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .gateway-meta .meta-chip {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding: 5px 10px;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.05);
                color: var(--text-2);
                font-size: 0.7rem;
                font-weight: 500;
            }

            /* ===== SECTION TITLE ===== */
            .section-title {
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.07em;
                color: var(--text-3);
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .section-title::after {
                content: '';
                flex: 1;
                height: 1px;
                background: linear-gradient(90deg, rgba(255, 255, 255, 0.06), transparent);
            }

            /* ===== TIMELINE ===== */
            .timeline {
                display: flex;
                flex-direction: column;
                gap: 0;
                margin-bottom: 16px;
            }

            .timeline-item {
                display: flex;
                gap: 12px;
                padding: 8px 0;
                position: relative;
            }

            .timeline-item::before {
                content: '';
                position: absolute;
                left: 5px;
                top: 28px;
                bottom: -6px;
                width: 1px;
                background: linear-gradient(180deg, rgba(124, 92, 255, 0.3), rgba(255, 255, 255, 0.06));
            }

            .timeline-item:last-child::before { display: none; }

            .timeline-dot {
                width: 11px;
                height: 11px;
                border-radius: 50%;
                background: var(--surface);
                border: 2px solid var(--primary);
                flex-shrink: 0;
                margin-top: 5px;
                z-index: 1;
                box-shadow: 0 0 8px rgba(124, 92, 255, 0.4);
            }

            .timeline-dot.green { border-color: var(--success); box-shadow: 0 0 8px rgba(52, 211, 153, 0.4); }
            .timeline-dot.orange { border-color: var(--warning); box-shadow: 0 0 8px rgba(251, 191, 36, 0.4); }
            .timeline-dot.red { border-color: var(--danger); box-shadow: 0 0 8px rgba(255, 93, 115, 0.4); }

            .timeline-content { flex: 1; min-width: 0; }

            .timeline-content strong {
                display: block;
                font-size: 0.8rem;
                font-weight: 600;
                color: var(--text);
            }

            .timeline-content p {
                color: var(--text-3);
                font-size: 0.74rem;
                margin-top: 2px;
            }

            .timeline-time {
                color: var(--text-3);
                font-size: 0.68rem;
                white-space: nowrap;
                flex-shrink: 0;
                padding-top: 5px;
            }

            /* ===== CHAT ===== */
            .chat-section { margin-top: 4px; }

            .chat-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 12px 0;
                color: var(--text-3);
                font-size: 0.75rem;
                border-bottom: 1px solid var(--card-border);
                margin-bottom: 14px;
            }

            .chat-topbar .channel-tag {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                color: var(--text-2);
                font-weight: 600;
                font-size: 0.78rem;
            }

            .channel-tag.email { color: var(--blue); }
            .channel-tag.live-chat { color: var(--cyan); }
            .channel-tag.whatsapp { color: var(--success); }
            .channel-tag.web-form { color: var(--primary); }
            .channel-tag.portal { color: var(--warning); }

            .messages {
                display: flex;
                flex-direction: column;
                gap: 10px;
                padding: 4px 0 12px;
            }

            .message {
                display: flex;
                gap: 10px;
                align-items: flex-start;
                animation: msg-in 0.3s var(--spring) both;
            }

            .message.staff { flex-direction: row-reverse; }

            @keyframes msg-in {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .message .msg-avatar {
                width: 30px;
                height: 30px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                background: rgba(255, 255, 255, 0.05);
                color: var(--text-2);
                font-size: 0.62rem;
                font-weight: 700;
                flex-shrink: 0;
            }

            .message.staff .msg-avatar {
                background: var(--primary-soft);
                color: var(--primary);
            }

            .bubble {
                max-width: 80%;
                padding: 10px 14px;
                border-radius: 16px;
                font-size: 0.82rem;
                line-height: 1.5;
                position: relative;
            }

            .message.customer .bubble {
                background: linear-gradient(180deg, var(--surface), rgba(20, 24, 36, 0.8));
                border: 1px solid var(--card-border);
                border-top-left-radius: 4px;
                color: var(--text);
                box-shadow: var(--shadow-card);
            }

            .message.staff .bubble {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.18), rgba(124, 92, 255, 0.08));
                border: 1px solid var(--primary-border);
                border-top-right-radius: 4px;
                color: #fff;
                box-shadow: 0 4px 16px rgba(124, 92, 255, 0.1);
            }

            .message.internal .bubble {
                background: linear-gradient(135deg, rgba(251, 191, 36, 0.12), rgba(251, 191, 36, 0.05));
                border: 1px solid rgba(251, 191, 36, 0.15);
                border-top-right-radius: 4px;
                color: var(--warning);
                font-style: italic;
            }

            .bubble strong {
                display: block;
                font-size: 0.72rem;
                font-weight: 600;
                margin-bottom: 4px;
                opacity: 0.85;
            }

            .bubble p { margin: 0; }

            .bubble .msg-meta {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 6px;
                font-size: 0.64rem;
                opacity: 0.6;
            }

            .bubble .msg-meta .delivered { color: var(--success); }

            .typing-indicator {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                padding: 12px 16px;
                background: var(--surface);
                border: 1px solid var(--card-border);
                border-radius: 16px;
                border-top-left-radius: 4px;
                box-shadow: var(--shadow-card);
            }

            .typing-indicator span {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: var(--text-3);
                animation: typing 1.2s infinite;
            }

            .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
            .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

            @keyframes typing {
                0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
                30% { transform: translateY(-4px); opacity: 1; }
            }

            /* ===== REPLY AREA ===== */
            .composer-wrap {
                padding: 16px 20px;
                border-top: 1px solid var(--card-border);
                background: linear-gradient(0deg, rgba(26, 31, 45, 0.4), transparent);
            }

            .reply-actions {
                display: flex;
                gap: 8px;
                margin-bottom: 10px;
            }

            .action-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 8px 14px;
                border-radius: 10px;
                background: var(--card);
                border: 1px solid var(--card-border);
                color: var(--text-2);
                font-size: 0.75rem;
                font-weight: 600;
                box-shadow: var(--shadow-card);
                transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition);
            }

            .action-btn:hover {
                background: var(--card-hover);
                color: var(--text);
                border-color: var(--card-border-hover);
                transform: translateY(-1px);
            }

            .action-btn.primary {
                background: linear-gradient(135deg, rgba(124, 92, 255, 0.2), rgba(124, 92, 255, 0.1));
                border-color: var(--primary-border);
                color: #fff;
            }

            .composer-box {
                display: flex;
                align-items: flex-end;
                gap: 8px;
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.7));
                border: 1px solid var(--card-border);
                border-radius: 18px;
                padding: 12px 14px;
                box-shadow: var(--shadow-card);
                transition: border-color var(--transition), box-shadow var(--transition);
            }

            .composer-box:focus-within {
                border-color: var(--primary-border);
                box-shadow:
                    0 0 0 4px rgba(124, 92, 255, 0.08),
                    0 12px 30px rgba(0, 0, 0, 0.35);
            }

            .composer-box textarea {
                flex: 1;
                background: transparent;
                border: none;
                outline: none;
                color: var(--text);
                font-size: 0.82rem;
                resize: none;
                min-height: 24px;
                max-height: 100px;
                line-height: 1.5;
            }

            .composer-box textarea::placeholder { color: var(--text-3); }

            .composer-tools {
                display: flex;
                align-items: center;
                gap: 2px;
                flex-shrink: 0;
            }

            .composer-tool {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                color: var(--text-3);
                transition: background var(--transition), color var(--transition), transform var(--transition);
            }

            .composer-tool:hover {
                background: rgba(255, 255, 255, 0.06);
                color: var(--text);
                transform: scale(1.1);
            }

            .send-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 18px;
                border-radius: 12px;
                background: linear-gradient(135deg, #8B7CFF, #7C5CFF, #6D4FFF);
                color: #fff;
                font-weight: 600;
                font-size: 0.78rem;
                box-shadow: var(--shadow-btn);
                transition: transform var(--transition), box-shadow var(--transition);
            }

            .send-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-btn-hover);
            }

            .send-btn:active { transform: translateY(0) scale(0.96); }

            .composer-hint {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-top: 8px;
                color: var(--text-3);
                font-size: 0.68rem;
            }

            /* ===== SKELETON ===== */
            .skeleton-card {
                background: linear-gradient(180deg, var(--card), rgba(26, 31, 45, 0.6));
                border: 1px solid var(--card-border);
                border-radius: var(--radius-lg);
                padding: 18px;
                margin-bottom: 10px;
                box-shadow: var(--shadow-card);
            }

            .skeleton-line {
                height: 12px;
                border-radius: 6px;
                background: linear-gradient(90deg, rgba(255, 255, 255, 0.04) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.04) 75%);
                background-size: 200% 100%;
                animation: shimmer 1.5s ease-in-out infinite;
                margin-bottom: 8px;
            }

            .skeleton-line.short { width: 40%; }
            .skeleton-line.tiny { width: 25%; height: 8px; }

            @keyframes shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }

            /* ===== TOAST ===== */
            .toast-container {
                position: fixed;
                top: 24px;
                right: 24px;
                z-index: 1000;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .toast {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 14px 18px;
                border-radius: 14px;
                background: linear-gradient(180deg, var(--card), var(--surface));
                border: 1px solid var(--card-border);
                box-shadow: var(--shadow-hover);
                color: var(--text);
                font-size: 0.82rem;
                font-weight: 500;
                animation: toast-in 0.3s var(--spring);
            }

            .toast.success { border-left: 3px solid var(--success); box-shadow: 0 12px 30px rgba(52, 211, 153, 0.1); }
            .toast.error { border-left: 3px solid var(--danger); }
            .toast.info { border-left: 3px solid var(--primary); }

            @keyframes toast-in {
                from { opacity: 0; transform: translateX(24px) scale(0.96); }
                to { opacity: 1; transform: translateX(0) scale(1); }
            }

            /* ===== DRAWER ===== */
            .drawer-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 50;
                opacity: 0;
                pointer-events: none;
                transition: opacity var(--transition);
            }

            .drawer-overlay.open {
                opacity: 1;
                pointer-events: auto;
            }

            /* ===== RESPONSIVE ===== */
            @media (max-width: 1280px) {
                .stats-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .detail-panel {
                    min-width: 320px;
                }
            }

            @media (max-width: 1024px) {
                .dashboard-shell {
                    padding: 12px;
                    gap: 12px;
                }

                .sidebar {
                    position: fixed;
                    left: 12px;
                    top: 12px;
                    transform: translateX(calc(-100% - 12px));
                    width: 260px;
                    min-width: 260px;
                    z-index: 60;
                    height: calc(100vh - 24px);
                }

                .sidebar.open {
                    transform: translateX(0);
                }

                .sidebar.collapsed {
                    width: 260px;
                    min-width: 260px;
                }

                .sidebar.collapsed .brand-text,
                .sidebar.collapsed .brand-subtitle,
                .sidebar.collapsed .nav-label,
                .sidebar.collapsed .nav-badge,
                .sidebar.collapsed .user-meta,
                .sidebar.collapsed .sidebar-footer-text {
                    display: block;
                }

                .sidebar.collapsed .nav-item {
                    justify-content: flex-start;
                    padding: 11px 12px;
                }

                .sidebar.collapsed .user-card {
                    justify-content: flex-start;
                    padding: 12px;
                }

                .mobile-menu-btn {
                    display: grid;
                }

                .detail-panel {
                    position: fixed;
                    right: 0;
                    top: 0;
                    bottom: 0;
                    width: 100%;
                    max-width: 100%;
                    min-width: 0;
                    z-index: 55;
                    transform: translateX(100%);
                    transition: transform var(--spring);
                    border-radius: 24px 0 0 24px;
                    box-shadow: -20px 0 60px rgba(0, 0, 0, 0.6);
                }

                .detail-panel.open {
                    transform: translateX(0);
                }
            }

            @media (max-width: 768px) {
                .dashboard-shell {
                    padding: 10px;
                    height: auto;
                    overflow: visible;
                }

                .main-panel {
                    height: auto;
                    overflow: visible;
                }

                .queue-panel {
                    height: auto;
                    overflow: visible;
                }

                .stats-grid {
                    gap: 10px;
                }

                .header-actions .search-shortcut {
                    display: none;
                }

                .header-title h1 { font-size: 1.2rem; }

                .ticket-item {
                    padding: 14px;
                    gap: 10px;
                }

                .detail-panel {
                    position: fixed;
                    right: 0;
                    top: 0;
                    bottom: 0;
                    width: 100%;
                    z-index: 55;
                    transform: translateX(100%);
                    transition: transform var(--spring);
                    border-radius: 0;
                }

                .detail-panel.open {
                    transform: translateX(0);
                }
            }

            @media (max-width: 480px) {
                .stats-grid {
                    grid-template-columns: 1fr 1fr;
                    gap: 8px;
                }

                .stat-card { padding: 16px; }
                .stat-value { font-size: 1.7rem; }

                .header-actions { gap: 6px; }

                .new-ticket-btn {
                    padding: 9px 14px;
                    font-size: 0.76rem;
                }

                .toolbar-row {
                    flex-direction: column;
                    align-items: stretch;
                }

                .filter-btn {
                    justify-content: center;
                }

                .customer-details {
                    grid-template-columns: 1fr;
                }

                .ticket-subtitle {
                    flex-wrap: wrap;
                }
            }

            /* ===== REDUCED MOTION ===== */
            @media (prefers-reduced-motion: reduce) {
                * {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
    </head>
    <body>
        @include('partials.status-banner')
        <div class="dashboard-shell">
            <!-- ===== FLOATING SIDEBAR ===== -->
            <aside class="sidebar" id="sidebar" aria-label="Sidebar navigation">
                <div class="brand-wrap">
                    <div class="brand-icon">◫</div>
                    <div>
                        <div class="brand-text">AyyNet</div>
                        <div class="brand-subtitle">ISP Support Desk</div>
                    </div>
                    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                        <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <nav class="side-nav">
                    <a href="{{ route('dashboard') }}" class="nav-item active" aria-current="page">
                        <span class="nav-icon">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h4A1.5 1.5 0 0 1 10 4.5v4A1.5 1.5 0 0 1 8.5 10h-4A1.5 1.5 0 0 1 3 8.5v-4Zm0 7A1.5 1.5 0 0 1 4.5 10h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 8.5 17h-4A1.5 1.5 0 0 1 3 15.5v-4Zm7-7A1.5 1.5 0 0 1 11.5 3h4A1.5 1.5 0 0 1 17 4.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 10 8.5v-4Zm0 7a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z"/></svg>
                        </span>
                        <span class="nav-label">Queue</span>
                        <span class="nav-badge">{{ $tickets->count() }}</span>
                    </a>
                    <a href="{{ route('my.tickets') }}" class="nav-item">
                        <span class="nav-icon">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1 3h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>
                        </span>
                        <span class="nav-label">My Tickets</span>
                        <span class="nav-badge muted">{{ $tickets->where('created_by', auth()->id() ?? 1)->count() }}</span>
                    </a>
                    <a href="{{ route('tickets.index') }}" class="nav-item">
                        <span class="nav-icon">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h11A1.5 1.5 0 0 1 17 4.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 15.5v-11Zm1.5.5v10h11V5h-11Zm2 2h7v1h-7V7Zm0 3h7v1h-7v-1Zm0 3h4v1h-4v-1Z"/></svg>
                        </span>
                        <span class="nav-label">All Tickets</span>
                        <span class="nav-badge muted">{{ $tickets->count() }}</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="nav-item">
                        <span class="nav-icon">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 3v14h14v-1.5H4.5V3H3Zm3 3h2v8H6V6Zm4 0h2v5h-2V6Zm4 0h2v3h-2V6Z"/></svg>
                        </span>
                        <span class="nav-label">Reports</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="nav-item">
                        <span class="nav-icon">
                            <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2.5a7.5 7.5 0 0 1 7.5 7.5 7.5 7.5 0 0 1-7.5 7.5A7.5 7.5 0 0 1 2.5 10 7.5 7.5 0 0 1 10 2.5Zm0 1.5a6 6 0 1 0 0 12 6 6 0 0 0 0-12Zm0 2a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm0 1.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z"/></svg>
                        </span>
                        <span class="nav-label">Settings</span>
                    </a>
                    @if(auth()->user()->hasRole('admin', 'cs'))
                        <a href="{{ route('status-banners.index') }}" class="nav-item">
                            <span class="nav-icon">
                                <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 8v4h3l5 4V4L6 8H3Zm11 -1a4 4 0 0 1 0 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <span class="nav-label">Status Banner</span>
                        </a>
                    @endif
                </nav>

                <!-- Floating profile card -->
                <div class="user-card">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'PS', 0, 2)) }}
                        <span class="online-dot"></span>
                    </div>
                    <div class="user-meta">
                        <strong>{{ auth()->user()->name ?? 'Priya Sharma' }}</strong>
                        <span>Admin · Online</span>
                    </div>
                </div>

                <div class="sidebar-footer">
                    <span class="sidebar-footer-text">AyyNet v2.1 · Enterprise</span>
                </div>
            </aside>

            <!-- Mobile drawer overlay -->
            <div class="drawer-overlay" id="drawer-overlay" onclick="closeSidebar()" aria-hidden="true"></div>

            <!-- ===== MAIN PANEL ===== -->
            <main class="main-panel">
                <!-- ===== QUEUE PANEL ===== -->
                <section class="queue-panel" aria-label="Ticket queue">
                    <!-- Header -->
                    <div class="queue-header">
                        <div class="header-left">
                            <button class="mobile-menu-btn" onclick="openSidebar()" aria-label="Open menu">
                                <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 5h14M3 10h14M3 15h14" stroke-linecap="round"/></svg>
                            </button>
                            <div class="header-title">
                                <h1>Ticket Queue</h1>
                                <p>{{ now()->translatedFormat('l, j F Y') }} · {{ now()->format('H:i') }} WIB</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button class="search-shortcut" onclick="focusSearch()" aria-label="Search tickets">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8.5 3a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Zm0 1.5a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm6.1 8.4 3.5 3.5-1.1 1.1-3.5-3.5 1.1-1.1Z"/></svg>
                                Search
                                <span class="kbd">Ctrl K</span>
                            </button>
                            <x-notification-bell />
                            <a href="{{ route('tickets.create') }}" class="new-ticket-btn">
                                <span class="btn-glow" aria-hidden="true"></span>
                                <span class="btn-icon">
                                    <svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M10 4v12M4 10h12" stroke-linecap="round"/></svg>
                                </span>
                                New Ticket
                            </a>
                        </div>
                    </div>

                    <!-- KPI Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card open">
                            <div class="stat-header">
                                <span class="stat-label">Open</span>
                                <span class="stat-icon">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1 3h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>
                                </span>
                            </div>
                            <div class="stat-value" data-counter="{{ $summary['open'] }}">0</div>
                            <div class="stat-footer">
                                <span class="stat-trend up">↑ 12%</span>
                                <span class="stat-sub">needs attention</span>
                            </div>
                            <div class="stat-progress">
                                <div class="stat-progress-fill" style="width: 68%;"></div>
                            </div>
                        </div>
                        <div class="stat-card progress">
                            <div class="stat-header">
                                <span class="stat-label">In Progress</span>
                                <span class="stat-icon">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1 3h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>
                                </span>
                            </div>
                            <div class="stat-value" data-counter="{{ $summary['in_progress'] }}">0</div>
                            <div class="stat-footer">
                                <span class="stat-trend up">↑ 8%</span>
                                <span class="stat-sub">being handled</span>
                            </div>
                            <div class="stat-progress">
                                <div class="stat-progress-fill" style="width: 52%;"></div>
                            </div>
                        </div>
                        <div class="stat-card critical">
                            <div class="stat-header">
                                <span class="stat-label">Critical</span>
                                <span class="stat-icon">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1 3h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>
                                </span>
                            </div>
                            <div class="stat-value" data-counter="{{ $summary['critical'] }}">0</div>
                            <div class="stat-footer">
                                <span class="stat-trend down">↓ 3%</span>
                                <span class="stat-sub"><2h remaining</span>
                            </div>
                            <div class="stat-progress">
                                <div class="stat-progress-fill" style="width: 35%;"></div>
                            </div>
                        </div>
                        <div class="stat-card resolved">
                            <div class="stat-header">
                                <span class="stat-label">Resolved</span>
                                <span class="stat-icon">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1.2 8.6-2.3-2.3-1.1 1.1 3.4 3.4 5.5-5.5-1.1-1.1-4.4 4.4Z"/></svg>
                                </span>
                            </div>
                            <div class="stat-value" data-counter="{{ $summary['resolved'] }}">0</div>
                            <div class="stat-footer">
                                <span class="stat-trend up">↑ 15%</span>
                                <span class="stat-sub">total resolved</span>
                            </div>
                            <div class="stat-progress">
                                <div class="stat-progress-fill" style="width: 85%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Search & Filter -->
                    <div class="toolbar-row">
                        <div class="search-box" id="search-box">
                            <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M8.5 3a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11Zm0 1.5a4 4 0 1 1 0 8 4 4 0 0 1 0-8Zm6.1 8.4 3.5 3.5-1.1 1.1-3.5-3.5 1.1-1.1Z"/></svg>
                            <input type="text" id="search-input" placeholder="Cari ticket, pelanggan, ID..." aria-label="Search tickets" />
                            <button class="search-clear" onclick="clearSearch()" aria-label="Clear search" style="display:none;">✕</button>
                        </div>
                        <button class="filter-btn" onclick="toggleFilters()" aria-label="Toggle filters">
                            <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 5h14M6 10h8M9 15h2" stroke-linecap="round"/></svg>
                            Filter
                            <span class="filter-count" id="filter-count" style="display:none;">0</span>
                        </button>
                    </div>

                    <!-- Quick Filters -->
                    <div class="quick-filters" id="quick-filters" style="display:none;">
                        <button class="quick-chip active" data-filter="all"><span class="chip-dot blue"></span> All</button>
                        <button class="quick-chip" data-filter="open"><span class="chip-dot red"></span> Open</button>
                        <button class="quick-chip" data-filter="pending"><span class="chip-dot orange"></span> Pending</button>
                        <button class="quick-chip" data-filter="resolved"><span class="chip-dot green"></span> Resolved</button>
                        <button class="quick-chip" data-filter="critical"><span class="chip-dot red"></span> Critical</button>
                        <button class="quick-chip" data-filter="today"><span class="chip-dot blue"></span> Today</button>
                        <button class="quick-chip" data-filter="high"><span class="chip-dot orange"></span> High Priority</button>
                    </div>

                    <!-- Channel Filter -->
                    <div class="channel-row">
                        @foreach ($channelOptions as $option)
                            <a href="{{ $option['key'] === 'All' ? route('dashboard') : route('dashboard', ['channel' => $option['key']]) }}"
                               class="chip-chip {{ $selectedChannel === $option['key'] ? 'selected' : '' }}">
                                {!! $option['icon'] !!}
                                {{ $option['label'] }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Ticket List -->
                    <div class="ticket-list" id="ticket-list">
                        @forelse($tickets as $ticket)
                            @php
                                $ticketChannel = $channelMeta($ticket);
                                $priorityClass = strtolower($ticket->priority ?? 'medium');
                                $slaClass = 'ok';
                                $slaText = 'SLA OK';
                                if ($ticket->sla_deadline) {
                                    if ($ticket->sla_deadline->isPast()) {
                                        $slaClass = 'breached';
                                        $slaText = 'SLA Breached';
                                    } elseif ($ticket->sla_deadline->diffInHours(now()) < 2) {
                                        $slaClass = 'near';
                                        $slaText = 'SLA ' . $ticket->sla_deadline->diffForHumans();
                                    } else {
                                        $slaText = 'SLA ' . $ticket->sla_deadline->diffForHumans();
                                    }
                                }
                            @endphp
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="ticket-item priority-{{ $priorityClass }}" data-status="{{ strtolower($ticket->status) }}" data-priority="{{ strtolower($ticket->priority ?? 'medium') }}" data-created="{{ $ticket->created_at?->format('Y-m-d') }}">
                                <div class="ticket-channel {{ $ticketChannel['class'] }}">
                                    {!! $ticketChannel['icon'] !!}
                                </div>
                                <div class="ticket-main">
                                    <div class="ticket-title-row">
                                        <span class="ticket-title">{{ \Illuminate\Support\Str::limit($ticket->title, 40) }}</span>
                                        @if($ticket->messages->isNotEmpty())
                                            <span class="unread-dot" aria-label="Unread messages"></span>
                                        @endif
                                    </div>
                                    <div class="ticket-subtitle">
                                        <span class="customer-chip">
                                            <span class="mini-avatar">{{ strtoupper(substr($ticket->customer->name ?? 'U', 0, 2)) }}</span>
                                            {{ $ticket->customer->name ?? 'Unknown' }}
                                        </span>
                                        <span class="sep">·</span>
                                        <span>{{ $ticket->customer->package ?? ($ticket->customer->phone ?? '—') }}</span>
                                        <span class="sep">·</span>
                                        <span>{{ $ticket->ticket_number ?? 'TKT-' . str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                </div>
                                <div class="ticket-right">
                                    <span class="ticket-badge {{ strtolower(str_replace(' ', '-', $ticket->status)) }}">
                                        {{ $ticket->status }}
                                    </span>
                                    <div class="ticket-meta">
                                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                                        @if($ticket->sla_deadline)
                                            <span class="sla-countdown {{ $slaClass }}">{{ $slaText }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg viewBox="0 0 20 20" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h11A1.5 1.5 0 0 1 17 4.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 15.5v-11Zm1.5.5v10h11V5h-11Zm2 2h7v1h-7V7Zm0 3h7v1h-7v-1Zm0 3h4v1h-4v-1Z"/></svg>
                                </div>
                                <div class="empty-title">Belum ada ticket</div>
                                <div class="empty-subtitle">Buat tiket baru untuk mulai menerima request pelanggan.</div>
                                <a href="{{ route('tickets.create') }}" class="new-ticket-btn" style="margin-top: 8px;">+ New Ticket</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Skeleton loading -->
                    <div id="skeleton-loading" style="display:none;">
                        @for($i = 0; $i < 4; $i++)
                            <div class="skeleton-card">
                                <div class="skeleton-line short"></div>
                                <div class="skeleton-line"></div>
                                <div class="skeleton-line tiny"></div>
                            </div>
                        @endfor
                    </div>
                </section>

                <!-- ===== DETAIL PANEL ===== -->
                <aside class="detail-panel" id="detail-panel" aria-label="Ticket detail">
                    <div class="detail-header">
                        <div class="detail-header-row">
                            <div class="detail-title">{{ $selected?->ticket_number ?? 'TKT-0000' }}</div>
                            <div class="detail-actions">
                                <button class="mini-btn primary" onclick="openDetail()" aria-label="Open full detail">
                                    <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h11A1.5 1.5 0 0 1 17 4.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 15.5v-11Zm1.5.5v10h11V5h-11Zm2 2h7v1h-7V7Zm0 3h7v1h-7v-1Zm0 3h4v1h-4v-1Z"/></svg>
                                    Detail
                                </button>
                                <button class="mini-btn" onclick="closeDetail()" aria-label="Close detail" style="display:none;" id="detail-close-btn">
                                    <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="detail-status-row">
                            <span class="tag {{ strtolower($selected?->status ?? 'open') }}">{{ $selected?->status ?? 'Open' }}</span>
                            <span class="tag priority-{{ strtolower($selected?->priority ?? 'medium') }}">{{ $selected?->priority ?? 'Medium' }}</span>
                            <span class="tag {{ $detailChannel['class'] }}">{!! $detailChannel['icon'] !!} {{ $detailChannel['label'] }}</span>
                        </div>
                    </div>

                    <div class="detail-body">
                        <!-- Customer Internet Card -->
                        <div class="customer-card">
                            <div class="customer-card-header">
                                <div class="customer-avatar">{{ strtoupper(substr($selected?->customer->name ?? 'MO', 0, 2)) }}</div>
                                <div class="customer-info">
                                    <div class="customer-name">{{ $selected?->customer->name ?? 'Mara Okonkwo' }}</div>
                                    <div class="customer-id">CUST-{{ $selected?->customer->customer_id ?? '0001' }}</div>
                                </div>
                                <span class="connection-status online">
                                    <span class="status-dot"></span> Online
                                </span>
                            </div>
                            <div class="customer-details">
                                <div class="detail-item">
                                    <span class="label">Service ID</span>
                                    <span class="value">SV-{{ $selected?->customer->id ?? '0001' }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Package Speed</span>
                                    <span class="value">{{ $selected?->customer->package ?? '30 Mbps' }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Current Bandwidth</span>
                                    <span class="value green">28.4 Mbps ↓</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Router Status</span>
                                    <span class="value green">Online</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">OLT</span>
                                    <span class="value">OLT-01 / PON 2</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">ONU Serial</span>
                                    <span class="value">HWTC-8F2A1B</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Signal RX</span>
                                    <span class="value green">-18.5 dBm</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Signal TX</span>
                                    <span class="value green">2.1 dBm</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Network Area</span>
                                    <span class="value">Area 3 · POP 2</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Technician</span>
                                    <span class="value">Andi S.</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Installation</span>
                                    <span class="value">12 Mar 2024</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Last Outage</span>
                                    <span class="value orange">2 hari lalu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Summary -->
                        <div class="gateway-box">
                            <h3>{{ $selected?->title ?? 'Payment gateway returning 500 errors on checkout' }}</h3>
                            <div class="gateway-meta">
                                <span class="meta-chip">{{ $selected?->created_at?->format('d M Y, H:i') ?? now()->format('d M Y, H:i') }}</span>
                                <span class="meta-chip">Priority SLA: 5h</span>
                                <span class="meta-chip">Escalation: L2</span>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="section-title">Activity Timeline</div>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-dot green"></div>
                                <div class="timeline-content">
                                    <strong>Status changed to Checking</strong>
                                    <p>Andi S. mulai menangani tiket</p>
                                </div>
                                <span class="timeline-time">10:24</span>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot orange"></div>
                                <div class="timeline-content">
                                    <strong>Escalated to L2</strong>
                                    <p>Diteruskan ke tim jaringan</p>
                                </div>
                                <span class="timeline-time">09:45</span>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <strong>Ticket created</strong>
                                    <p>Dibuat oleh customer via WhatsApp</p>
                                </div>
                                <span class="timeline-time">09:12</span>
                            </div>
                        </div>

                        <!-- Chat -->
                        <div class="chat-section">
                            <div class="chat-topbar">
                                <span class="channel-tag {{ $detailChannel['class'] }}">
                                    {!! $detailChannel['icon'] !!}
                                    Masuk melalui {{ $detailChannel['label'] }}
                                </span>
                                <span>{{ $selected?->created_at?->format('H:i') ?? '09:12' }}</span>
                            </div>

                            <div class="messages">
                                <div class="message customer">
                                    <div class="msg-avatar">MO</div>
                                    <div class="bubble">
                                        <strong>{{ $selected?->customer->name ?? 'Mara Okonkwo' }}</strong>
                                        <p>
                                            {{ $selected?->description ?? 'We started seeing 500 errors from the Stripe integration around 9 AM. All checkout flows are broken — approximately 40% of transactions failing.' }}
                                        </p>
                                        <div class="msg-meta">
                                            <span>09:12</span>
                                            <span class="delivered">✓✓</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="message staff">
                                    <div class="msg-avatar">AS</div>
                                    <div class="bubble">
                                        <strong>Andi S. · Staff</strong>
                                        <p>Baik, saya cek dulu status ONU dan signal level-nya. Mohon tunggu sebentar.</p>
                                        <div class="msg-meta">
                                            <span>09:20</span>
                                            <span class="delivered">✓✓</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="message internal">
                                    <div class="msg-avatar">AS</div>
                                    <div class="bubble">
                                        <strong>Internal Note</strong>
                                        <p>Signal RX -18.5 dBm normal. Kemungkinan masalah di sisi router customer. Perlu remote check.</p>
                                        <div class="msg-meta">
                                            <span>09:35</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="message customer">
                                    <div class="msg-avatar">MO</div>
                                    <div class="bubble">
                                        <strong>{{ $selected?->customer->name ?? 'Mara Okonkwo' }}</strong>
                                        <p>Sudah dicoba restart router tapi masih error. Mohon bantuannya.</p>
                                        <div class="msg-meta">
                                            <span>09:40</span>
                                            <span class="delivered">✓✓</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="typing-indicator" aria-label="Staff is typing">
                                    <span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Area -->
                    <div class="composer-wrap">
                        <div class="reply-actions">
                            <button class="action-btn primary">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10l14-7-4 14-3-5-7-2Z" stroke-linejoin="round"/></svg>
                                Reply
                            </button>
                            <button class="action-btn">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 3h12v14H4V3Zm2 2v10h8V5H6Zm2 2h4v1H8V7Zm0 3h4v1H8v-1Zm0 3h2v1H8v-1Z"/></svg>
                                Internal Note
                            </button>
                        </div>
                        <div class="composer-box">
                            <textarea id="reply-input" placeholder="Tulis balasan..." rows="1" aria-label="Reply message"></textarea>
                            <div class="composer-tools">
                                <button class="composer-tool" aria-label="Add emoji">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13ZM7 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm6 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm-6.5 2.5a4.5 4.5 0 0 0 7 0l-1.2-.8a3 3 0 0 1-4.6 0l-1.2.8Z"/></svg>
                                </button>
                                <button class="composer-tool" aria-label="Attach file">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2a8 8 0 1 0 0 16 8 8 0 0 0 0-16Zm0 1.5a6.5 6.5 0 1 1 0 13 6.5 6.5 0 0 1 0-13Zm-1 3h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>
                                </button>
                                <button class="composer-tool" aria-label="Quick reply">
                                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h11A1.5 1.5 0 0 1 17 4.5v11a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 15.5v-11Zm1.5.5v10h11V5h-11Zm2 2h7v1h-7V7Zm0 3h7v1h-7v-1Zm0 3h4v1h-4v-1Z"/></svg>
                                </button>
                            </div>
                            <button class="send-btn" onclick="sendReply()" aria-label="Send reply">
                                <svg viewBox="0 0 20 20" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10l14-7-4 14-3-5-7-2Z" stroke-linejoin="round"/></svg>
                                Send
                            </button>
                        </div>
                        <div class="composer-hint">
                            <span class="kbd">Enter</span> to send · <span class="kbd">Shift+Enter</span> for new line
                        </div>
                    </div>
                </aside>
            </main>
        </div>

        <!-- Toast container -->
        <div class="toast-container" id="toast-container" aria-live="polite"></div>

        <script>
            // ===== SIDEBAR =====
            function toggleSidebar() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('collapsed');
            }

            function openSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('drawer-overlay');
                sidebar.classList.add('open');
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('drawer-overlay');
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }

            // ===== DETAIL PANEL (mobile) =====
            function openDetail() {
                const panel = document.getElementById('detail-panel');
                panel.classList.add('open');
                document.getElementById('detail-close-btn').style.display = 'inline-flex';
            }

            function closeDetail() {
                const panel = document.getElementById('detail-panel');
                panel.classList.remove('open');
                document.getElementById('detail-close-btn').style.display = 'none';
            }

            // ===== SEARCH =====
            function focusSearch() {
                document.getElementById('search-input').focus();
            }

            function clearSearch() {
                const input = document.getElementById('search-input');
                input.value = '';
                input.focus();
                document.querySelector('.search-clear').style.display = 'none';
                filterTickets();
            }

            document.getElementById('search-input')?.addEventListener('input', function () {
                document.querySelector('.search-clear').style.display = this.value ? 'grid' : 'none';
                filterTickets();
            });

            // ===== FILTERS =====
            function toggleFilters() {
                const filters = document.getElementById('quick-filters');
                filters.style.display = filters.style.display === 'none' ? 'flex' : 'none';
            }

            document.querySelectorAll('.quick-chip').forEach(chip => {
                chip.addEventListener('click', function () {
                    document.querySelectorAll('.quick-chip').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    filterTickets();
                });
            });

            function filterTickets() {
                const search = (document.getElementById('search-input')?.value || '').toLowerCase();
                const activeFilter = document.querySelector('.quick-chip.active')?.dataset.filter || 'all';
                const tickets = document.querySelectorAll('.ticket-item');
                let count = 0;

                tickets.forEach(ticket => {
                    const text = ticket.textContent.toLowerCase();
                    const status = ticket.dataset.status;
                    const priority = ticket.dataset.priority;
                    const created = ticket.dataset.created;
                    const today = new Date().toISOString().split('T')[0];

                    let show = true;

                    if (search && !text.includes(search)) show = false;

                    if (show && activeFilter !== 'all') {
                        switch (activeFilter) {
                            case 'open': show = status === 'open'; break;
                            case 'pending': show = ['checking', 'waiting-customer'].includes(status); break;
                            case 'resolved': show = status === 'solved'; break;
                            case 'critical': show = priority === 'high'; break;
                            case 'today': show = created === today; break;
                            case 'high': show = priority === 'high'; break;
                        }
                    }

                    ticket.style.display = show ? '' : 'none';
                    if (show) count++;
                });

                const emptyState = document.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.style.display = count === 0 ? 'flex' : 'none';
                }
            }

            // ===== ANIMATED COUNTERS =====
            function animateCounters() {
                document.querySelectorAll('[data-counter]').forEach(el => {
                    const target = parseInt(el.dataset.counter, 10) || 0;
                    const duration = 800;
                    const start = performance.now();

                    function tick(now) {
                        const progress = Math.min((now - start) / duration, 1);
                        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
                        el.textContent = Math.round(target * eased);
                        if (progress < 1) {
                            requestAnimationFrame(tick);
                        }
                    }

                    requestAnimationFrame(tick);
                });
            }

            // ===== KEYBOARD SHORTCUTS =====
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    focusSearch();
                }
                if (e.key === 'Escape') {
                    closeSidebar();
                    closeDetail();
                }
            });

            // ===== AUTO-RESIZE TEXTAREA =====
            const replyInput = document.getElementById('reply-input');
            if (replyInput) {
                replyInput.addEventListener('input', function () {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });
            }

            // ===== SEND REPLY =====
            function sendReply() {
                const input = document.getElementById('reply-input');
                const text = input.value.trim();
                if (!text) return;

                showToast('Pesan terkirim', 'success');
                input.value = '';
                input.style.height = 'auto';

                const typing = document.querySelector('.typing-indicator');
                if (typing) typing.remove();
            }

            // ===== TOAST =====
            function showToast(message, type = 'info') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                toast.textContent = message;
                container.appendChild(toast);

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(24px) scale(0.96)';
                    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            // ===== SKELETON LOADING =====
            window.addEventListener('DOMContentLoaded', function () {
                const skeleton = document.getElementById('skeleton-loading');
                const ticketList = document.getElementById('ticket-list');

                if (ticketList && ticketList.children.length === 0) {
                    skeleton.style.display = 'block';
                    setTimeout(() => {
                        skeleton.style.display = 'none';
                    }, 800);
                }

                animateCounters();
            });

            // ===== LIVE CLOCK =====
            function updateClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                const el = document.querySelector('.header-title p');
                if (el) {
                    el.textContent = `${date} · ${time} WIB`;
                }
            }
            setInterval(updateClock, 30000);
        </script>
    </body>
</html>