<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
     'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
    ],

    'facebook' => [
        'client_id'     => env('1487476975355426'),
        'client_secret' => env('0bdfeee1fa72f16a7fc9c4175bb0735a'),
        'redirect'      => env('/'),
    ],

    // Patron Asistani — opsiyonel Haiku fallback (kural motoru anlamayinca).
    // Anahtar YOKSA asistan tamamen bedava kural motorunda calisir.
    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY', ''),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5'),
        // Yalniz telefon asistani (RandevuAsistan). Diger ozellikler (patron/uygulama asistani) 'model'i kullanir.
        // Geri donus: .env ANTHROPIC_SANTRAL_MODEL=claude-haiku-4-5
        'santral_model' => env('ANTHROPIC_SANTRAL_MODEL', 'claude-haiku-5-5'),
        'santral_effort' => env('ANTHROPIC_SANTRAL_EFFORT', 'medium'),
    ],

    // Santral yapay zeka asistani (randevu-santral-ai): randevu (1 gun once) ve alacak (2 gun once)
    // HATIRLATMA aramalari hangi salonlarda yeni asistanla yapilir. "20,35" ya da "*" (hepsi).
    // Listede olmayan salonlar eski tuslamali IVR'da kalir. Varsayilan: yalniz test salonu 20.
    'santral_ai' => [
        'hatirlatma_salonlar' => env('SANTRAL_AI_HATIRLATMA_SALONLAR', '20'),
        // Asistan paket yonetimi maliyet hesabi (USD). Claude: Haiku 5.5 fiyati / 1M token;
        // cache okuma = girdi x0.1, cache yazma = girdi x1.25. STT: Google streaming dakika basi; TTS: Chirp3-HD / 1M karakter.
        'girdi_usd_m' => env('SANTRAL_GIRDI_USD_M', 0.10),
        'cikti_usd_m' => env('SANTRAL_CIKTI_USD_M', 0.50),
        'stt_usd_dk'  => env('SANTRAL_STT_USD_DK', 0.016),
        'tts_usd_m'   => env('SANTRAL_TTS_USD_M', 30.0), // Chirp3-HD; kopru Wavenet kullaniyorsa .env'de 4
        'stt_oran'    => env('SANTRAL_STT_ORAN', 0.52),   // STT'nin ucretlenen kismi (kopru AI konusurken akisi kapatir); kapaliysa 1
        // Arac sonucu belirliyse cumleyi kod kurar, son Claude cagrisi atlanir (gecikme). 0 = kapali.
        'kod_cevap' => (bool) env('SANTRAL_KOD_CEVAP', true),
    ],

    // Google Cloud Text-to-Speech — SADECE iOS icin bulut ses (Android cihaz sesi
    // zaten bedava/iyi). Anahtar YOKSA sistem cihaz TTS'ine duser (regresyon yok).
    // Cevaplar sunucuda MP3 olarak ONBELLEGE alinir -> ayni cumle tekrar uretilmez.
    // Santral ses onizleme (Sistem Yonetimi). Kopru (santral sunucusu) kendi .env'indeki anahtari kullanir.
    'elevenlabs' => [
        'key' => env('ELEVENLABS_API_KEY', ''),
    ],

    'google_tts' => [
        'key'   => env('GOOGLE_TTS_API_KEY', ''),
        // Chirp3-HD = Google'in en dogal/insansi sesleri (erkek: Charon/Puck/Fenrir/Orus).
        // SSML/pitch DESTEKLEMEZ; dogalligi + speakingRate ile sicaklik verilir.
        'voice' => env('GOOGLE_TTS_VOICE', 'tr-TR-Chirp3-HD-Charon'),
        // Chirp3-HD proje/bolgede kapaliysa (400) buna duser -> cagride sessizlik olmaz.
        'voice_fallback' => env('GOOGLE_TTS_VOICE_FALLBACK', 'tr-TR-Wavenet-E'),
        // Hafif yavas = daha sicak/anlasilir (1.0 notr, 0.90-0.95 sicak).
        'rate'  => (float) env('GOOGLE_TTS_RATE', 0.95),
    ],

    // Mobil softphone iOS VoIP push: santral (softphoneVoipPush.php) <-> SoftphoneApiController.
    'softphone' => [
        'santral_anahtar' => env('SOFTPHONE_SANTRAL_ANAHTAR', ''),
    ],

];
