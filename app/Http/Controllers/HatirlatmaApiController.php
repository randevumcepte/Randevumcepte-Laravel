<?php

namespace App\Http\Controllers;

/**
 * Salon Hatirlatma (reminder/toast feed) — Mobil API koprusu.
 *
 * Web paneldeki sag-alt hatirlatma kart sisteminin AYNI is mantigini mobil
 * uygulamayla paylasmak icin SalonHatirlatmaController'i genisletir. feed()
 * ve tum tetikleyici metotlar parent'tan aynen miras alinir.
 *
 * TEK FARK kimlik dogrulamadir:
 *  - SalonHatirlatmaController web constructor'i 'isletmeyonetim' (session) guard'ini zorunlu kilar.
 *  - Bu sinif parent constructor'ini CAGIRMAZ; 'isletmeyonetim-api' (Passport/Bearer) guard'i uygular.
 *    Web constructor'ina hic dokunulmaz, web davranisi bire bir korunur.
 *
 * Kullaniciya ozel arama randevulari cmUser() (guard-agnostik) ile cozulur:
 * web istegi eski davranisla ayni, mobil istek api guard ile calisir.
 * Salon, request'teki 'sube' parametresinden gelir (aktifSalonId erken doner).
 */
class HatirlatmaApiController extends SalonHatirlatmaController
{
    public function __construct()
    {
        // Web constructor'ini bilerek cagirmiyoruz (parent::__construct YOK).
        $this->middleware('auth:isletmeyonetim-api');
    }
}
