<?php

return [
    'service_url' => env('WHATSAPP_SERVICE_URL', 'http://127.0.0.1:3001'),
    // whatsmeow (Go) pilot bridge — paralel test ortami.
    // Yalniz WhatsmeowService bu URL'yi okur; mevcut WhatsAppService (Baileys)
    // hala service_url'yi kullanir. Production akisi etkilenmez.
    'whatsmeow_service_url' => env('WHATSMEOW_SERVICE_URL', 'http://127.0.0.1:3002'),
    'service_token' => env('WHATSAPP_SERVICE_TOKEN', ''),
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET', ''),
    'request_timeout' => (int) env('WHATSAPP_REQUEST_TIMEOUT', 10),
    'default_daily_limit' => (int) env('WHATSAPP_DEFAULT_DAILY_LIMIT', 200),
    // Randevu mesajlari gunluk limitten MUAF tutulan salonlar (virgullu id listesi).
    // Bu salonlarin limiti randevu_muaf_daily_limit'tir ve randevu/hatirlatma
    // mesajlari ne limite takilir ne de limitten sayilir (limit = kampanya/diger mesajlar).
    'randevu_muaf_salonlar' => array_values(array_filter(array_map('intval', explode(',', (string) env('WHATSAPP_RANDEVU_MUAF_SALONLAR', '236'))))),
    'randevu_muaf_daily_limit' => (int) env('WHATSAPP_RANDEVU_MUAF_DAILY_LIMIT', 250),
    'business_hours' => [
        // 'enforce' = false ise saat kontrolü tamamen atlanır (24/7 gönderim).
        // Test ve sürekli akış için default kapalı bırakıldı.
        'enforce' => (bool) env('WHATSAPP_BUSINESS_HOURS_ENFORCE', false),
        'start' => (int) env('WHATSAPP_BUSINESS_START', 9),
        'end' => (int) env('WHATSAPP_BUSINESS_END', 21),
    ],
    'fallback_to_sms' => (bool) env('WHATSAPP_FALLBACK_TO_SMS', true),
];
