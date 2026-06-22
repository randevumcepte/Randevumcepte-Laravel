<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Yeni whatsmeow (Go) bridge'i ile konusan paralel servis.
 *
 * WhatsAppService'in birebir kopyasi degil — sadece bridge yonetim
 * endpoint'lerini (start/status/qr/logout) saglar. Mesaj gonderme aktif
 * uretim hala mevcut WhatsAppService uzerinden Baileys'e gider. Bu servis
 * sadece test/pilot panel'i icin: yeni bridge'i QR taratip baglamak,
 * durumunu izlemek, manuel test mesaji gondermek.
 *
 * Mevcut whatsapp_gonderim_loglari, whatsapp_aktif gibi alanlara dokunmaz.
 */
class WhatsmeowService
{
    protected $baseUrl;
    protected $token;
    protected $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('whatsapp.whatsmeow_service_url', 'http://127.0.0.1:3002'), '/');
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

    public function health()
    {
        return $this->request('GET', '/health');
    }

    /**
     * Test mesaji gonderir. Pilot panelden manuel test icin.
     */
    public function sendTest($salonId, $to, $message)
    {
        return $this->request('POST', "/session/{$salonId}/send", [
            'to' => $to,
            'message' => $message,
            'logId' => 0,
            'urgent' => false,
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
            Log::warning('[WMW] curl hata', [
                'method' => $method, 'url' => $url, 'err' => $curlErr, 'sure_ms' => $sureMs,
            ]);
            return ['ok' => false, 'error' => 'service-unreachable', 'status' => 0];
        }

        $decoded = json_decode($raw, true);
        $ok = $status >= 200 && $status < 300;
        Log::info('[WMW] http istek', [
            'method' => $method, 'url' => $url, 'status' => $status, 'sure_ms' => $sureMs,
        ]);
        return [
            'ok' => $ok,
            'status' => $status,
            'body' => $decoded,
        ];
    }
}
