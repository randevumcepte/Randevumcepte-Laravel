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
    ],

    // Google Cloud Text-to-Speech — SADECE iOS icin bulut ses (Android cihaz sesi
    // zaten bedava/iyi). Anahtar YOKSA sistem cihaz TTS'ine duser (regresyon yok).
    // Cevaplar sunucuda MP3 olarak ONBELLEGE alinir -> ayni cumle tekrar uretilmez.
    'google_tts' => [
        'key'   => env('GOOGLE_TTS_API_KEY', ''),
        'voice' => env('GOOGLE_TTS_VOICE', 'tr-TR-Wavenet-E'), // erkek; kadin icin tr-TR-Wavenet-D
    ],

];
