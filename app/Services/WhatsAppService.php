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

    public function sendReminder(Salonlar $salon, $to, $message, $randevuId = null, $userId = null)
    {
        Log::info('[WA] sendReminder cagrildi', [
            'salon_id' => $salon->id, 'randevu_id' => $randevuId, 'user_id' => $userId,
            'telefon_raw' => $to, 'baseUrl' => $this->baseUrl,
        ]);

        $normalized = $this->normalizePhone($to);
        if (!$normalized) {
            Log::warning('[WA] invalid-phone', ['salon_id' => $salon->id, 'telefon' => $to]);
            return ['ok' => false, 'error' => 'invalid-phone'];
        }

        if (!$this->canSendToday($salon)) {
            Log::warning('[WA] daily-cap-reached veya kanal kapali', [
                'salon_id' => $salon->id,
                'wa_aktif' => (int) ($salon->whatsapp_aktif ?? 0),
                'wa_durum' => $salon->whatsapp_durum,
                'gunluk_limit' => (int) ($salon->whatsapp_gunluk_limit ?: 0),
            ]);
            return ['ok' => false, 'error' => 'daily-cap-reached'];
        }

        if (!$this->withinBusinessHours()) {
            Log::warning('[WA] outside-business-hours', [
                'salon_id' => $salon->id,
                'simdi_saat' => (int) now()->format('H'),
                'baslangic' => (int) config('whatsapp.business_hours.start', 9),
                'bitis' => (int) config('whatsapp.business_hours.end', 21),
            ]);
            return ['ok' => false, 'error' => 'outside-business-hours'];
        }

        $logId = $this->logPending($salon->id, $userId, $randevuId, $normalized, $message);

        $response = $this->request('POST', "/session/{$salon->id}/send", [
            'to' => $normalized,
            'message' => $message,
            'warmupStart' => optional($salon->whatsapp_warmup_baslangic)->toIso8601String()
                ?: optional($salon->whatsapp_baglanti_tarihi)->toIso8601String(),
            'dailyLimit' => (int) ($salon->whatsapp_gunluk_limit ?: config('whatsapp.default_daily_limit', 150)),
            'logId' => $logId,
        ]);

        Log::info('[WA] service yanit', [
            'salon_id' => $salon->id, 'randevu_id' => $randevuId, 'logId' => $logId,
            'status' => $response['status'] ?? 0,
            'error' => $response['error'] ?? null,
            'body' => $response['body'] ?? null,
        ]);

        // 202 Accepted = kuyruğa alındı, webhook ile sent/failed bildirecek
        if (($response['status'] ?? 0) === 202) {
            return ['ok' => true, 'queued' => true, 'logId' => $logId];
        }

        // 4xx/5xx = hemen başarısız, SMS fallback tetiklenmeli
        $err = $response['error'] ?? ($response['body']['error'] ?? 'unknown');
        $this->markFailed($logId, $err);
        return ['ok' => false, 'error' => $err, 'status' => $response['status'] ?? 0, 'logId' => $logId];
    }

    public function canSendToday(Salonlar $salon)
    {
        if (!$salon->whatsapp_aktif || $salon->whatsapp_durum !== 'connected') {
            return false;
        }
        $limit = (int) ($salon->whatsapp_gunluk_limit ?: config('whatsapp.default_daily_limit', 150));
        $limit = min($limit, $this->warmupCap($salon));
        if ($limit <= 0) return false;

        $sentToday = DB::table('whatsapp_gonderim_loglari')
            ->where('salon_id', $salon->id)
            ->where('durum', 1)
            ->whereDate('gonderim_tarihi', Carbon::today())
            ->count();

        return $sentToday < $limit;
    }

    public function warmupCap(Salonlar $salon)
    {
        $start = $salon->whatsapp_warmup_baslangic ?? $salon->whatsapp_baglanti_tarihi;
        if (!$start) {
            return (int) ($salon->whatsapp_gunluk_limit ?: 150);
        }
        $days = Carbon::parse($start)->diffInDays(Carbon::now());
        $ramp = [15, 30, 50, 80, 110, 140, 180];
        if ($days >= count($ramp)) {
            return (int) ($salon->whatsapp_gunluk_limit ?: 180);
        }
        return $ramp[$days] ?? 15;
    }

    public function withinBusinessHours()
    {
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

    protected function logPending($salonId, $userId, $randevuId, $telefon, $mesaj)
    {
        return DB::table('whatsapp_gonderim_loglari')->insertGetId([
            'salon_id' => $salonId,
            'user_id' => $userId,
            'randevu_id' => $randevuId,
            'telefon' => $telefon,
            'mesaj' => $mesaj,
            'durum' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
