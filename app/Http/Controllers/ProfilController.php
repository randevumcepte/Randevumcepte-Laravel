<?php

namespace App\Http\Controllers;

use App\Services\PersonelProfil;
use Illuminate\Http\Request;

/**
 * Profil sayfasi uclari (2026-11-23). Giris yapan kisi (her rol) kendi verisini gorur/degistirir;
 * ekip, hedef ve izin karari yalniz yonetici (rol < 5). Web: oturum, mobil: Bearer + sube.
 */
class ProfilController extends StoreAdminController
{
    /** SAC kurucusundaki web-oturum kapisi mobil Bearer istegini reddederdi; kimlik kisi() icinde dogrulanir. */
    public function __construct()
    {
    }

    /** [salon_id, personel satiri, yonetici mi] ya da null. */
    protected function kisi(Request $request)
    {
        $yetkili = $this->asistanGorusmeYetkili();
        if (!$yetkili) return null;
        $salonId = (int) ($request->has('sube') ? $request->input('sube') : self::mevcutsube($request));
        $salonlar = array_map('intval', $yetkili->yetkili_olunan_isletmeler->where('aktif', 1)->pluck('salon_id')->toArray());
        if (!$salonId || !in_array($salonId, $salonlar, true)) return null;
        $p = \App\Personeller::where('salon_id', $salonId)->where('yetkili_id', $yetkili->id)->first(['id', 'personel_adi', 'unvan', 'salon_id']);
        if (!$p) return null;
        $rol = self::kullaniciRolu($salonId, $yetkili->id);
        PersonelProfil::semaGaranti();
        return [$salonId, $p, $rol === null || (int) $rol < 5];
    }

    private function yok()
    {
        return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
    }

    private function sonuc($r, array $ek = [])
    {
        if (is_string($r)) return response()->json(['ok' => 0, 'hata' => $r], 422);
        return response()->json(['ok' => 1] + $ek);
    }

    public function ozet(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return response()->json(['ok' => 1, 'yonetici' => $k[2], 'personel_id' => (int) $k[1]->id,
            'durumlar' => PersonelProfil::DURUMLAR] + PersonelProfil::ozet($k[0], $k[1], $k[2]));
    }

    public function durum(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!PersonelProfil::durumKaydet($k[0], $k[1], (string) $request->input('durum'), $request)) return $this->sonuc('Geçersiz durum.');
        return $this->sonuc(true, ['durum' => PersonelProfil::durum($k[1]->id)]);
    }

    public function yorumlar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['yorumlar' => PersonelProfil::yorumlar($k[0], (int) $k[1]->id, $k[2] && $request->input('tum') == 1)]);
    }

    public function calismaSaatleri(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, PersonelProfil::calismaSaatleri((int) $k[1]->id));
    }

    public function hizmetler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['hizmetler' => PersonelProfil::hizmetler($k[0], (int) $k[1]->id)]);
    }

    public function izinler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $tum = $k[2] && $request->input('tum') == 1;
        return $this->sonuc(true, ['izinler' => PersonelProfil::izinler($k[0], (int) $k[1]->id, $tum), 'yonetici' => $k[2]]);
    }

    public function izinTalep(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::izinTalep($k[0], $k[1], $request->only(['baslangic', 'bitis', 'tur', 'aciklama']), $request));
    }

    public function izinIptal(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::izinIptal($k[0], $k[1], (int) $request->input('id'), $request));
    }

    public function izinKarar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'İzin kararını yalnızca yönetici verebilir.'], 403);
        return $this->sonuc(PersonelProfil::izinKarar($k[0], $k[1], (int) $request->input('id'),
            (int) $request->input('onay') === 1, (string) $request->input('not'), $request));
    }

    public function ekip(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Bu ekran yalnızca yöneticiye açık.'], 403);
        return $this->sonuc(true, ['ekip' => PersonelProfil::ekip($k[0]), 'donem' => date('Y-m'), 'durumlar' => PersonelProfil::DURUMLAR]);
    }

    public function hedef(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$k[2]) return response()->json(['ok' => 0, 'hata' => 'Hedefi yalnızca yönetici belirleyebilir.'], 403);
        return $this->sonuc(PersonelProfil::hedefKaydet($k[0], $k[1], (int) $request->input('personel_id'), $request->input('tutar'), $request));
    }

    public function calismalar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(true, ['calismalar' => PersonelProfil::calismalar((int) $k[1]->id)]);
    }

    public function calismaEkle(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        if (!$request->hasFile('resim')) return $this->sonuc('Fotoğraf seçin.');
        return $this->sonuc(PersonelProfil::calismaEkle($k[0], $k[1], $request->file('resim'), $request->input('aciklama'), $request));
    }

    public function calismaSil(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::calismaSil($k[0], $k[1], (int) $request->input('id'), $request));
    }

    public function yetkiler(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $y = $this->asistanGorusmeYetkili();
        return $this->sonuc(true, ['gruplar' => PersonelProfil::yetkiler($k[0], $y->id), 'yonetici' => $k[2]]);
    }

    public function cihazlar(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        $bu = (string) $request->input('token');
        $liste = array_map(function ($c) use ($bu) {
            $c->bu_cihaz = $bu !== '' && hash_equals((string) $c->bildirim_id, $bu);
            unset($c->bildirim_id);
            return $c;
        }, PersonelProfil::cihazlar($k[0], (int) $k[1]->id));
        return $this->sonuc(true, ['cihazlar' => $liste]);
    }

    public function cihazKapat(Request $request)
    {
        if (!$k = $this->kisi($request)) return $this->yok();
        return $this->sonuc(PersonelProfil::cihazKapat($k[0], $k[1], (int) $request->input('id'), $request));
    }
}
