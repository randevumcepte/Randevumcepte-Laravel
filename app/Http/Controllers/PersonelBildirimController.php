<?php

namespace App\Http\Controllers;

use App\Services\PersonelBildirimSessiz;
use Illuminate\Http\Request;

/**
 * Bildirimlerim (2026-11-23): giris yapan kisi (her rol) kendi uygulama bildirimlerini tur bazinda
 * sesli/sessiz yapar. Yalniz kendi personel kaydi; isletmenin kanal ayarlarina dokunmaz.
 * Web: oturum, mobil: Bearer + sube parametresi.
 */
class PersonelBildirimController extends StoreAdminController
{
    /** SAC kurucusundaki web-oturum kapisi mobil Bearer istegini reddederdi; kimlik kisi() icinde dogrulanir. */
    public function __construct()
    {
    }

    /** [salon_id, personel satiri, alici (yonetici|personel)] ya da null. */
    protected function kisi(Request $request)
    {
        $yetkili = $this->asistanGorusmeYetkili();
        if (!$yetkili) return null;
        $salonId = (int) ($request->has('sube') ? $request->input('sube') : self::mevcutsube($request));
        $salonlar = array_map('intval', $yetkili->yetkili_olunan_isletmeler->where('aktif', 1)->pluck('salon_id')->toArray());
        if (!$salonId || !in_array($salonId, $salonlar, true)) return null;
        $p = \App\Personeller::where('salon_id', $salonId)->where('yetkili_id', $yetkili->id)->first(['id', 'personel_adi']);
        if (!$p) return null;
        $rol = self::kullaniciRolu($salonId, $yetkili->id);
        $alici = ($rol === null || (int) $rol < 5) ? 'yonetici' : 'personel';
        return [$salonId, $p, $alici];
    }

    public function veri(Request $request)
    {
        $k = $this->kisi($request);
        if (!$k) return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
        return response()->json(['ok' => 1] + PersonelBildirimSessiz::ekranVerisi($k[0], (int) $k[1]->id, $k[2]));
    }

    /** Govde: {kategori, sessiz: 0/1} */
    public function kaydet(Request $request)
    {
        $k = $this->kisi($request);
        if (!$k) return response()->json(['ok' => 0, 'hata' => 'Oturum bulunamadı.'], 403);
        $sonuc = PersonelBildirimSessiz::kaydet($k[0], (int) $k[1]->id, (string) $request->input('kategori'),
            (int) $request->input('sessiz') === 1, $request, $k[1]->personel_adi);
        if ($sonuc === null) return response()->json(['ok' => 0, 'hata' => 'Geçersiz bildirim türü.'], 422);
        return response()->json(['ok' => 1, 'degisti' => $sonuc] + PersonelBildirimSessiz::ekranVerisi($k[0], (int) $k[1]->id, $k[2]));
    }
}
