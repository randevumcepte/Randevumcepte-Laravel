<?php

namespace App\Http\Controllers;

use App\GoogleCalendarBaglanti;
use App\GoogleCalendarEventEslemesi;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Google Calendar OAuth akis + baglanti yonetimi.
 * Her isletme yetkilisi kendi Google hesabini baglar/cozer.
 */
class GoogleCalendarController extends Controller
{
    protected $gc;

    public function __construct(GoogleCalendarService $gc)
    {
        $this->gc = $gc;
    }

    /** State'i HMAC ile imzala — session'a baglimlilik yok (OAuth sirasinda session rotate olabilir). */
    protected function imzaliState($payload)
    {
        $json = json_encode($payload);
        $sig  = hash_hmac('sha256', $json, config('app.key'));
        return rtrim(strtr(base64_encode($json . '|' . $sig), '+/', '-_'), '=');
    }

    protected function stateCoz($state)
    {
        $raw = base64_decode(strtr($state, '-_', '+/'));
        if (!$raw || strpos($raw, '|') === false) return null;
        $parts = explode('|', $raw, 2);
        if (count($parts) !== 2) return null;
        list($json, $sig) = $parts;
        $hesap = hash_hmac('sha256', $json, config('app.key'));
        if (!hash_equals($hesap, $sig)) return null;
        $data = json_decode($json, true);
        if (!is_array($data)) return null;
        return $data;
    }

    /** 1) Baglat: Google OAuth consent sayfasina redirect. */
    public function baglat(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return redirect('/');
        $salonId = (int) $request->sube;
        if (!$salonId) return redirect()->back()->with('hata', 'Sube ID gerekli');

        // Session'a yazmiyoruz — HMAC imzali state kendi kendini dogrular (session rotate'ten etkilenmez)
        $state = $this->imzaliState([
            'y' => $u->id, 's' => $salonId,
            'n' => bin2hex(random_bytes(8)),
            't' => time(),
        ]);
        return redirect($this->gc->authUrl($state));
    }

    protected function hataGeri($salonId, $err)
    {
        $sube = $salonId ? '&sube=' . (int)$salonId : '';
        // p=entegrasyonlar: ayarlar.blade.php $_GET['p'] bekliyor (undefined index olmasin)
        return redirect('/isletmeyonetim/ayarlar?p=entegrasyonlar' . $sube . '&google_err=' . urlencode($err));
    }

    /** 2) Callback: Google'dan donen `code`'u token'a cevir, baglantiyi DB'ye yaz. */
    public function callback(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        $state = $request->state;
        $sd = $state ? $this->stateCoz($state) : null;
        $salonId = (is_array($sd) && !empty($sd['s'])) ? (int)$sd['s'] : null;

        if (!$u) return redirect('/'); // login yoksa degil, akis anlamsiz

        if ($request->has('error')) {
            return $this->hataGeri($salonId, $request->error);
        }
        $code = $request->code;
        if (!$code || !$state) {
            return $this->hataGeri($salonId, 'eksik_param');
        }
        if (!is_array($sd) || empty($sd['y']) || empty($sd['s']) || empty($sd['n'])) {
            return $this->hataGeri($salonId, 'bozuk_state');
        }
        // State HMAC imzali — session gerekmez. Sadece zaman asimi kontrolu (10dk).
        if (!isset($sd['t']) || (time() - (int)$sd['t']) > 600) {
            return $this->hataGeri($salonId, 'state_sureli_doldu');
        }
        // Login olan kullanici state'teki yetkili ile eslesmeli (baska biri baglamasin)
        if ((int) $sd['y'] !== (int) $u->id) {
            return $this->hataGeri($salonId, 'yetkisiz');
        }

        try {
            $t = $this->gc->exchangeCode($code);
            $email = $this->gc->fetchUserEmail($t['access_token']);
        } catch (\Exception $e) {
            \Log::error('[GoogleCalendar] exchange hata', ['hata' => $e->getMessage()]);
            return $this->hataGeri($salonId, 'token_hata');
        }

        $expiresAt = Carbon::now()->addSeconds(((int)($t['expires_in'] ?? 3600)) - 60);

        $bag = GoogleCalendarBaglanti::where('yetkili_id', $u->id)
            ->where('salon_id', $sd['s'])->first();
        if (!$bag) $bag = new GoogleCalendarBaglanti();
        $bag->yetkili_id    = $u->id;
        $bag->salon_id      = (int) $sd['s'];
        $bag->google_email  = $email ?: ($bag->google_email ?? '');
        $bag->calendar_id   = $bag->calendar_id ?: 'primary';
        $bag->access_token  = $t['access_token'];
        if (!empty($t['refresh_token'])) {
            $bag->refresh_token = $t['refresh_token'];
        }
        $bag->expires_at    = $expiresAt;
        $bag->scope         = $t['scope'] ?? null;
        $bag->aktif         = 1;
        $bag->son_hata_zamani = null;
        $bag->son_hata_mesaji = null;
        $bag->save();

        // BACKFILL: ilk baglantida yakin tarihli mevcut randevulari Google'a aktar
        // (daha fazlasi icin kullanici panelden 'Tumunu aktar' butonuyla devam eder).
        $bfOzet = '';
        try {
            $bf = $this->gc->backfillPersonel($bag, 7, 100); // son 7 gun + ileri, 100 kayit limit
            if (!empty($bf['personel_yok'])) {
                $bfOzet = '&bf_pers=0';
            } else {
                $bfOzet = '&bf_sync=' . (int)$bf['sync'] . '&bf_hata=' . (int)$bf['hata'];
            }
        } catch (\Exception $e) {
            \Log::warning('[GoogleCalendar] callback backfill hata', ['hata' => $e->getMessage()]);
            $bfOzet = '&bf_err=1';
        }

        return redirect('/isletmeyonetim/ayarlar?p=entegrasyonlar&sube=' . $sd['s'] . '&google_ok=1' . $bfOzet);
    }

    /** Manuel backfill: 'Mevcut randevularimi Google'a aktar' butonu. */
    public function aktar(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return response()->json(['hata' => 'yetkisiz'], 401);
        $salonId = (int) $request->sube;
        $bag = GoogleCalendarBaglanti::where('yetkili_id', $u->id)
            ->where('salon_id', $salonId)->where('aktif', 1)->first();
        if (!$bag) return response()->json(['hata' => 'Baglanti yok'], 400);

        // Kapsam: 'gecmis' (90 gun) veya 'ileri' (yalniz bugun ve sonrasi)
        $kapsamGun = $request->kapsam === 'gecmis' ? 90 : null;
        $limit = min(300, max(50, (int)($request->limit ?: 150)));

        try {
            $sonuc = $this->gc->backfillPersonel($bag, $kapsamGun, $limit);
            return response()->json(['ok' => true] + $sonuc);
        } catch (\Exception $e) {
            return response()->json(['hata' => $e->getMessage()], 500);
        }
    }

    /** 3) Coz: baglantiyi deaktive et (token revoke isteyen Google'a opsiyonel). */
    public function coz(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return response()->json(['hata' => 'yetkisiz'], 401);
        $salonId = (int) $request->sube;

        $bag = GoogleCalendarBaglanti::where('yetkili_id', $u->id)
            ->where('salon_id', $salonId)->first();
        if (!$bag) return response()->json(['ok' => true]);

        // Google tarafinda token'i revoke et (best-effort)
        try {
            $http = new \GuzzleHttp\Client(['timeout' => 5, 'http_errors' => false]);
            $http->post('https://oauth2.googleapis.com/revoke', [
                'form_params' => ['token' => $bag->refresh_token ?: $bag->access_token],
            ]);
        } catch (\Exception $e) { /* ignore */ }

        // Esleme kayitlarini da sil (Google'daki eventleri sillmek kullanici tercihi — bu akisla iz birakmayalim)
        GoogleCalendarEventEslemesi::where('baglanti_id', $bag->id)->delete();
        $bag->delete();
        return response()->json(['ok' => true]);
    }

    /** Durum sorgu: AJAX ile ayarlar sekmesinde "Bagli: email" veya "Bagli degil" gostermek icin. */
    public function durum(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return response()->json(['hata' => 'yetkisiz'], 401);
        $salonId = (int) $request->sube;
        $bag = GoogleCalendarBaglanti::where('yetkili_id', $u->id)
            ->where('salon_id', $salonId)->first();
        if (!$bag) return response()->json(['bagli' => false]);
        return response()->json([
            'bagli' => true,
            'email' => $bag->google_email,
            'son_hata' => $bag->son_hata_mesaji,
            'son_hata_zamani' => $bag->son_hata_zamani ? $bag->son_hata_zamani->toDateTimeString() : null,
        ]);
    }
}
