<?php

namespace App\Services;

/**
 * Passport 4 token'i yalniz kullanici NUMARASINI tasir; musteri (users) ile yetkili
 * (isletmeyetkilileri) numarasi cakisirsa token diger guard'da baska hesabi acar.
 * Token adina tip yazilir, guard'da karsilastirilir (App\Auth\TipliTokenGuard).
 *
 * Tipi yazilmamis ESKI token'lar ('appToken') aynen gecer: kimse oturumundan dusmez.
 * Anahtar: config('token_tipi.kontrol') / TOKEN_TIP_KONTROL (varsayilan kapali).
 */
class TokenTipi
{
    const YETKILI = 'appToken-yetkili';
    const MUSTERI = 'appToken-musteri';

    /** Token adi -> bu token'in acabilecegi model. Listede olmayan ad (eski token) denetlenmez. */
    private static $modeller = [
        self::YETKILI => \App\IsletmeYetkilileri::class,
        self::MUSTERI => \App\User::class,
        'v2-web'      => \App\IsletmeYetkilileri::class, // V2KopruController
    ];

    public static function acik()
    {
        return (bool) config('token_tipi.kontrol');
    }

    /** Giris/kayitta createToken() icin ad. Kapaliyken eski ad. */
    public static function ad($user)
    {
        if (!self::acik()) return 'appToken';
        if ($user instanceof \App\IsletmeYetkilileri) return self::YETKILI;
        if ($user instanceof \App\User) return self::MUSTERI;
        return 'appToken';
    }

    /** Bu addaki token bu modeli acabilir mi? */
    public static function uygun($tokenAdi, $user)
    {
        $model = self::$modeller[(string) $tokenAdi] ?? null;
        return $model === null || $user instanceof $model;
    }
}
