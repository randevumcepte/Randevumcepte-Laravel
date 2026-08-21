<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Saglayici (voicetelekom) CDR scraper ayarlari
    |--------------------------------------------------------------------------
    | E-santral trunk'larinin FATURALANDIRILAN dakikasini saglayici panelinden
    | (API'si olmadigi icin HTML scrape ile) ceker. Kimlik bilgilerini .env'e
    | koy; koda yazma. Degistirdikten sonra: php artisan config:clear
    |
    | .env ornegi:
    |   SANTRAL_SAGLAYICI_USER="kullanici"
    |   SANTRAL_SAGLAYICI_PASS="sifre"
    |
    | cdr_url: {SOURCE} {START} {END} yer tutucularini icermeli. Calisan bir
    | rapor URL'in varsa aynen buraya koyup source/tarihleri yer tutucuyla
    | degistir (c2963/caller gibi hesaba ozel parcalari oldugu gibi birak).
    */
    'saglayici' => [
        'base'       => env('SANTRAL_SAGLAYICI_BASE', 'https://sip1.voicetelekom.net'),
        'login_path' => env('SANTRAL_SAGLAYICI_LOGIN_PATH', '/main.php'),
        'acct_type'  => env('SANTRAL_SAGLAYICI_ACCT', 'customer'),
        'user'       => env('SANTRAL_SAGLAYICI_USER'),
        'pass'       => env('SANTRAL_SAGLAYICI_PASS'),
        'verify_ssl' => (bool) env('SANTRAL_SAGLAYICI_VERIFY_SSL', true),
        'cache_dk'   => (int) env('SANTRAL_SAGLAYICI_CACHE_DK', 30),
        'cdr_url'    => env(
            'SANTRAL_SAGLAYICI_CDR_URL',
            'https://sip1.voicetelekom.net/c2963/cdrs_customer.php'
            . '?cli_clause=0&source={SOURCE}&startDate={START}&caller=2_3094'
            . '&cld_clause=0&destination=&endDate={END}&cdr_currency=TRY'
            . '&calls_select=3&from_form=1&action='
        ),
    ],

];
