@php
    // Channel metadata for badges/icons
    $channelMap = [
        'email' => ['label' => 'Email', 'class' => 'email', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 5.5A1.5 1.5 0 0 1 4 4h12a1.5 1.5 0 0 1 1.5 1.5v9A1.5 1.5 0 0 1 16 16H4a1.5 1.5 0 0 1-1.5-1.5v-9Zm1.2.8 6.3 4.5 6.3-4.5H3.7Zm12.8 8.2V6.7l-5.6 4-5.6-4v7.8h11.2Z"/></svg>'],
        'live chat' => ['label' => 'Live Chat', 'class' => 'live-chat', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v6A2.5 2.5 0 0 1 14.5 13H9l-4.5 3v-3H5.5A2.5 2.5 0 0 1 3 10.5v-6Zm2 1.5h10v1H5V6Zm0 3h7v1H5v-1Z"/></svg>'],
        'whatsapp' => ['label' => 'WhatsApp', 'class' => 'whatsapp', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 1.8A8.2 8.2 0 0 0 3.1 13.7L2 18l4.4-1.1A8.2 8.2 0 1 0 10 1.8Zm4.7 11.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1.3.2-4.1-1.2-3.5-1.7-5.8-6.1-6-6.4-.2-.4-.2-.9.1-1.3.1-.1.3-.2.5-.3l.5-.4c.2-.1.3-.1.5 0l.7.5c.2.2.4.5.5.8.1.2.3.5.1.6-.1.2-.2.3-.3.4-.2.2-.4.4-.6.6-.2.2-.1.4.1.6l.7.8c.3.3.7.5 1 .8.2.1.4.2.7.1.2-.1.7-.8.9-1.1.2-.3.4-.3.7-.2l.9.4c.2.1.4.3.4.5.1.2 0 .6-.2.8Z"/></svg>'],
        'web form' => ['label' => 'Web Form', 'class' => 'web-form', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A2.5 2.5 0 0 1 5.5 2h9A2.5 2.5 0 0 1 17 4.5v11A2.5 2.5 0 0 1 14.5 18h-9A2.5 2.5 0 0 1 3 15.5v-11Zm2.5-.5a.5.5 0 0 0-.5.5v1h10v-1a.5.5 0 0 0-.5-.5h-9Zm-.5 4v6h10v-6H5Zm2 1h4v1H7v-1Zm0 2h6v1H7v-1Z"/></svg>'],
        'portal' => ['label' => 'Portal', 'class' => 'portal', 'icon' => '<svg viewBox="0 0 20 20" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M10 2.3A7.7 7.7 0 1 1 2.3 10 7.7 7.7 0 0 1 10 2.3Zm0 1.5a6.2 6.2 0 1 0 6.2 6.2A6.2 6.2 0 0 0 10 3.8Zm-1 2.2h2v5.2H9V6Zm0 6.1h2v1.5H9v-1.5Z"/></svg>'],
    ];

    $rawChannel = strtolower('WhatsApp');
    $channel = $channelMap[$rawChannel] ?? $channelMap['email'];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket TKT-0004 — AyyNet ISP Support Desk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           PREMIUM ENTERPRISE DASHBOARD — AYYNET ISP
           Modal Detail View with Glassmorphism
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
            overflow: hidden;
        }

        button, input, select, textarea { font: inherit; color: inherit; }
        button { cursor: pointer; background: none; border: none; }
        a { text-decoration: none; color: inherit; }

        :focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: 6px;
        }

        /* ===== AMBIENT BACKGROUND (dimmed + blurred) ===== */
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

        /* ===== BLURRED BACKDROP OVERLAY ===== */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            /* Glassmorphism blur + dim */
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            background: rgba(11, 13, 18, 0.65);
        }

        /* ===== GLASSMORPHISM MODAL ===== */
        .modal-glass {
            position: relative;
            width: 100%;
            max-width: 1000px;
            max-height: calc(100vh - 48px);
            background: rgba(26, 31, 45, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-xl);
            box-shadow:
                0 40px 80px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.03),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: modal-in 0.4s var(--spring) both;
        }

        @keyframes modal-in {
            from { opacity: 0; transform: scale(0.92) translateY(12px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* ===== CLOSE BUTTON (X) ===== */
        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 10;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-2);
            transition: all var(--transition);
            backdrop-filter: blur(8px);
        }

        .modal-close:hover {
            background: rgba(255, 93, 115, 0.15);
            border-color: rgba(255, 93, 115, 0.3);
            color: var(--danger);
            transform: scale(1.08);
            box-shadow: 0 0 16px rgba(255, 93, 115, 0.3);
        }

        /* ===== MODAL HEADER ===== */
        .modal-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--card-border);
            background: linear-gradient(180deg, rgba(26, 31, 45, 0.5), transparent);
            position: relative;
            z-index: 2;
        }

        .modal-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .modal-ticket-id {
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

        .modal-status-row {
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

        .tag.whatsapp { background: var(--success-soft); color: var(--success); }
        .tag.email { background: var(--blue-soft); color: var(--blue); }
        .tag.live-chat { background: var(--cyan-soft); color: var(--cyan); }
        .tag.web-form { background: var(--primary-soft); color: var(--primary); }
        .tag.portal { background: var(--warning-soft); color: var(--warning); }

        /* ===== MODAL BODY ===== */
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.08) transparent;
        }

        .modal-body::-webkit-scrollbar { width: 4px; }
        .modal-body::-webkit-scrollbar-thumb {
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
            backdrop-filter: blur(10px);
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

        .connection-status.online {
            background: var(--success-soft);
            color: var(--success);
            box-shadow: 0 4px 12px rgba(52, 211, 153, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .connection-status .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 6px rgba(52, 211, 153, 0.6);
        }

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
            padding: 16px 24px;
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

        .kbd {
            padding: 2px 6px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.66rem;
            font-weight: 600;
            color: var(--text-2);
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

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .modal-backdrop {
                padding: 12px;
            }

            .modal-glass {
                max-width: 100%;
                max-height: calc(100vh - 24px);
                border-radius: var(--radius-lg);
            }

            .customer-details {
                grid-template-columns: 1fr;
            }

            .modal-body {
                padding: 16px;
            }

            .composer-wrap {
                padding: 14px;
            }
        }

        @media (max-width: 480px) {
            .modal-glass {
                border-radius: 12px;
            }

            .bubble {
                max-width: 85%;
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
    <!-- ===== DIMMED & BLURRED DASHBOARD BACKGROUND ===== -->
    <div class="dashboard-shell">
        <!-- Sidebar (dimmed) -->
        <aside class="sidebar" aria-label="Sidebar navigation" style="opacity: 0.35;">
            <div class="brand-wrap">
                <div class="brand-icon">◫</div>
                <div>
                    <div class="brand-text">AyyNet</div>
                    <div class="brand-subtitle">ISP Support Desk</div>
                </div>
            </div>
            <nav class="side-nav">
                <a href="#" class="nav-item active" style="opacity: 0.5;">
                    <span class="nav-icon">
                        <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 4.5A1.5 1.5 0 0 1 4.5 3h4A1.5 1.5 0 0 1 10 4.5v4A1.5 1.5 0 0 1 8.5 10h-4A1.5 1.5 0 0 1 3 8.5v-4Zm0 7A1.5 1.5 0 0 1 4.5 10h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 8.5 17h-4A1.5 1.5 0 0 1 3 15.5v-4Zm7-7A1.5 1.5 0 0 1 11.5 3h4A1.5 1.5 0 0 1 17 4.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 10 8.5v-4Zm0 7a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z"/></svg>
                    </span>
                    <span class="nav-label">Queue</span>
                    <span class="nav-badge">12</span>
                </a>
            </nav>
        </aside>

        <!-- Main panel (dimmed) -->
        <main class="main-panel" style="opacity: 0.35;">
            <section class="queue-panel">
                <div class="queue-header">
                    <div class="header-left">
                        <div class="header-title">
                            <h1>Ticket Queue</h1>
                            <p>Wednesday, 7 August 2026 · 10:01 WIB</p>
                        </div>
                    </div>
                </div>
                <div class="stats-grid">
                    <div class="stat-card open">
                        <div class="stat-header">
                            <span class="stat-label">Open</span>
                        </div>
                        <div class="stat-value">3</div>
                    </div>
                    <div class="stat-card progress">
                        <div class="stat-header">
                            <span class="stat-label">In Progress</span>
                        </div>
                        <div class="stat-value">5</div>
                    </div>
                    <div class="stat-card critical">
                        <div class="stat-header">
                            <span class="stat-label">Critical</span>
                        </div>
                        <div class="stat-value">2</div>
                    </div>
                    <div class="stat-card resolved">
                        <div class="stat-header">
                            <span class="stat-label">Resolved</span>
                        </div>
                        <div class="stat-value">18</div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- ===== CENTERED GLASSMORPHISM MODAL ===== -->
    <div class="modal-backdrop" id="modal-backdrop">
        <div class="modal-glass" id="ticket-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <!-- Close (X) Button -->
            <button class="modal-close" id="modal-close-btn" aria-label="Close ticket detail">
                <svg viewBox="0 0 20 20" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 5l10 10M15 5L5 15" stroke-linecap="round"/>
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title-row">
                    <div class="modal-ticket-id">TKT-0004</div>
                </div>
                <div class="modal-status-row">
                    <span class="tag checking">CHECKING</span>
                    <span class="tag priority-medium">MEDIUM</span>
                    <span class="tag whatsapp">{!! $channel['icon'] !!} {{ $channel['label'] }}</span>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Customer Profile Card -->
                <div class="customer-card">
                    <div class="customer-card-header">
                        <div class="customer-avatar">ME</div>
                        <div class="customer-info">
                            <div class="customer-name">mei</div>
                            <div class="customer-id">CUST-C-0001</div>
                        </div>
                        <span class="connection-status online">
                            <span class="status-dot"></span> Online
                        </span>
                    </div>
                    <div class="customer-details">
                        <div class="detail-item">
                            <span class="label">Service ID</span>
                            <span class="value">SV-2401-0047</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Package Speed</span>
                            <span class="value">100 Mbps</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Current Bandwidth</span>
                            <span class="value green">82.4 Mbps ↓ / 18.7 Mbps ↑</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Router Status</span>
                            <span class="value green">Online</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">OLT</span>
                            <span class="value">OLT-01/PON 2</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">ONU Serial</span>
                            <span class="value">HWTC-2F2A1B</span>
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

                <!-- Chat Section -->
                <div class="chat-section">
                    <div class="chat-topbar">
                        <span class="channel-tag whatsapp">
                            {!! $channel['icon'] !!}
                            Masuk melalui {{ $channel['label'] }}
                        </span>
                        <span>09:12</span>
                    </div>

                    <div class="messages">
                        <!-- Customer message -->
                        <div class="message customer">
                            <div class="msg-avatar">ME</div>
                            <div class="bubble">
                                <strong>mei</strong>
                                <p>
                                    Koneksi internet saya sering putus secara berkala sejak tadi pagi. Sudah coba restart router beberapa kali tapi masih sama. Mohon bantuannya.
                                </p>
                                <div class="msg-meta">
                                    <span>09:12</span>
                                    <span class="delivered">✓✓</span>
                                </div>
                            </div>
                        </div>

                        <!-- Staff reply -->
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

                        <!-- Internal note -->
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

                        <!-- Customer follow-up -->
                        <div class="message customer">
                            <div class="msg-avatar">ME</div>
                            <div class="bubble">
                                <strong>mei</strong>
                                <p>Sudah dicoba restart router tapi masih error. Mohon bantuannya.</p>
                                <div class="msg-meta">
                                    <span>09:40</span>
                                    <span class="delivered">✓✓</span>
                                </div>
                            </div>
                        </div>

                        <!-- Typing indicator -->
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
                    <span class="kbd">Enter</span> to send · <span class="kbd">Ctrl+Enter</span> for new line
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="toast-container" id="toast-container" aria-live="polite"></div>

    <script>
        // ===== CLOSE MODAL =====
        document.getElementById('modal-close-btn')?.addEventListener('click', function () {
            closeModal();
        });

        // Close on backdrop click
        document.getElementById('modal-backdrop')?.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        function closeModal() {
            const backdrop = document.getElementById('modal-backdrop');
            backdrop.style.opacity = '0';
            backdrop.style.pointerEvents = 'none';
            setTimeout(() => {
                window.location.href = '{{ route('dashboard') }}';
            }, 300);
        }

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

        // ===== ENTER / CTRL+ENTER =====
        document.getElementById('reply-input')?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.ctrlKey && !e.shiftKey) {
                e.preventDefault();
                sendReply();
            }
        });
    </script>
</body>
</html>
