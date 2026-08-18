<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Salonlar;
use App\Services\SesliRandevuCozService;

/**
 * Sesli / yazili komuttan randevu cozumleme endpoint'i (SADECE mobil uygulama).
 *
 * Akis:
 *   1) Uygulama sesi cihazda metne cevirir (speech_to_text, ucretsiz).
 *   2) Metni buraya yollar -> kural motoru cozer (LLM yok, sifir maliyet).
 *   3) Uygulama donen yapiyla ONAY KARTINI gosterir.
 *   4) Kullanici onaylayinca MEVCUT /api/v1/randevuekleguncelle cagrilir.
 *
 * Bu endpoint randevu OLUSTURMAZ; sadece cozumleme yapar (salt-okunur).
 */
class SesliRandevuController extends Controller
{
    public function coz(Request $request, SesliRandevuCozService $servis)
    {
        $metin = trim((string) $request->input('metin', ''));
        if ($metin === '') {
            return response()->json([
                'basarili' => false,
                'hata'     => 'Bos metin. Once konusun ya da yazin.',
            ], 422);
        }

        // Salon cozumleme: randevuekleguncelle ile ayni mantik (salonid | appBundle)
        $salonId = $this->salonIdCoz($request);
        if (!$salonId) {
            return response()->json([
                'basarili' => false,
                'hata'     => 'Salon bulunamadi (salonid veya appBundle gonderin).',
            ], 422);
        }

        // Randevu DAIMA giris yapan personel adina; hizmetler o personelinkiyle sinirli
        $personelId = $request->filled('personel_id') ? (int) $request->input('personel_id') : null;

        $sonuc = $servis->coz($metin, $salonId, $personelId);
        $sonuc['salon_id'] = $salonId;

        // Debug alanlari yalnizca ?debug=1 ile don (app'e temiz JSON gitsin)
        if (!$request->has('debug')) {
            unset($sonuc['_ver'], $sonuc['_debug']);
        }

        // Eski kayitlarda utf8 kolon icinde latin5 bayt olabiliyor -> json_encode patlar.
        // Gecersiz UTF-8 string'leri Windows-1254'ten cevir (Turkce legacy).
        $sonuc = $this->utf8Duzelt($sonuc);

        return response()->json($sonuc, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Diziyi/degeri ozyinelemeli dolasip gecersiz UTF-8 string'leri onarir.
     */
    protected function utf8Duzelt($deger)
    {
        if (is_array($deger)) {
            foreach ($deger as $k => $v) {
                $deger[$k] = $this->utf8Duzelt($v);
            }
            return $deger;
        }
        if (is_string($deger) && !mb_check_encoding($deger, 'UTF-8')) {
            return mb_convert_encoding($deger, 'UTF-8', 'Windows-1254');
        }
        return $deger;
    }

    /**
     * En yakin BOS slotu bulur (calisma saati + mevcut randevulara gore).
     * Girdi: salonid|appBundle, personel_id, hizmet_id, (ops) tarih, saat, vakit.
     */
    public function musaitlik(Request $request, SesliRandevuCozService $servis)
    {
        $salonId = $this->salonIdCoz($request);
        if (!$salonId) {
            return response()->json(['bulundu' => false, 'hata' => 'Salon bulunamadi.'], 422);
        }
        $personelId  = $request->filled('personel_id') ? (int) $request->input('personel_id') : null;
        $hizmetId    = (int) $request->input('hizmet_id');
        $tercihTarih = $request->filled('tarih') ? $request->input('tarih') : null;
        $tercihSaat  = $request->filled('saat') ? $request->input('saat') : null;
        $vakit       = $request->filled('vakit') ? $request->input('vakit') : null;

        $sonuc = $servis->musaitlikBul($salonId, $personelId, $hizmetId, $tercihTarih, $tercihSaat, $vakit);

        if (!$request->has('debug')) {
            unset($sonuc['_debug']);
        }

        return response()->json($sonuc);
    }

    /** Personelin randevu takvimi acik mi + hizmeti var mi? (mikrofona basinca on kontrol) */
    public function takvimDurumu(Request $request, SesliRandevuCozService $servis)
    {
        $personelId = $request->filled('personel_id') ? (int) $request->input('personel_id') : null;
        return response()->json([
            'acik'       => $servis->takvimAcikMi($personelId),
            'hizmet_var' => $servis->personelHizmetiVarMi($personelId),
        ]);
    }

    protected function salonIdCoz(Request $request)
    {
        if ($request->filled('salonid')) {
            return (int) $request->input('salonid');
        }
        if ($request->filled('appBundle')) {
            $salon = Salonlar::where('app_bundle', $request->input('appBundle'))->first();
            return $salon ? (int) $salon->id : null;
        }
        // Test kolayligi: salon adiyla da cozulebilsin (uygulama salonid/appBundle gonderecek)
        if ($request->filled('salonAdi')) {
            $salon = Salonlar::where('salon_adi', 'like', '%' . $request->input('salonAdi') . '%')->first();
            return $salon ? (int) $salon->id : null;
        }
        return null;
    }
}
