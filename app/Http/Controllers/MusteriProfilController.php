<?php

namespace App\Http\Controllers;

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
}
