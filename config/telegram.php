<?php

return [
    // Notification broadcast bot: one-way ticket alerts to the staff Telegram group.
    'notif_bot_token' => env('TELEGRAM_NOTIF_BOT_TOKEN'),
    'notif_group_id' => env('TELEGRAM_NOTIF_GROUP_ID'),
    'notif_enabled' => env('TELEGRAM_NOTIF_ENABLED', true),
];
