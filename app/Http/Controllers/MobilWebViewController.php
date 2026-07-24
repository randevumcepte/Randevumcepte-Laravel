<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\IsletmeYetkilileri;

/**
 * Mobil uygulama WebView koprusu.
 *
 * Uygulama Passport Bearer token'i ile /api/v1/mobil/webview-token cagirir,
 * 2 dk gecerli tek kullanimlik imzali bir link alir ve bunu WebView'da acar.
 * /mobil/webview-giris imzayi dogrulayip ilgili yetkiliyi web oturumuna sokar,
 * boylece WhatsApp/kontor gibi ekranlar uygulama icinde salonun kendi
 * oturumuyla (kendi bakiyesi/paketleri) acilir. Ileride fiyat/paket degisince
 * uygulama guncellemesi gerekmez; icerik server-side gelir.
 */
class MobilWebViewController extends Controller
{
    /**
     * Acilabilir hedefler (whitelist) — acik yonlendirmeyi (open redirect) onler.
     */
    private static function hedefler()
    {
        return [
            'kontor'   => '/isletmeyonetim/whatsapp-kontor-mobil',
            'whatsapp' => '/isletmeyonetim/whatsapp',
        ];
    }

    /**
     * API (auth:isletmeyonetim-api): imzali tek kullanimlik giris linki uretir.
     * GET /api/v1/mobil/webview-token?hedef=kontor&sube=123
     */
    public function token(Request $request)
    {
        $u = Auth::guard('isletmeyonetim-api')->user();
        if (!$u) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $hedefler = self::hedefler();
        $hedef = $request->input('hedef', 'kontor');
        if (!isset($hedefler[$hedef])) {
            $hedef = 'kontor';
        }

        $sube = (int) $request->input('sube', 0);

        // Salon gercekten bu yetkiliye ait mi? (sube verildiyse dogrula)
        if ($sube > 0) {
            $sahip = $u->yetkili_olunan_isletmeler->pluck('salon_id')->contains($sube);
            if (!$sahip) {
                return response()->json(['ok' => false, 'error' => 'salon-yetkisiz'], 403);
            }
        }

        $url = URL::temporarySignedRoute(
            'mobil.webview.giris',
            now()->addMinutes(2),
            ['uid' => $u->id, 'hedef' => $hedef, 'sube' => $sube]
        );

        return response()->json([
            'ok'         => true,
            'url'        => $url,
            'expires_in' => 120,
        ]);
    }

    /**
     * Web ('web' middleware, session var): imzayi dogrula, oturum ac, hedefe git.
     * GET /mobil/webview-giris?uid=..&hedef=..&sube=..&expires=..&signature=..
     */
    public function giris(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response(
                'Baglantinin suresi dolmus veya gecersiz. Lutfen uygulamadan tekrar acin.',
                403
            );
        }

        $u = IsletmeYetkilileri::find((int) $request->query('uid'));
        if (!$u) {
            return response('Kullanici bulunamadi.', 404);
        }

        Auth::guard('isletmeyonetim')->login($u);

        $hedefler = self::hedefler();
        $hedef = $request->query('hedef', 'kontor');
        $path = isset($hedefler[$hedef]) ? $hedefler[$hedef] : $hedefler['kontor'];

        $sube = (int) $request->query('sube', 0);
        if ($sube > 0) {
            $path .= '?sube=' . $sube;
        }

        return redirect($path);
    }
}
