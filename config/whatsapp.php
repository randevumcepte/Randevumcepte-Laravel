<?php

return [
    'service_url' => env('WHATSAPP_SERVICE_URL', 'http://127.0.0.1:3001'),
    'service_token' => env('WHATSAPP_SERVICE_TOKEN', ''),
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET', ''),
    'request_timeout' => (int) env('WHATSAPP_REQUEST_TIMEOUT', 10),
    'default_daily_limit' => (int) env('WHATSAPP_DEFAULT_DAILY_LIMIT', 150),
    'business_hours' => [
        'start' => (int) env('WHATSAPP_BUSINESS_START', 9),
        'end' => (int) env('WHATSAPP_BUSINESS_END', 21),
    ],
    'fallback_to_sms' => (bool) env('WHATSAPP_FALLBACK_TO_SMS', true),
];
