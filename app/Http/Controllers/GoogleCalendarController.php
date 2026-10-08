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

    /** 1) Baglat: Google OAuth consent sayfasina redirect. */
    public function baglat(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return redirect('/');
        $salonId = (int) $request->sube;
        if (!$salonId) return redirect()->back()->with('hata', 'Sube ID gerekli');

        // state: CSRF korumasi + callback'te kullaniciyi tanimak icin yetkili_id+salon_id+random nonce
        $nonce = bin2hex(random_bytes(8));
        $state = base64_encode(json_encode([
            'y' => $u->id, 's' => $salonId, 'n' => $nonce, 't' => time(),
        ]));
        session(['google_oauth_state' => $nonce, 'google_oauth_state_expires' => time() + 600]);

        return redirect($this->gc->authUrl($state));
    }

    /** 2) Callback: Google'dan donen `code`'u token'a cevir, baglantiyi DB'ye yaz. */
    public function callback(Request $request)
    {
        $u = Auth::guard('isletmeyonetim')->user();
        if (!$u) return redirect('/');

        if ($request->has('error')) {
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=' . urlencode($request->error));
        }
        $code  = $request->code;
        $state = $request->state;
        if (!$code || !$state) {
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=eksik_param');
        }
        $sd = @json_decode(base64_decode($state), true);
        if (!is_array($sd) || empty($sd['y']) || empty($sd['s']) || empty($sd['n'])) {
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=bozuk_state');
        }
        // State CSRF: session'daki nonce ile eslesmeli, 10dk icinde kullanilmali
        $sessionNonce = session('google_oauth_state');
        $sessionExp   = session('google_oauth_state_expires', 0);
        if ($sessionNonce !== $sd['n'] || time() > $sessionExp) {
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=state_uyusmazlik');
        }
        session()->forget(['google_oauth_state', 'google_oauth_state_expires']);

        // Login olan kullanici state'teki yetkili ile eslesmeli (baska biri baglamasin)
        if ((int) $sd['y'] !== (int) $u->id) {
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=yetkisiz');
        }

        try {
            $t = $this->gc->exchangeCode($code);
            $email = $this->gc->fetchUserEmail($t['access_token']);
        } catch (\Exception $e) {
            \Log::error('[GoogleCalendar] exchange hata', ['hata' => $e->getMessage()]);
            return redirect('/isletmeyonetim/ayarlar?sekme=entegrasyonlar&google_err=token_hata');
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

        return redirect('/isletmeyonetim/ayarlar?sube=' . $sd['s'] . '&sekme=entegrasyonlar&google_ok=1');
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
