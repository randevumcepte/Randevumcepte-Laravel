<?php

namespace App\Http\Controllers;

use App\Personeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mobil softphone (rc_softphone) - iOS VoIP push (PushKit) uclari.
 *
 * Akis (uygulama kapali/arka plandayken gelen cagri):
 *  1. Uygulama PushKit VoIP token'ini dahili + sifre ile kaydeder (voip-token).
 *  2. Santral dahiliyi cagirmadan once (softphone-push context'i,
 *     softphoneVoipPush.php AGI) bu dahilinin token'larini ister
 *     (santral/hedefler), APNs'e VoIP push yollar.
 *  3. Push uygulamayi uyandirir, CallKit ekrani acilir, uygulama santrale
 *     register olur ve "hazir" der (hazir). Reddedildiyse "red".
 *  4. Santral (santral/hazir-mi) cevabi gorunce dahiliyi Dial eder.
 *
 * Kimlik:
 *  - Mobil uclar: salon_personelleri.dahili_no_webrtc + dahili_sifre_webrtc
 *    (uygulama bunlarla zaten santrale register oluyor; mobil token yok).
 *  - Santral uclari: SOFTPHONE_SANTRAL_ANAHTAR (.env) paylasilan anahtar.
 *
 * StoreAdminController'dan TURETILMEZ (session guard tuzagi): bagimsiz.
 * Tablo: softphone_voip_tokenlar (SQL: softphone_voip_tokenlar.sql).
 */
class SoftphoneApiController extends Controller
{
    /** Ayni dahili icin tutulacak en fazla cihaz. */
    const DAHILI_BASINA_CIHAZ = 5;

    // ------------------------------------------------------------------
    // Mobil uclar (dahili + sifre)
    // ------------------------------------------------------------------

    public function voipTokenKaydet(Request $request)
    {
        $personel = $this->dahiliDogrula($request);
        if (!$personel) {
            return response()->json(['ok' => false, 'mesaj' => 'Dahili dogrulanamadi'], 401);
        }
        $token = preg_replace('/[^0-9a-fA-F]/', '', (string) $request->input('token'));
        $bundle = substr(trim((string) $request->input('bundle')), 0, 150);
        if (strlen($token) < 32 || $bundle === '') {
            return response()->json(['ok' => false, 'mesaj' => 'Token/bundle eksik'], 422);
        }
        $dahili = (string) $personel->dahili_no_webrtc;
        $simdi = date('Y-m-d H:i:s');

        // Token tekil: cihaz baska dahiliyle giris yaptiysa kayit ona tasinir.
        DB::table('softphone_voip_tokenlar')->where('token', $token)->delete();
        DB::table('softphone_voip_tokenlar')->insert([
            'dahili' => $dahili,
            'salon_id' => $personel->salon_id,
            'personel_id' => $personel->id,
            'token' => $token,
            'bundle' => $bundle,
            'platform' => substr((string) $request->input('platform', 'ios'), 0, 10),
            'created_at' => $simdi,
            'updated_at' => $simdi,
        ]);

        // Eski cihazlari buda.
        $fazla = DB::table('softphone_voip_tokenlar')->where('dahili', $dahili)
            ->orderBy('updated_at', 'desc')->skip(self::DAHILI_BASINA_CIHAZ)->take(100)->pluck('id');
        if (count($fazla)) {
            DB::table('softphone_voip_tokenlar')->whereIn('id', $fazla)->delete();
        }
        return response()->json(['ok' => true]);
    }

    public function voipTokenSil(Request $request)
    {
        $personel = $this->dahiliDogrula($request);
        if (!$personel) {
            return response()->json(['ok' => false], 401);
        }
        $token = preg_replace('/[^0-9a-fA-F]/', '', (string) $request->input('token'));
        DB::table('softphone_voip_tokenlar')
            ->where('dahili', (string) $personel->dahili_no_webrtc)
            ->where('token', $token)->delete();
        return response()->json(['ok' => true]);
    }

    /** Uygulama santrale register oldu ("hazir") ya da cagriyi reddetti ("red"). */
    public function hazir(Request $request)
    {
        $personel = $this->dahiliDogrula($request);
        if (!$personel) {
            return response()->json(['ok' => false], 401);
        }
        $uuid = strtolower((string) $request->input('uuid'));
        $durum = $request->input('durum') === 'red' ? 'red' : 'hazir';
        if (!preg_match('/^[0-9a-f-]{36}$/', $uuid)) {
            return response()->json(['ok' => false], 422);
        }
        // Laravel 5.6: 3. parametre DAKIKA.
        Cache::put('sp_hazir_' . $uuid, $durum . '|' . $personel->dahili_no_webrtc, 2);
        return response()->json(['ok' => true]);
    }

    // ------------------------------------------------------------------
    // Santral uclari (paylasilan anahtar)
    // ------------------------------------------------------------------

    /** Dahilinin VoIP push hedefleri: [{token, bundle}] */
    public function santralHedefler(Request $request)
    {
        if (!$this->santralDogrula($request)) {
            return response()->json(['ok' => false], 403);
        }
        $dahili = preg_replace('/[^0-9]/', '', (string) $request->input('dahili'));
        $hedefler = DB::table('softphone_voip_tokenlar')
            ->where('dahili', $dahili)
            ->where('updated_at', '>', date('Y-m-d H:i:s', strtotime('-60 days')))
            ->select('token', 'bundle')->get();
        return response()->json(['ok' => true, 'hedefler' => $hedefler]);
    }

    /** Santral bekleme dongusu: uygulama "hazir"/"red" dedi mi? */
    public function santralHazirMi(Request $request)
    {
        if (!$this->santralDogrula($request)) {
            return response()->json(['ok' => false], 403);
        }
        $uuid = strtolower((string) $request->input('uuid'));
        $deger = Cache::get('sp_hazir_' . $uuid);
        $durum = $deger ? explode('|', $deger)[0] : null;
        return response()->json(['ok' => true, 'durum' => $durum]);
    }

    /** APNs "Unregistered/BadDeviceToken" donen token'i sil. */
    public function santralTokenSil(Request $request)
    {
        if (!$this->santralDogrula($request)) {
            return response()->json(['ok' => false], 403);
        }
        $token = preg_replace('/[^0-9a-fA-F]/', '', (string) $request->input('token'));
        $silinen = DB::table('softphone_voip_tokenlar')->where('token', $token)->delete();
        Log::info('softphone voip token silindi (APNs gecersiz)', ['silinen' => $silinen]);
        return response()->json(['ok' => true, 'silinen' => $silinen]);
    }

    // ------------------------------------------------------------------

    private function dahiliDogrula(Request $request)
    {
        $dahili = trim((string) $request->input('dahili'));
        $sifre = (string) $request->input('sifre');
        if ($dahili === '' || $sifre === '') {
            return null;
        }
        $personel = Personeller::where('dahili_no_webrtc', $dahili)
            ->whereNotNull('dahili_sifre_webrtc')
            ->where('dahili_sifre_webrtc', '!=', '')
            ->get()
            ->first(function ($p) use ($sifre) {
                return hash_equals((string) $p->dahili_sifre_webrtc, $sifre);
            });
        return $personel;
    }

    private function santralDogrula(Request $request)
    {
        $beklenen = (string) config('services.softphone.santral_anahtar');
        $gelen = (string) $request->input('anahtar');
        return $beklenen !== '' && hash_equals($beklenen, $gelen);
    }
}
