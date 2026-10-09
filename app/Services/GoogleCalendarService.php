<?php

namespace App\Services;

use App\GoogleCalendarBaglanti;
use App\GoogleCalendarEventEslemesi;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Google Calendar entegrasyonu — one-way push (randevumcepte -> Google).
 * Her isletme personeli kendi Google hesabini OAuth2 ile baglar; randevu olusturulup/
 * guncellenip/silindiginde ilgili personelin Google Calendar'ina event dusurulur.
 *
 * .env:
 *   GOOGLE_CLIENT_ID=...
 *   GOOGLE_CLIENT_SECRET=...
 *   GOOGLE_REDIRECT_URI=https://app.randevumcepte.com.tr/isletmeyonetim/google/oauth/callback
 *
 * google/apiclient paketi KULLANILMADI — Guzzle ile direkt HTTP (PHP 7.4 uyumu + daha az bagimlilik).
 */
class GoogleCalendarService
{
    const AUTH_URL   = 'https://accounts.google.com/o/oauth2/v2/auth';
    const TOKEN_URL  = 'https://oauth2.googleapis.com/token';
    const USERINFO   = 'https://www.googleapis.com/oauth2/v2/userinfo';
    const CAL_BASE   = 'https://www.googleapis.com/calendar/v3';
    // calendar.events: tek takvimdeki event'leri yonetir (tum takvim listesi degil).
    // userinfo.email: hangi Google hesabinin baglandigini kaydetmek icin.
    const SCOPES     = 'https://www.googleapis.com/auth/calendar.events openid email';

    protected $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 15, 'http_errors' => false]);
    }

    /* ============ OAUTH ============ */

    public function clientId()     { return env('GOOGLE_CLIENT_ID'); }
    public function clientSecret() { return env('GOOGLE_CLIENT_SECRET'); }
    public function redirectUri()
    {
        $env = env('GOOGLE_REDIRECT_URI');
        if ($env) return $env;
        return url('/isletmeyonetim/google/oauth/callback');
    }

    /** OAuth consent sayfasi URL'si. `state` CSRF korumasi + callback'te kullaniciyi tanimak icin. */
    public function authUrl($state)
    {
        $params = [
            'client_id'     => $this->clientId(),
            'redirect_uri'  => $this->redirectUri(),
            'response_type' => 'code',
            'scope'         => self::SCOPES,
            'access_type'   => 'offline',    // refresh_token almak icin SART
            'prompt'        => 'consent',    // her bagin'da refresh_token donmesi icin
            'include_granted_scopes' => 'true',
            'state'         => $state,
        ];
        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /** Google'dan donen `code`'u access/refresh token'a cevirir. */
    public function exchangeCode($code)
    {
        $r = $this->http->post(self::TOKEN_URL, [
            'form_params' => [
                'code'          => $code,
                'client_id'     => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'redirect_uri'  => $this->redirectUri(),
                'grant_type'    => 'authorization_code',
            ],
        ]);
        $body = json_decode((string) $r->getBody(), true);
        if ($r->getStatusCode() !== 200 || !isset($body['access_token'])) {
            throw new \RuntimeException('Token alinamadi: ' . (string) $r->getBody());
        }
        return $body; // access_token, refresh_token, expires_in, scope, id_token
    }

    /** Access token'in sahibini (email) ogrenmek icin userinfo. */
    public function fetchUserEmail($accessToken)
    {
        $r = $this->http->get(self::USERINFO, [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
        ]);
        $b = json_decode((string) $r->getBody(), true);
        return $b['email'] ?? null;
    }

    /** Expire olmus access_token'i refresh_token ile tazeler, DB'ye yazar. */
    public function refreshIfNeeded(GoogleCalendarBaglanti $bag)
    {
        if ($bag->tokenGecerli()) return $bag;
        if (!$bag->refresh_token) {
            throw new \RuntimeException('refresh_token yok, yeniden baglanmak gerekli');
        }
        $r = $this->http->post(self::TOKEN_URL, [
            'form_params' => [
                'client_id'     => $this->clientId(),
                'client_secret' => $this->clientSecret(),
                'refresh_token' => $bag->refresh_token,
                'grant_type'    => 'refresh_token',
            ],
        ]);
        $b = json_decode((string) $r->getBody(), true);
        if ($r->getStatusCode() !== 200 || !isset($b['access_token'])) {
            throw new \RuntimeException('refresh basarisiz: ' . (string) $r->getBody());
        }
        $bag->access_token = $b['access_token'];
        $bag->expires_at   = Carbon::now()->addSeconds(((int)($b['expires_in'] ?? 3600)) - 60);
        if (!empty($b['refresh_token'])) {
            $bag->refresh_token = $b['refresh_token']; // Google bazen yeni refresh dondurur
        }
        $bag->save();
        return $bag;
    }

    /* ============ CALENDAR EVENT CRUD ============ */

    public function insertEvent(GoogleCalendarBaglanti $bag, array $event)
    {
        $this->refreshIfNeeded($bag);
        $url = self::CAL_BASE . '/calendars/' . rawurlencode($bag->calendar_id) . '/events';
        $r = $this->http->post($url, [
            'headers' => ['Authorization' => 'Bearer ' . $bag->access_token, 'Content-Type' => 'application/json'],
            'body'    => json_encode($event),
        ]);
        return $this->handleResp($r, $bag);
    }

    public function updateEvent(GoogleCalendarBaglanti $bag, $eventId, array $event)
    {
        $this->refreshIfNeeded($bag);
        $url = self::CAL_BASE . '/calendars/' . rawurlencode($bag->calendar_id) . '/events/' . rawurlencode($eventId);
        $r = $this->http->patch($url, [
            'headers' => ['Authorization' => 'Bearer ' . $bag->access_token, 'Content-Type' => 'application/json'],
            'body'    => json_encode($event),
        ]);
        return $this->handleResp($r, $bag);
    }

    public function deleteEvent(GoogleCalendarBaglanti $bag, $eventId)
    {
        $this->refreshIfNeeded($bag);
        $url = self::CAL_BASE . '/calendars/' . rawurlencode($bag->calendar_id) . '/events/' . rawurlencode($eventId);
        $r = $this->http->delete($url, [
            'headers' => ['Authorization' => 'Bearer ' . $bag->access_token],
        ]);
        // 404 (zaten silinmis) + 410 (gone) OK sayilir
        $code = $r->getStatusCode();
        if (in_array($code, [200, 204, 404, 410])) return true;
        return $this->handleResp($r, $bag);
    }

    protected function handleResp($r, GoogleCalendarBaglanti $bag)
    {
        $code = $r->getStatusCode();
        $body = (string) $r->getBody();
        if ($code >= 200 && $code < 300) {
            return json_decode($body, true);
        }
        // Hata — baglantiya logla, exception at
        $bag->son_hata_zamani = Carbon::now();
        $bag->son_hata_mesaji = substr('HTTP ' . $code . ' ' . $body, 0, 2000);
        $bag->save();
        throw new \RuntimeException('Google API hata: HTTP ' . $code . ' ' . $body);
    }

    /* ============ RANDEVU -> GOOGLE EVENT MAP ============ */

    /**
     * RandevuHizmetler satirindan Google event payload uretir.
     * - start/end: tarih + saat + sure_dk
     * - timezone: Europe/Istanbul (randevumcepte TR'de)
     * - summary: musteri adi + hizmet
     * - description: not + salon
     */
    public function buildEventFromRandevuHizmet(\App\RandevuHizmetler $rh)
    {
        $r = $rh->randevu;
        if (!$r) return null;
        $tz = 'Europe/Istanbul';
        $baslangicSaat = $rh->saat ?: $r->saat;
        $start = Carbon::parse($r->tarih . ' ' . $baslangicSaat, $tz);
        $sure  = (int) ($rh->sure_dk ?: 15);
        $end   = (clone $start)->addMinutes($sure);

        $musteriAd = $r->users->name ?? ('Musteri #' . $r->user_id);
        $hizmetAd  = $rh->hizmetler->hizmet_adi ?? '';
        $summary   = trim($musteriAd . ($hizmetAd ? (' — ' . $hizmetAd) : ''));

        $desc = [];
        if ($r->notlar)        $desc[] = 'Musteri notu: ' . $r->notlar;
        if ($r->personel_notu) $desc[] = 'Personel notu: ' . $r->personel_notu;
        $desc[] = 'Randevu #' . $r->id;

        return [
            'summary'     => $summary,
            'description' => implode("\n", $desc),
            'start'       => ['dateTime' => $start->toRfc3339String(), 'timeZone' => $tz],
            'end'         => ['dateTime' => $end->toRfc3339String(),   'timeZone' => $tz],
            // extendedProperties: Randevumcepte tarafindan yazildigini isaretle
            'extendedProperties' => [
                'private' => [
                    'randevumcepte_randevu_id'       => (string) $r->id,
                    'randevumcepte_randevu_hizmet_id'=> (string) $rh->id,
                ],
            ],
            'source' => ['title' => 'Randevumcepte', 'url' => url('/')],
        ];
    }

    /**
     * Bir RandevuHizmet satirini personelin Google Takvimine yaz (insert veya update).
     * Esleme varsa update, yoksa insert + esleme kaydet.
     */
    public function syncRandevuHizmet(\App\RandevuHizmetler $rh)
    {
        $r = $rh->randevu;
        if (!$r || !$rh->personel_id) return;

        // RH personelinin yetkili_id'si (profil Google baglantisi burada)
        $yetkiliId = \DB::table('salon_personelleri')->where('id', $rh->personel_id)->value('yetkili_id');
        if (!$yetkiliId) return;

        $bag = GoogleCalendarBaglanti::where('yetkili_id', $yetkiliId)
            ->where('salon_id', $r->salon_id)
            ->where('aktif', 1)
            ->first();
        if (!$bag) return; // bu personel baglamamis

        $event = $this->buildEventFromRandevuHizmet($rh);
        if (!$event) return;

        $esleme = GoogleCalendarEventEslemesi::where('baglanti_id', $bag->id)
            ->where('randevu_hizmet_id', $rh->id)->first();

        try {
            if ($esleme) {
                $this->updateEvent($bag, $esleme->google_event_id, $event);
                $esleme->son_sync_zamani = Carbon::now();
                $esleme->save();
            } else {
                $created = $this->insertEvent($bag, $event);
                if (!empty($created['id'])) {
                    GoogleCalendarEventEslemesi::create([
                        'baglanti_id'       => $bag->id,
                        'randevu_hizmet_id' => $rh->id,
                        'google_event_id'   => $created['id'],
                        'son_sync_zamani'   => Carbon::now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('[GoogleCalendar] sync hata', [
                'rh_id' => $rh->id, 'baglanti_id' => $bag->id, 'hata' => $e->getMessage(),
            ]);
        }
    }

    /**
     * BACKFILL: Baglanti sonrasi (veya manuel tetik) bu personelin mevcut
     * randevularini Google'a aktar. Idempotent — zaten eslesen varsa atlar.
     *
     * $kapsamGun: kac gun oncesinden baslayarak aktar (default 7 gun geriden).
     *             null -> sadece bugun ve sonrasi.
     * $limit: tek seferde en fazla kac RH islenir (Google rate-limit + browser timeout koruma).
     */
    public function backfillPersonel(GoogleCalendarBaglanti $bag, $kapsamGun = 7, $limit = 200)
    {
        $this->refreshIfNeeded($bag);

        // Bu yetkiliye bagli SP (salon_personelleri) kayitlari (bir yetkili birden fazla
        // kayitli olabilir ama ayni salon icinde genelde tek)
        $personelIds = \DB::table('salon_personelleri')
            ->where('yetkili_id', $bag->yetkili_id)
            ->where('salon_id', $bag->salon_id)
            ->pluck('id')->all();
        if (empty($personelIds)) {
            return ['sync' => 0, 'skip' => 0, 'hata' => 0, 'personel_yok' => true];
        }

        $tarihBas = $kapsamGun !== null
            ? \Carbon\Carbon::now()->subDays((int)$kapsamGun)->format('Y-m-d')
            : \Carbon\Carbon::now()->format('Y-m-d');

        // Zaten eslesmis RH id'leri — tekrar sorgulamayalim
        $eslesenIds = \App\GoogleCalendarEventEslemesi::where('baglanti_id', $bag->id)
            ->pluck('randevu_hizmet_id')->all();

        $rhler = \App\RandevuHizmetler::with(['randevu.users', 'hizmetler'])
            ->whereIn('personel_id', $personelIds)
            ->whereNotIn('id', $eslesenIds ?: [0])
            ->whereHas('randevu', function($q) use ($bag, $tarihBas){
                $q->where('salon_id', $bag->salon_id)
                  ->where('tarih', '>=', $tarihBas)
                  ->where('durum', '!=', 2); // iptal degil
            })
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();

        $sync = 0; $hata = 0;
        foreach ($rhler as $rh) {
            try {
                // syncRandevuHizmet esleme kontrolunu kendisi yapar; cift yazim riski yok
                $this->syncRandevuHizmet($rh);
                $sync++;
            } catch (\Exception $e) {
                $hata++;
                \Log::warning('[GoogleCalendar/backfill] hata', [
                    'rh_id' => $rh->id, 'bag_id' => $bag->id, 'hata' => $e->getMessage(),
                ]);
            }
        }
        return ['sync' => $sync, 'skip' => count($eslesenIds), 'hata' => $hata, 'kalan_tahmini' => count($rhler) >= $limit ? '>'.$limit : 0];
    }

    /** Randevu silinirken/iptal edilirken Google event'ini sil. */
    public function removeRandevuHizmet($rhId)
    {
        $eslemeler = GoogleCalendarEventEslemesi::where('randevu_hizmet_id', $rhId)->get();
        foreach ($eslemeler as $es) {
            $bag = GoogleCalendarBaglanti::find($es->baglanti_id);
            if (!$bag) { $es->delete(); continue; }
            try {
                $this->deleteEvent($bag, $es->google_event_id);
            } catch (\Exception $e) {
                \Log::warning('[GoogleCalendar] delete hata', ['rh_id' => $rhId, 'hata' => $e->getMessage()]);
            }
            $es->delete();
        }
    }
}
