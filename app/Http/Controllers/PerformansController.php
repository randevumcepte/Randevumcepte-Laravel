<?php

namespace App\Http\Controllers;

use App\Services\HataKaydi;
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
        $salonId = is_numeric($sube) ? (int) $sube : null;

        $baslik = [
            'timeout'   => 'Tarayıcı zaman aşımı',
            'baglanti'  => 'Bağlantı koptu',
            '502'       => 'HTTP 502 (PHP cevap vermedi)',
            '504'       => 'HTTP 504 (ağ geçidi zaman aşımı)',
        ][$durum] ?? ('Tarayıcı hatası '.$durum);
        HataKaydi::istemci('zaman_asimi', $baslik, null, null, mb_substr($yol, 0, 255), $salonId, (int) $yetkili->id);

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

    /** POST /isletmeyonetim/performans/js-hata  {mesaj, kaynak, satir, sutun, yigin, sayfa, sube} */
    public function jsHata(Request $request)
    {
        $yetkili = Auth::guard('isletmeyonetim')->user();
        if (!$yetkili) {
            return response()->json(['ok' => false], 401);
        }
        $mesaj = trim((string) $request->input('mesaj'));
        if ($mesaj === '') {
            return response()->json(['ok' => false], 422);
        }
        $kaynak = (string) parse_url((string) $request->input('kaynak'), PHP_URL_PATH);
        $konum = $kaynak !== '' ? $kaynak.':'.(int) $request->input('satir').':'.(int) $request->input('sutun') : null;
        $sayfa = (string) parse_url((string) $request->input('sayfa'), PHP_URL_PATH);
        $sube = $request->input('sube');

        HataKaydi::istemci(
            'js',
            'JS: '.mb_substr(strtok($mesaj, ':') ?: 'Hata', 0, 120),
            $mesaj,
            $konum,
            $sayfa !== '' ? $sayfa : null,
            is_numeric($sube) ? (int) $sube : null,
            (int) $yetkili->id,
            mb_substr((string) $request->input('yigin'), 0, 3000) ?: null
        );

        return response()->json(['ok' => true]);
    }
}
