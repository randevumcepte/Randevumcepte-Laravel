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
        $platform = $request->input('platform') === 'android' ? 'android' : 'ios';
        $token = $this->tokenTemizle((string) $request->input('token'));
        $bundle = substr(trim((string) $request->input('bundle')), 0, 150);
        // iOS: bundle zorunlu (APNs topic). Android: bos olabilir (FCM projesi
        // bildirim_kimlikleri.app_bundle / salon firebase_profile'dan cozulur).
        if (strlen($token) < 32 || ($platform === 'ios' && $bundle === '')) {
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
            'platform' => $platform,
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
        $token = $this->tokenTemizle((string) $request->input('token'));
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
            ->select('token', 'bundle', 'platform')->get();
        // Arayan kayitli musteriyse adi (CallKit/iOS push ve Dial callerid adi icin).
        $arayan = $this->musteriAdi((string) $request->input('numara'), $dahili);
        return response()->json(['ok' => true, 'hedefler' => $hedefler, 'arayan' => $arayan]);
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
        $token = $this->tokenTemizle((string) $request->input('token'));
        $silinen = DB::table('softphone_voip_tokenlar')->where('token', $token)->delete();
        Log::info('softphone voip token silindi (APNs gecersiz)', ['silinen' => $silinen]);
        return response()->json(['ok' => true, 'silinen' => $silinen]);
    }

    /**
     * Android gelen cagri: dahilinin Android token'larina data-only, HIGH
     * oncelikli FCM yollar (bildirim blogu YOK: uygulamanin arka plan
     * handler'i tam ekran gelen cagriyi kendisi gosterir).
     */
    public function santralAndroidPush(Request $request)
    {
        if (!$this->santralDogrula($request)) {
            return response()->json(['ok' => false], 403);
        }
        $dahili = preg_replace('/[^0-9]/', '', (string) $request->input('dahili'));
        $uuid = strtolower((string) $request->input('uuid'));
        if ($dahili === '' || !preg_match('/^[0-9a-f-]{36}$/', $uuid)) {
            return response()->json(['ok' => false], 422);
        }
        $satirlar = DB::table('softphone_voip_tokenlar')
            ->where('dahili', $dahili)->where('platform', 'android')->get();

        $veri = [
            'kaynak' => 'softphone_cagri',
            'uuid' => $uuid,
            'numara' => substr((string) $request->input('numara'), 0, 40),
            'arayan' => substr((string) $request->input('arayan'), 0, 80),
        ];
        // Kayitli musteri adi santralin gonderdigi callerid adindan onceliklidir.
        $musteri = $this->musteriAdi($veri['numara'], $dahili);
        if ($musteri !== '') {
            $veri['arayan'] = substr($musteri, 0, 80);
        }
        $gonderilen = 0;
        $sonuclar = [];
        foreach ($satirlar as $r) {
            try {
                $dosya = $this->firebaseDosyasi($r);
                $proje = json_decode(file_get_contents(storage_path($dosya)), true)['project_id'];
                $yanit = $this->http()->post(
                    "https://fcm.googleapis.com/v1/projects/{$proje}/messages:send",
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->fcmErisim($dosya),
                            'Content-Type' => 'application/json',
                        ],
                        'json' => ['message' => [
                            'token' => $r->token,
                            'data' => $veri,
                            'android' => ['priority' => 'HIGH', 'ttl' => '30s'],
                        ]],
                        'http_errors' => false,
                    ]
                );
                $kod = $yanit->getStatusCode();
                $govde = (string) $yanit->getBody();
                if ($kod === 200) {
                    $gonderilen++;
                } elseif ($kod === 404 || strpos($govde, 'UNREGISTERED') !== false
                    || strpos($govde, 'SENDER_ID_MISMATCH') !== false) {
                    DB::table('softphone_voip_tokenlar')->where('id', $r->id)->delete();
                }
                $sonuclar[] = ['kod' => $kod, 'neden' => $kod === 200 ? '' : substr($govde, 0, 200)];
            } catch (\Throwable $e) {
                $sonuclar[] = ['kod' => 0, 'neden' => $e->getMessage()];
            }
        }
        if ($gonderilen === 0 && count($satirlar)) {
            Log::warning('softphone android push gonderilemedi', ['dahili' => $dahili, 'sonuclar' => $sonuclar]);
        }
        return response()->json(['ok' => true, 'gonderilen' => $gonderilen, 'sonuclar' => $sonuclar]);
    }

    // ------------------------------------------------------------------

    /**
     * Arayan numara, dahilinin calistigi salon(lar)da kayitli musteriyse adi;
     * degilse ''. Numara son 10 hane ile eslesir (0532.., 90532.., +90532..).
     */
    private function musteriAdi($numara, $dahili)
    {
        $hane = preg_replace('/[^0-9]/', '', (string) $numara);
        if (strlen($hane) < 10) {
            return '';
        }
        $son10 = substr($hane, -10);
        try {
            return (string) Cache::remember('sp_musteri_adi_' . $dahili . '_' . $son10, 5, function () use ($dahili, $son10) {
                $salonlar = DB::table('softphone_voip_tokenlar')->where('dahili', $dahili)
                    ->whereNotNull('salon_id')->distinct()->pluck('salon_id')->all();
                if (!$salonlar) {
                    $salonlar = Personeller::where('dahili_no_webrtc', $dahili)->pluck('salon_id')->all();
                }
                if (!$salonlar) {
                    return '';
                }
                $ad = DB::table('musteri_portfoy')
                    ->join('users', 'musteri_portfoy.user_id', '=', 'users.id')
                    ->whereIn('musteri_portfoy.salon_id', $salonlar)
                    ->where('users.cep_telefon', 'like', '%' . $son10)
                    ->orderBy('musteri_portfoy.id', 'desc')
                    ->value('users.name');
                return trim((string) $ad);
            });
        } catch (\Throwable $e) {
            Log::warning('softphone musteri adi cozulemedi', ['hata' => $e->getMessage()]);
            return '';
        }
    }

    /** iOS hex, Android FCM token (harf/rakam : _ -). */
    private function tokenTemizle($t)
    {
        return substr(preg_replace('/[^0-9A-Za-z:_\-]/', '', $t), 0, 200);
    }

    /**
     * Token'in Firebase projesi: NotificationService ile ayni kural.
     * 1) token'in bildirim_kimlikleri.app_bundle'i firebase_bundle_projects'te varsa o,
     * 2) salonun firebase_profile'i, 3) varsayilan.
     */
    private function firebaseDosyasi($r)
    {
        $bundleMap = config('firebase_bundle_projects', []);
        $bundle = $r->bundle ?: DB::table('bildirim_kimlikleri')->where('bildirim_id', $r->token)->value('app_bundle');
        if ($bundle && !empty($bundleMap[$bundle])) {
            return $bundleMap[$bundle];
        }
        if ($r->salon_id) {
            $profil = DB::table('salonlar')->where('id', $r->salon_id)->value('firebase_profile');
            if ($profil && config("firebase_projects.{$profil}")) {
                return config("firebase_projects.{$profil}");
            }
        }
        return config('firebase_projects.default', 'app/firebase/randevumcepte-uygulamala-5ff4d-8a85c43832c1.json');
    }

    private function http()
    {
        static $c = null;
        if ($c === null) {
            $c = new \GuzzleHttp\Client(['connect_timeout' => 3, 'timeout' => 6]);
        }
        return $c;
    }

    /** FCM OAuth erisim token'i; NotificationService ile ayni cache anahtari (paylasilir). */
    private function fcmErisim($dosya)
    {
        $anahtar = 'fcm_erisim_token:' . md5($dosya);
        $kayitli = Cache::get($anahtar);
        if (is_array($kayitli) && ($kayitli['exp'] ?? 0) > time() + 60) {
            return $kayitli['token'];
        }
        $json = json_decode(file_get_contents(storage_path($dosya)), true);
        $b64 = function ($d) {
            return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        };
        $simdi = time();
        $veri = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . $b64(json_encode([
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $json['token_uri'],
            'iat' => $simdi,
            'exp' => $simdi + 3600,
        ]));
        openssl_sign($veri, $imza, $json['private_key'], OPENSSL_ALGO_SHA256);
        $yanit = $this->http()->post($json['token_uri'], ['form_params' => [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $veri . '.' . $b64($imza),
        ]]);
        $govde = json_decode((string) $yanit->getBody(), true);
        $deger = ['token' => $govde['access_token'], 'exp' => $simdi + 3550];
        Cache::put($anahtar, $deger, 55); // L5.6: dakika
        return $deger['token'];
    }

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
