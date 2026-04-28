<?php

namespace App\Services;

use App\Salonlar;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API HTTP istemcisi.
 *
 * Salon başına token + phone_number_id tutulur, gönderim doğrudan
 * graph.facebook.com'a yapılır. Template-based mesajlama (utility/marketing).
 *
 * Bu sınıf gönderim yapar — başarı/hata durumu döner. Webhook ile
 * gerçek delivery status (delivered/read) için ayrı bir endpoint gerek.
 */
class WhatsAppCloudApiClient
{
    protected $apiVersion = 'v21.0';
    protected $baseUrl = 'https://graph.facebook.com';

    /**
     * Şablon mesaj gönderir.
     *
     * @param Salonlar $salon
     * @param string $to    Normalize edilmiş telefon (905xxxxxxxxx)
     * @param string $templateName  Meta'da onaylı template adı
     * @param array $params Template body parametreleri (sırasıyla)
     * @param string|null $language Default 'tr'
     * @return array ['ok' => bool, 'messageId' => string|null, 'error' => string|null, 'status' => int]
     */
    public function sendTemplate(Salonlar $salon, $to, $templateName, array $params = [], $language = null)
    {
        if (empty($salon->cloud_api_token) || empty($salon->cloud_api_phone_number_id)) {
            return ['ok' => false, 'error' => 'cloud-api-credentials-missing', 'status' => 0];
        }

        $language = $language ?: ($salon->cloud_api_template_dil ?: 'tr');
        $url = $this->baseUrl . '/' . $this->apiVersion . '/' . $salon->cloud_api_phone_number_id . '/messages';

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($params)) {
            $body['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(function ($p) {
                    return ['type' => 'text', 'text' => (string) $p];
                }, $params),
            ]];
        }

        return $this->request($salon, 'POST', $url, $body);
    }

    /**
     * 24 saatlik service window içindeki yanıt mesajları için (template gerek yok).
     * Müşteri ilk yazınca pencere açılır, bu metodla freeform yanıt verilir.
     */
    public function sendText(Salonlar $salon, $to, $message)
    {
        if (empty($salon->cloud_api_token) || empty($salon->cloud_api_phone_number_id)) {
            return ['ok' => false, 'error' => 'cloud-api-credentials-missing', 'status' => 0];
        }

        $url = $this->baseUrl . '/' . $this->apiVersion . '/' . $salon->cloud_api_phone_number_id . '/messages';
        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        return $this->request($salon, 'POST', $url, $body);
    }

    protected function request(Salonlar $salon, $method, $url, array $body = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            'Authorization: Bearer ' . $salon->cloud_api_token,
            'Accept: application/json',
        ];
        if ($body !== null) {
            $json = json_encode($body, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($json);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            Log::warning('[WA Cloud] curl error', ['salon_id' => $salon->id, 'err' => $err]);
            return ['ok' => false, 'error' => 'service-unreachable', 'status' => 0];
        }

        $decoded = json_decode($raw, true);

        if ($status >= 200 && $status < 300) {
            $messageId = $decoded['messages'][0]['id'] ?? null;
            return ['ok' => true, 'messageId' => $messageId, 'status' => $status, 'body' => $decoded];
        }

        $errMsg = $decoded['error']['message'] ?? ($decoded['error']['code'] ?? 'unknown');
        Log::warning('[WA Cloud] api error', [
            'salon_id' => $salon->id, 'status' => $status, 'err' => $errMsg, 'body' => $decoded,
        ]);
        return [
            'ok' => false,
            'error' => substr((string) $errMsg, 0, 150),
            'status' => $status,
            'body' => $decoded,
        ];
    }
}
