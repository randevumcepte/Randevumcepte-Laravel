<?php

namespace App\Http\Controllers;

/**
 * Cagri Merkezi - Mobil API koprusu.
 *
 * Mobil uygulama (Flutter) ile web panelin AYNI cagri merkezi is mantigini
 * paylasmasi icin StoreAdminController'i genisletir. Tum cagri merkezi metotlari
 * (arama_kartlarim, arama_baslat, santral_not_ekle, ...) parent'tan aynen miras alinir.
 *
 * TEK FARK kimlik dogrulamadir:
 *  - StoreAdminController web constructor'i 'isletmeyonetim' (session) guard'ini zorunlu kilar.
 *  - Bu sinif parent constructor'ini CAGIRMAZ; yerine 'isletmeyonetim-api' (Passport/Bearer)
 *    guard'ini uygular. Boylece WEB CONSTRUCTOR'INA HIC DOKUNULMAZ, web davranisi bire bir korunur.
 *
 * Miras alinan metotlardaki kimlik cozumleme cmAuthId()/cmUser() (Controller.php) uzerinden
 * yapildigi icin web istegi eski davranisla ayni, mobil istek api guard ile calisir.
 */
class CagriMerkeziApiController extends StoreAdminController
{
    public function __construct()
    {
        // Web constructor'ini bilerek cagirmiyoruz (parent::__construct YOK).
        $this->middleware('auth:isletmeyonetim-api');
    }
}
