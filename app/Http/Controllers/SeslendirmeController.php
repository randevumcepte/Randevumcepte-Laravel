<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SeslendirmeServisi;

/**
 * Metni Google Cloud TTS ile MP3'e cevirip URL doner (sunucuda onbellekli).
 * Uygulama SADECE iOS'ta bunu kullanir; Android cihaz TTS'inde kalir.
 */
class SeslendirmeController extends Controller
{
    /** POST/GET /api/v1/seslendir  body: metin, (ops) ses -> {basarili, url} */
    public function uret(Request $request, SeslendirmeServisi $servis)
    {
        $metin = trim((string) $request->input('metin', ''));
        if ($metin === '') {
            return response()->json(['basarili' => false, 'hata' => 'Bos metin.'], 422);
        }
        // Asiri uzun metni sinirla (kotuye kullanim + kota korumasi)
        if (mb_strlen($metin) > 4000) {
            $metin = mb_substr($metin, 0, 4000);
        }

        $ses = null;
        if ($request->filled('ses')) {
            $ses = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $request->input('ses'));
        }

        $ad = $servis->uret($metin, $ses ?: null);
        if (!$ad) {
            // NOT: 502 yerine 200 -> Cloudflare govdeyi yemesin, app JSON okuyup cihaz
            // TTS'ine dussun. 'anahtar_var' teshis icin (false=.env'de yok/cache).
            $anahtarVar = (string) config('services.google_tts.key', '') !== '';
            return response()->json([
                'basarili'    => false,
                'hata'        => $anahtarVar
                    ? 'Ses uretilemedi (Google cagrisi basarisiz olabilir).'
                    : 'GOOGLE_TTS_API_KEY sunucuda tanimli degil (.env) veya config cache.',
                'anahtar_var' => $anahtarVar,
            ], 200);
        }

        return response()->json([
            'basarili' => true,
            'url'      => url('/api/v1/ses/' . $ad),
        ]);
    }

    /** GET /api/v1/ses/{ad}.mp3 -> onbellekteki MP3'u servis eder */
    public function ses($ad, SeslendirmeServisi $servis)
    {
        $yol = $servis->dosyaYolu($ad);
        if (!$yol) abort(404);

        return response()->file($yol, [
            'Content-Type'  => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
