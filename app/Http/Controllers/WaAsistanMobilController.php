<?php

namespace App\Http\Controllers;

use App\Services\WaAsistan;
use App\Services\WaAsistanVeri;
use App\Services\WaGeriKazanim;
use App\Services\WaGorselDanisman;
use App\Services\WaPaket;
use Illuminate\Http\Request;

/**
 * Mobil uygulama (Bearer, auth:isletmeyonetim-api) > WhatsApp Asistanı.
 * Web paneliyle (WaAsistanPanelController) AYNI metodlar ve ayni yetki kapisi (asistanGorusmeSalon: rol < 5, sube zorunlu).
 *
 * Constructor bilerek BOS: StoreAdminController::__construct web oturum (session) kapisi kurar; api grubunda session
 * olmadigi icin her istek 401 session_expired yerdi. Kimligi route'taki auth:isletmeyonetim-api dogrular.
 */
class WaAsistanMobilController extends WaAsistanPanelController
{
    public function __construct()
    {
    }

    /** Menu gorunurlugu (web menusu de yalniz asistani acik isletmede gosterir). Yetkisizse acik=0. */
    public function durum(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return response()->json(['ok' => 1, 'acik' => 0, 'yetki' => 0]);
        try { $acik = WaAsistan::acikMi($sid); } catch (\Throwable $e) { $acik = false; }
        return response()->json(['ok' => 1, 'acik' => $acik ? 1 : 0, 'yetki' => 1]);
    }

    /** Web sayfasinin sunucuda bastigi baslangic verisi (ayarlar, sektorler, kurallar, hizmetler, otomatik eslesmeler). */
    public function baslangic(Request $request)
    {
        $sid = $this->salon($request);
        if (!$sid) return $this->yok();
        WaAsistanVeri::semaGaranti();
        WaGeriKazanim::semaGaranti();
        WaGorselDanisman::semaGaranti();
        $hizmetler = self::hizmetListesi($sid);
        $ayar = WaAsistanVeri::ayarlar($sid);
        $sektorler = WaGorselDanisman::sektorler();
        $hz = $hizmetler->map(function ($h) { return (array) $h; })->all();
        $oto = [];
        foreach (array_keys($sektorler) as $s) $oto[$s] = (object) WaGorselDanisman::otomatikKurallar($hz, $s);
        return response()->json([
            'ok' => 1,
            'acik' => WaAsistan::acikMi($sid),
            'ayar' => $ayar,
            'paket' => WaPaket::durum($sid),
            'sektorler' => $sektorler,
            'kurallar' => (object) WaGorselDanisman::kurallar($sid, (string) $ayar['gorsel_sektor']),
            'hizmetler' => $hizmetler,
            'oto_kurallar' => (object) $oto,
        ]);
    }
}
