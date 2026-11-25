<?php

/*
 * Musteri dili (cok dillilik). Pilot: yalniz 'salonlar' listesindeki isletmelerde acik; listede olmayan
 * salonda hicbir sey degismez (uygulamada dil dugmesi yok, mesajlar Turkce).
 * Yeni dil: resources/dil/kaynak/*.txt'ye sutun + scripts/dil-derle.js + buraya satir.
 */
return [
    'salonlar' => [236], // Ola Nail (pilot)

    'diller' => [
        ['kod' => 'tr', 'ad' => 'Türkçe', 'bayrak' => '🇹🇷'],
        ['kod' => 'en', 'ad' => 'English', 'bayrak' => '🇬🇧'],
        ['kod' => 'ru', 'ad' => 'Русский', 'bayrak' => '🇷🇺'],
        ['kod' => 'de', 'ad' => 'Deutsch', 'bayrak' => '🇩🇪'],
    ],

    // SMS daima Turkce gider (isletme istegi 2026-11-25); WhatsApp ve uygulama bildirimi musterinin dilinde
    'sms_cevir' => false,

    // (sms_cevir aciksa) Latin disi alfabe: eski SMS saglayicisi (VoiceTelekom, encoding 0) tasiyamaz -> SMS bu dilde gider
    'sms_latin_yedek' => ['ru' => 'en'],

    // Sozlukte olmayan serbest metin (kampanya vb.) yapay zeka ile bir kez cevrilir ve saklanir
    'ai_model' => 'claude-haiku-5-5',
    'ai_gunluk_tavan' => 300, // salon basina gunluk yeni ceviri
];
