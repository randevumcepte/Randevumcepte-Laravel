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

        $sonuc = $servis->coz($metin, $salonId);
        $sonuc['salon_id'] = $salonId;

        return response()->json($sonuc);
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
        return null;
    }
}
