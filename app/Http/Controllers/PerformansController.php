<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Performans izleme uclari. Tarayicida zaman asimina dusen istekler sunucuda hic gorunmez
 * (PHP isini bitirir, kullanici ise takilma yasar); custom.js bunlari buraya bildirir ve
 * yavas_istekler'e method=TIMEOUT satiri olarak yazilir.
 */
class PerformansController extends Controller
{
    /** POST /isletmeyonetim/performans/zaman-asimi  {url, sure_ms, sube, durum} */
    public function istemciZamanAsimi(Request $request)
    {
        $yetkili = Auth::guard('isletmeyonetim')->user();
        if (!$yetkili) {
            return response()->json(['ok' => false], 401);
        }

        // Yalniz yol: sorgu dizgisi telefon/isim tasiyabilir.
        $yol = (string) parse_url((string) $request->input('url'), PHP_URL_PATH);
        if ($yol === '') {
            return response()->json(['ok' => false], 422);
        }
        $durum = preg_replace('/[^a-z0-9]/i', '', (string) $request->input('durum'));
        $sube = $request->input('sube');

        try {
            DB::table('yavas_istekler')->insert([
                'created_at' => date('Y-m-d H:i:s'),
                'uri'        => mb_substr($yol, 0, 500),
                'method'     => 'TIMEOUT',
                'aksiyon'    => mb_substr('istemci:'.($durum !== '' ? $durum : 'timeout'), 0, 191),
                'user_id'    => (int) $yetkili->id,
                'salon_id'   => is_numeric($sube) ? (int) $sube : null,
                'sure_ms'    => min(max((int) $request->input('sure_ms'), 0), 3600000),
            ]);
        } catch (\Throwable $e) {
            // izleme kaydi hicbir zaman hata dondurmesin
        }

        return response()->json(['ok' => true]);
    }
}
