<?php

namespace App\Services;

use App\Salonlar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $baseUrl;
    protected $token;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('whatsapp.service_url'), '/');
        $this->token = config('whatsapp.service_token');
        $this->timeout = (int) config('whatsapp.request_timeout', 10);
    }

    public function startSession($salonId)
    {
        return $this->request('POST', "/session/{$salonId}/start");
    }

    public function status($salonId)
    {
        return $this->request('GET', "/session/{$salonId}/status");
    }

    public function qr($salonId)
    {
        return $this->request('GET', "/session/{$salonId}/qr");
    }

    public function logout($salonId)
    {
        return $this->request('POST', "/session/{$salonId}/logout");
    }

    /**
     * Salon nesnesi OLMADAN, dogrudan bir oturum id'sinden mesaj gonderir.
     * Sistem bildirimleri (salon-bagimsiz "sistem" oturumu) icin kullanilir.
     */
    public function sendRaw($sessionId, $to, $message)
    {
        $to = preg_replace('/[^0-9]/', '', (string) $to);
        return $this->request('POST', "/session/{$sessionId}/send", [
            'to' => $to,
            'message' => $message,
            'warmupStart' => null,
            'dailyLimit' => 9999,
            'urgent' => true,
        ]);
    }

    /**
     * Hatırlatma gönderir. Salon `whatsapp_saglayici`'ya göre Baileys veya Cloud API.
     *
     * @param array|null $templateCtx Cloud API kullanırken: ['key' => '1gun|yaklasan|iptal|guncelleme', 'params' => [...]]
     * @param string|null $gonderimTipi Log'a yazilacak tip etiketi: 'randevu_hatirlatma',
     *        'personel_hatirlatma', 'sifre_kodu', 'anket', 'iptal_bildirim', 'guncelleme_bildirim',
     *        'manuel', 'kampanya' vb. Bos birakilirsa null kaydedilir.
     */
    public function sendReminder(Salonlar $salon, $to, $message, $randevuId = null, $userId = null, $templateCtx = null, $urgent = false, $gonderimTipi = null)
    {
        $normalized = $this->normalizePhone($to);
        if (!$normalized) {
            Log::warning('[WA] invalid-phone', ['salon_id' => $salon->id, 'telefon' => $to]);
            return ['ok' => false, 'error' => 'invalid-phone'];
        }

        if (!$this->canSendToday($salon)) {
            return ['ok' => false, 'error' => 'daily-cap-reached'];
        }

        // urgent (sifre/OTP) mesajlar business hours kisitlamasini bypass eder
        if (!$urgent && !$this->withinBusinessHours()) {
            return ['ok' => false, 'error' => 'outside-business-hours'];
        }

        $saglayici = $salon->whatsapp_saglayici ?? 'baileys';

        if ($saglayici === 'cloud_api') {
            return $this->sendViaCloudApi($salon, $normalized, $message, $randevuId, $userId, $templateCtx, $gonderimTipi);
        }

        return $this->sendViaBaileys($salon, $normalized, $message, $randevuId, $userId, $urgent, $gonderimTipi);
    }

    /**
     * Anlik WhatsApp gonderimi — sifre/OTP gibi acil mesajlar icin.
     * Anti-ban delay (60-120s), typing simulation ve business hours kontrolu atlanir.
     * Ban riski yuksek oldugundan sadece zorunlu durumlarda kullanilmali.
     */
    public function sendUrgent(Salonlar $salon, $to, $message, $userId = null, $gonderimTipi = 'sifre_kodu')
    {
        return $this->sendReminder($salon, $to, $message, null, $userId, null, true, $gonderimTipi);
    }

    protected function sendViaBaileys(Salonlar $salon, $to, $message, $randevuId, $userId, $urgent = false, $gonderimTipi = null)
    {
        $logId = $this->logPending($salon->id, $userId, $randevuId, $to, $message, 'baileys', $gonderimTipi);

        $response = $this->request('POST', "/session/{$salon->id}/send", [
            'to' => $to,
            'message' => $message,
            'warmupStart' => optional($salon->whatsapp_warmup_baslangic)->toIso8601String()
                ?: optional($salon->whatsapp_baglanti_tarihi)->toIso8601String(),
            'dailyLimit' => (int) ($salon->whatsapp_gunluk_limit ?: config('whatsapp.default_daily_limit', 150)),
            'logId' => $logId,
            'urgent' => $urgent,
        ]);

        // 202 Accepted = kuyruğa alındı, webhook ile sent/failed bildirecek
        if (($response['status'] ?? 0) === 202) {
            return ['ok' => true, 'queued' => true, 'logId' => $logId, 'provider' => 'baileys'];
        }

        // 4xx/5xx = hemen başarısız, SMS fallback tetiklenmeli
        $err = $response['error'] ?? ($response['body']['error'] ?? 'unknown');
        $this->markFailed($logId, $err);
        $this->markSessionLostIfNeeded($salon, $err);
        return ['ok' => false, 'error' => $err, 'status' => $response['status'] ?? 0, 'logId' => $logId, 'provider' => 'baileys'];
    }

    /**
     * Bridge "unauthorized / loggedout / session-not-found" gibi bir hata dondurduyse
     * salonun WhatsApp durumunu DB'de "disconnected" yap. Aksi halde panel "Bagli"
     * gostermeye devam eder, her mesajda bos yere WA denenir ve fallback gecikir.
     */
    public function markSessionLostIfNeeded(Salonlar $salon, $error)
    {
        $err = strtolower((string) $error);
        $markers = ['unauthorized', 'loggedout', 'logged-out', 'logged_out', 'session-not-found', 'no-session', 'session-missing', 'not-authenticated'];
        $sessionLost = false;
        foreach ($markers as $m) {
            if (strpos($err, $m) !== false) { $sessionLost = true; break; }
        }
        // Meta anti-abuse: stanza error 463 = sender numara soft-banned ("warning zone"),
        // tum gonderimler ack'te reddediliyor. Cozumu numara dinlendirmek ya da degistirmek;
        // bu surede WA'ya tekrar tekrar deneme yapmak ban riskini artirir.
        if (!$sessionLost && (strpos($err, '463') !== false || strpos($err, 'error in ack') !== false)) {
            $sessionLost = true;
        }
        if (!$sessionLost) return;

        $simdiki = (string) ($salon->whatsapp_durum ?? '');
        // Zaten disconnected/banned ise tekrar yazma — gereksiz update + log gurultusu olmasin
        if (in_array($simdiki, ['disconnected', 'banned-or-loggedout', 'auto-paused-ban-risk'], true)) {
            return;
        }

        $salon->whatsapp_durum = 'disconnected';
        $salon->whatsapp_son_hata = substr('Bridge: ' . $error, 0, 120);
        $salon->save();
        Log::warning('[WA] oturum yetkisiz tespit edildi, salon disconnected isaretlendi', [
            'salon_id' => $salon->id, 'error' => $error,
        ]);
    }

    protected function sendViaCloudApi(Salonlar $salon, $to, $message, $randevuId, $userId, $templateCtx, $gonderimTipi = null)
    {
        $logId = $this->logPending($salon->id, $userId, $randevuId, $to, $message, 'cloud_api', $gonderimTipi);

        // Template adını salon ayarlarından çöz (key: '1gun', 'yaklasan', 'iptal', 'guncelleme')
        $templateKey = $templateCtx['key'] ?? null;
        $params = $templateCtx['params'] ?? [];

        $templateMap = [
            '1gun' => $salon->cloud_api_template_1gun,
            'yaklasan' => $salon->cloud_api_template_yaklasan,
            'iptal' => $salon->cloud_api_template_iptal,
            'guncelleme' => $salon->cloud_api_template_guncelleme,
        ];
        $templateName = $templateKey && isset($templateMap[$templateKey]) ? $templateMap[$templateKey] : null;

        if (!$templateName) {
            $this->markFailed($logId, 'cloud-template-not-configured');
            return ['ok' => false, 'error' => 'cloud-template-not-configured', 'logId' => $logId, 'provider' => 'cloud_api'];
        }

        $client = app(WhatsAppCloudApiClient::class);
        $resp = $client->sendTemplate($salon, $to, $templateName, $params);

        if ($resp['ok']) {
            $this->markSent($logId, $resp['messageId'] ?? null);
            return ['ok' => true, 'queued' => false, 'logId' => $logId, 'messageId' => $resp['messageId'], 'provider' => 'cloud_api'];
        }

        $this->markFailed($logId, $resp['error'] ?? 'unknown');
        return ['ok' => false, 'error' => $resp['error'] ?? 'unknown', 'status' => $resp['status'] ?? 0, 'logId' => $logId, 'provider' => 'cloud_api'];
    }

    public function canSendToday(Salonlar $salon)
    {
        $saglayici = $salon->whatsapp_saglayici ?? 'baileys';

        if ($saglayici === 'cloud_api') {
            // Cloud API'de salon-level aktif/durum/limit kontrolü gevşek (Meta'nın kendi limit'leri var)
            if (empty($salon->cloud_api_token) || empty($salon->cloud_api_phone_number_id)) {
                return false;
            }
            return true;
        }

        // Baileys: aktif + connected + gunluk limit
        if (!$salon->whatsapp_aktif || $salon->whatsapp_durum !== 'connected') {
            return false;
        }
        $limit = (int) ($salon->whatsapp_gunluk_limit ?: config('whatsapp.default_daily_limit', 150));
        if ($limit <= 0) return false;

        $sentToday = DB::table('whatsapp_gonderim_loglari')
            ->where('salon_id', $salon->id)
            ->where('durum', 1)
            ->whereDate('gonderim_tarihi', Carbon::today())
            ->count();

        return $sentToday < $limit;
    }

    /**
     * Warmup ramp kaldirildi — sadece salon.whatsapp_gunluk_limit kullaniliyor.
     * Geriye uyumluluk icin metod korundu; tum cagrilar gunluk_limit donduruyor.
     */
    public function warmupCap(Salonlar $salon)
    {
        return (int) ($salon->whatsapp_gunluk_limit ?: config('whatsapp.default_daily_limit', 150));
    }

    public function withinBusinessHours()
    {
        // Eger enforce kapaliysa her saatte gonderime izin ver (24/7).
        if (!config('whatsapp.business_hours.enforce', false)) {
            return true;
        }
        $hour = (int) now()->format('H');
        $start = (int) config('whatsapp.business_hours.start', 9);
        $end = (int) config('whatsapp.business_hours.end', 21);
        return $hour >= $start && $hour < $end;
    }

    public function normalizePhone($raw)
    {
        $n = preg_replace('/\D+/', '', (string) $raw);
        if (!$n) return null;
        if (substr($n, 0, 2) === '00') $n = substr($n, 2);
        if (strlen($n) === 10 && $n[0] === '5') $n = '90' . $n;
        if (strlen($n) === 11 && $n[0] === '0') $n = '90' . substr($n, 1);
        return strlen($n) >= 11 ? $n : null;
    }

    public function varyMessage($base, $musteriAdi = null)
    {
        $greetings = ['İyi günler.', 'Merhaba.', 'Selamlar.', 'İyi günler, umarız iyisinizdir.'];
        $closings = [
            'Görüşmek üzere.',
            'Sizi bekliyoruz.',
            'Hatırlatmak istedik.',
            'Randevunuzu unutmayın lütfen.',
        ];
        $greet = $greetings[array_rand($greetings)];
        $close = $closings[array_rand($closings)];
        $name = $musteriAdi ? (' Sayın ' . trim($musteriAdi) . ',') : '';
        return trim($greet . $name . ' ' . $base . ' ' . $close);
    }

    protected function logPending($salonId, $userId, $randevuId, $telefon, $mesaj, $provider = null, $gonderimTipi = null)
    {
        $row = [
            'salon_id' => $salonId,
            'user_id' => $userId,
            'randevu_id' => $randevuId,
            'telefon' => $telefon,
            'mesaj' => $mesaj,
            'durum' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        // Yeni kolonlar opsiyonel — migration calismadiysa schema check ile koruma
        if ($provider !== null && \Illuminate\Support\Facades\Schema::hasColumn('whatsapp_gonderim_loglari', 'provider')) {
            $row['provider'] = $provider;
        }
        if ($gonderimTipi !== null && \Illuminate\Support\Facades\Schema::hasColumn('whatsapp_gonderim_loglari', 'gonderim_tipi')) {
            $row['gonderim_tipi'] = $gonderimTipi;
        }
        return DB::table('whatsapp_gonderim_loglari')->insertGetId($row);
    }

    protected function markSent($logId, $messageId)
    {
        DB::table('whatsapp_gonderim_loglari')->where('id', $logId)->update([
            'durum' => 1,
            'mesaj_id' => $messageId,
            'gonderim_tarihi' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function markFailed($logId, $hata)
    {
        DB::table('whatsapp_gonderim_loglari')->where('id', $logId)->update([
            'durum' => 2,
            'hata' => substr($hata, 0, 150),
            'updated_at' => now(),
        ]);
    }

    public function markSmsFallback($logId)
    {
        if (!$logId) return;
        DB::table('whatsapp_gonderim_loglari')->where('id', $logId)->update([
            'durum' => 3,
            'updated_at' => now(),
        ]);
    }

    protected function request($method, $path, array $body = null)
    {
        $url = $this->baseUrl . $path;
        $t0 = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers = [
            'X-Service-Token: ' . $this->token,
            'Accept: application/json',
        ];
        if ($body !== null) {
            $json = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        $sureMs = (int) ((microtime(true) - $t0) * 1000);
        curl_close($ch);

        if ($raw === false) {
            Log::warning('[WA] curl hata', [
                'method' => $method, 'url' => $url, 'err' => $curlErr, 'sure_ms' => $sureMs,
            ]);
            return ['ok' => false, 'error' => 'service-unreachable', 'status' => 0];
        }

        $decoded = json_decode($raw, true);
        $ok = $status >= 200 && $status < 300;
        Log::info('[WA] http istek', [
            'method' => $method, 'url' => $url, 'status' => $status, 'sure_ms' => $sureMs,
            'body_kb' => round(strlen($raw) / 1024, 2),
        ]);
        return [
            'ok' => $ok,
            'status' => $status,
            'body' => $decoded,
        ];
    }
}
