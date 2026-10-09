<?php

return [
    'sla' => [
        'high' => env('SLA_HIGH', '5h'),
        'medium' => env('SLA_MEDIUM', '8h'),
        'low' => env('SLA_LOW', '24h'),
    ],
    'channels' => [
        'email' => env('CHANNEL_EMAIL', true),
        'live_chat' => env('CHANNEL_LIVE_CHAT', true),
        'whatsapp' => env('CHANNEL_WHATSAPP', true),
    ],
];
