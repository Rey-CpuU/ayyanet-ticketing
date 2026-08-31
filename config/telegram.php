<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    'allowed_chat_ids' => array_filter(explode(',', env('TELEGRAM_ALLOWED_CHAT_IDS', ''))),
    'default_admin_id' => env('TELEGRAM_DEFAULT_ADMIN_ID'),

    // Notification Broadcast Bot (Separate Bot for Staff Group Alert)
    'notif_bot_token' => env('TELEGRAM_NOTIF_BOT_TOKEN'),
    'notif_group_id' => env('TELEGRAM_NOTIF_GROUP_ID'),
    'notif_enabled' => env('TELEGRAM_NOTIF_ENABLED', true),
];
