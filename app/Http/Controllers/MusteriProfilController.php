<?php

namespace App\Http\Controllers;

use App\Services\AkilliBildirim;
use App\Services\BildirimKutusu;
use App\Services\IsletmeYasalMetin;
use App\Services\MusteriProfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Musteri profil uclari (mobil, Bearer + sube): /api/v1/musteri-profil */
class MusteriProfilController extends Controller
{
    /** [kullanici, salon_id, marka subeleri] ya da null */
    private function kisi(Request $request)
    {
        $u = Auth::guard('api')->user();
        $salonId = (int) $request->input('sube');
        if (!$u || !$salonId) return null;
        $salonlar = MusteriProfil::salonlar($u->id, $salonId);
        return $salonlar ? [$u, $salonId, $salonlar] : null;
    }

    private function yok()
    {
        return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
    }

    public function ozet(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return response()->json(['ok' => 1] + MusteriProfil::ozet($k[0]->id, $k[2], $k[1]));
    }

    public function hakkinda(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return response()->json(['ok' => 1, 'isletme' => IsletmeYasalMetin::isletme($k[1]), 'metinler' => IsletmeYasalMetin::metinler($k[1], true)]);
    }

    public function cihazlar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $bu = (string) $request->input('token');
        $liste = array_map(function ($c) use ($bu) {
            $c->bu_cihaz = $bu !== '' && hash_equals((string) $c->bildirim_id, $bu);
            unset($c->bildirim_id);
            return $c;
        }, MusteriProfil::cihazlar($k[0]->id, $k[1]));
        return response()->json(['ok' => 1, 'cihazlar' => $liste]);
    }

    public function cihazKapat(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $r = MusteriProfil::cihazKapat($k[0], $k[1], (int) $request->input('id'));
        return $r === true ? response()->json(['ok' => 1]) : response()->json(['ok' => 0, 'hata' => $r], 422);
    }

    /** Govde: {kapsam: secili|okunmus|tumu, idler: [], son_id} */
    public function bildirimSil(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $kapsam = (string) $request->input('kapsam');
        $adet = BildirimKutusu::sil(BildirimKutusu::musteri($k[0]->id, $k[1]), $kapsam, (array) $request->input('idler'), (int) $request->input('son_id'));
        if ($adet === null) return response()->json(['ok' => 0, 'hata' => 'Silinecek bildirim seçilmedi.'], 422);
        // Istek verilmez: musteri numarasi, ayni numarali isletme yetkilisi gibi loglanmasin
        BildirimKutusu::logla($k[1], null, 'musteri', $k[0]->id, $k[0]->name, $kapsam, $adet);
        return response()->json(['ok' => 1, 'silinen' => $adet]);
    }

    /** Hatirlatmaya cevap. Govde: {randevu_id, yanit: geliyorum|gecikecegim, dakika?, kaynak?} */
    public function randevuYanit(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $r = AkilliBildirim::randevuYanit((int) $k[0]->id, (int) $request->input('randevu_id'), (string) $request->input('yanit'),
            $request->filled('dakika') ? (int) $request->input('dakika') : null, (string) $request->input('kaynak', 'uygulama'));
        return response()->json($r, empty($r['ok']) ? 422 : 200);
    }

    /** Randevunun son musteri cevabi (detay ekraninda gosterilir) */
    public function randevuYanitGetir(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $id = (int) $request->input('randevu_id');
        if (!\App\Randevular::where('id', $id)->where('user_id', $k[0]->id)->exists()) return response()->json(['ok' => 0], 404);
        return response()->json(['ok' => 1, 'yanit' => AkilliBildirim::sonYanit($id)]);
    }

    /** Musteri bildirime dokundu. Govde: {randevu_id?, tip?} */
    public function bildirimAcildi(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $rid = (int) $request->input('randevu_id');
        if ($rid && !\App\Randevular::where('id', $rid)->where('user_id', $k[0]->id)->exists()) $rid = 0;
        AkilliBildirim::acildi((int) $k[0]->id, $rid ?: null, (int) $k[1], (string) $request->input('tip'));
        return response()->json(['ok' => 1]);
    }

    /** Kampanya / firsat bildirimleri izni. GET: okur, POST {kampanya: 0|1}: kaydeder */
    public function kampanyaIzni(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if ($request->isMethod('post')) {
            $deger = (int) $request->input('kampanya') === 1;
            AkilliBildirim::kampanyaIzniKaydet((int) $k[0]->id, $deger);
            try {
                \App\SalonYonetim\Audit::logApi($k[1], null, 'kampanya_izni', 'musteri', $k[0]->id, $k[0]->name,
                    $k[0]->name . ' kampanya bildirimlerini ' . ($deger ? 'açtı' : 'kapattı'));
            } catch (\Throwable $e) {
            }
        }
        return response()->json(['ok' => 1, 'kampanya' => AkilliBildirim::kampanyaIzni((int) $k[0]->id) ? 1 : 0,
            'sessiz_saat' => sprintf('%02d:00-%02d:00', AkilliBildirim::SESSIZ_BAS, AkilliBildirim::SESSIZ_BIT)]);
    }
}
