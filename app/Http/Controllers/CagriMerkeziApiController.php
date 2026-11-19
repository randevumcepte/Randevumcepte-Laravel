<?php

namespace App\Http\Controllers;

use App\AramaListesi;
use App\AranacakMusteriler;
use App\SistemYonetim\DakikaHesap;
use Illuminate\Http\Request;

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

    // ------------------------------------------------------------------
    // SANTRAL DAKIKA KAPISI (yalniz mobil; web arama_baslat'a dokunulmaz)
    // Softphone ve Bria (originate) aramalari salonun santral dakikasini harcar;
    // dakika bittiyse arama BASLATILMAZ, uyari doner. Cihazin kendi telefonuyla
    // arama (telefon_ile=1) santral kullanmadigi icin engellenmez.
    // ------------------------------------------------------------------

    public function arama_baslat(Request $request)
    {
        if (!filter_var($request->input('telefon_ile'), FILTER_VALIDATE_BOOLEAN)) {
            $kayit = AranacakMusteriler::where('id', $request->aranacak_musteri_id)->first();
            $liste = $kayit ? AramaListesi::where('id', $kayit->arama_id)->first() : null;
            if ($liste && ($uyari = DakikaHesap::aramaEngeli($liste->salon_id))) {
                return $this->dakikaBittiCevabi($uyari);
            }
        }
        return parent::arama_baslat($request);
    }

    public function arama_baslat_numara(Request $request)
    {
        if (!filter_var($request->input('telefon_ile'), FILTER_VALIDATE_BOOLEAN)) {
            if ($uyari = DakikaHesap::aramaEngeli(self::mevcutsube($request))) {
                return $this->dakikaBittiCevabi($uyari);
            }
        }
        return parent::arama_baslat_numara($request);
    }

    /** Tus takimi (uygulama ici softphone) acilmadan once: {ok, engel, mesaj}. */
    public function dakika_durum(Request $request)
    {
        $uyari = DakikaHesap::aramaEngeli(self::mevcutsube($request));
        return response()->json(['ok' => true, 'engel' => $uyari !== null, 'mesaj' => $uyari]);
    }

    /** 200 + success=false: uygulama mesaji alt bantta (kirmizi) gosterir. */
    private function dakikaBittiCevabi(string $uyari)
    {
        return response()->json(['success' => false, 'dakika_bitti' => true, 'message' => $uyari]);
    }
}
