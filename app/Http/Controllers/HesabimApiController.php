<?php

namespace App\Http\Controllers;

/**
 * Hesabim (Hesabim / My Account) — Mobil API koprusu.
 *
 * Web paneldeki Hesabim sayfasinin (StoreAdminController@hesabim) bilgi
 * fonksiyonlarini mobil uygulamayla paylasir. hesabim() blade view dondurdugu
 * icin JSON ucu olarak hesabimApiFeed() kullanilir (StoreAdminController'da,
 * guard-agnostik cmUser ile). Fatura bilgisi guncelleme zaten JSON donen
 * hesabimFaturaBilgiGuncelle()'dir (guard-agnostik, request->sube).
 *
 * Web constructor'ina dokunulmaz; bu sinif 'isletmeyonetim-api' (Passport) guard
 * uygular. Satin alma/yukseltme YOK (Netflix modeli) — sadece bilgilendirme.
 */
class HesabimApiController extends StoreAdminController
{
    public function __construct()
    {
        $this->middleware('auth:isletmeyonetim-api');
    }
}
