<?php

return [

    // Paraşüt API kimlik bilgileri. Prod sunucudaki .env dosyasina eklenecek.
    // Paraşüt → Ayarlar → Paraşüt API / Geliştirici bölümünden alınır.
    'client_id'     => env('PARASUT_CLIENT_ID', ''),
    'client_secret' => env('PARASUT_CLIENT_SECRET', ''),
    'username'      => env('PARASUT_USERNAME', ''),   // Paraşüt giriş e-postası
    'password'      => env('PARASUT_PASSWORD', ''),   // Paraşüt giriş şifresi
    'company_id'    => env('PARASUT_COMPANY_ID', ''), // parasut.com/{company_id}/...

    // API uç noktaları
    'base_url'  => env('PARASUT_BASE_URL', 'https://api.parasut.com/v4'),
    'token_url' => env('PARASUT_TOKEN_URL', 'https://api.parasut.com/oauth/token'),

    // KDV oranı (fiyatlar KDV dahil girildiği için matrah geriye hesaplanır)
    'kdv_orani' => (int) env('PARASUT_KDV_ORANI', 20),

    // Faturada görünecek ürün/hizmet adı
    'urun_adi'  => env('PARASUT_URUN_ADI', 'İnteraktif Duyuru Paketi'),

];
