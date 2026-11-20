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
 * Web paneliyle (WaAsistanPanelController) AYNI metodlar ve ayni yetki kapisi (salon(): yalniz rol 1-2-3, sube zorunlu).
 *
 * Constructor bilerek BOS: StoreAdminController::__construct web oturum (session) kapisi kurar; api grubunda session
 * olmadigi icin her istek 401 session_expired yerdi. Kimligi route'taki auth:isletmeyonetim-api dogrular.
 */
class WaAsistanMobilController extends WaAsistanPanelController
{
    public function __construct()
    {
    }

    /**
     * Erisim kapisi. SOHBET uclari personel yetkisine bakar (rol degil): okuma 'whatsapp.sohbet_gor', yazma/devral/geri ver
     * 'whatsapp.sohbet_yaz' (salon sahibi her zaman; yonetici/sekreter varsayilan acik; personel rolune salon sahibi acar).
     * Diger uclar (ayar, kurallar, gelisim...) eskisi gibi rol 1-2-3.
     */
    protected function salon(Request $request): ?int
    {
        $yol = $request->path();
        if (preg_match('#wa-asistan/sohbet/\d+/(yaz|devral|geri-ver|devam|yol-tarifi|cevaplandi)$#', $yol)) return $this->sohbetSalon($request, 'whatsapp.sohbet_yaz');
        if (preg_match('#wa-asistan/(sohbetler$|sohbet/\d+$|gorsel/\d+$|avatar/\d+$)#', $yol)) return $this->sohbetSalon($request, 'whatsapp.sohbet_gor');
        return parent::salon($request);
    }

    /** Kullanici bu subede aktif yetkili mi ve $yetki anahtarina sahip mi: salon id ya da null. */
    protected function sohbetSalon(Request $request, string $yetki): ?int
    {
        $y = $this->asistanGorusmeYetkili();
        if (!$y) return null;
        $sid = (int) $request->input('sube');
        $salonlar = array_map('intval', $y->yetkili_olunan_isletmeler->where('aktif', 1)->pluck('salon_id')->toArray());
        if (!$sid || !in_array($sid, $salonlar, true)) return null;
        return \App\Services\PersonelYetkiServisi::yetkiliYetkiVar((int) $y->id, $sid, $yetki) ? $sid : null;
    }

    /** Menu gorunurlugu: asistan acik mi + kullanicinin yetkileri (gor: sohbetleri okur, yaz: sohbete katilir, yetki: yonetim ekranlari). */
    public function durum(Request $request)
    {
        $gor = $this->sohbetSalon($request, 'whatsapp.sohbet_gor');
        $yaz = $this->sohbetSalon($request, 'whatsapp.sohbet_yaz');
        $yon = parent::salon($request);
        $sid = $gor ?: $yon;
        if (!$sid) return response()->json(['ok' => 1, 'acik' => 0, 'yetki' => 0, 'gor' => 0, 'yaz' => 0]);
        try { $acik = WaAsistan::acikMi($sid); } catch (\Throwable $e) { $acik = false; }
        return response()->json(['ok' => 1, 'acik' => $acik ? 1 : 0, 'yetki' => $yon ? 1 : 0, 'gor' => $gor ? 1 : 0, 'yaz' => $yaz ? 1 : 0,
            'esik_dk' => WaAsistan::insanEsikDk()]);
    }

    /**
     * Personel uygulamadan musteriye yazar. Mesaj musteriye isimsiz (salon numarasindan) gider; gecmiste yazan gorunur.
     * Asistan 5 dk susar, personel sonra cevap vermezse asistan devam eder (telefondan yazmakla ayni).
     */
    public function yaz(Request $request, $id)
    {
        $sid = $this->salon($request);
        if (!$sid) return response()->json(['ok' => 0, 'hata' => 'Bu sohbete yazma yetkiniz yok.'], 403);
        $o = \Illuminate\Support\Facades\DB::table('wa_asistan_oturumlari')->where('id', (int) $id)->where('isletme_id', $sid)->first(['id']);
        if (!$o) return response()->json(['ok' => 0, 'hata' => 'Sohbet bulunamadı.'], 404);
        $y = $this->asistanGorusmeYetkili();
        $yazan = \App\Personeller::where('yetkili_id', $y->id)->where('salon_id', $sid)->value('personel_adi') ?: ($y->name ?? 'Personel');
        $r = WaAsistan::personelYazdi((int) $o->id, (string) $request->input('metin', ''), trim((string) $yazan) ?: 'Personel');
        return response()->json(['ok' => $r['ok'] ? 1 : 0, 'hata' => $r['hata']], $r['ok'] ? 200 : 422);
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
