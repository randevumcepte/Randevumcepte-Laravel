<?php

/**
 * Firebase Web SDK config (tarayici push).
 * Backend FCM v1 ile gondermek icin storage/app/firebase/*.json kullanir;
 * bu dosya sadece tarayici client'inin Firebase init etmesi icin gerekli
 * public anahtarlari .env'den okur.
 */
return [
    'apiKey'            => env('FIREBASE_API_KEY'),
    'authDomain'        => env('FIREBASE_AUTH_DOMAIN'),
    'projectId'         => env('FIREBASE_PROJECT_ID'),
    'storageBucket'     => env('FIREBASE_STORAGE_BUCKET'),
    'messagingSenderId' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'appId'             => env('FIREBASE_APP_ID'),
    'measurementId'     => env('FIREBASE_MEASUREMENT_ID'),
    'vapidKey'          => env('FIREBASE_VAPID_KEY'),
];
