<?php

namespace App\Services\Gorsel;

use Illuminate\Support\Facades\Log;

/**
 * Claude ile gorsel analiz (RandevuAsistan gibi ham HTTP; proje SDK kullanmiyor).
 * Model: config services.wa_asistan.gorsel_model (vars. claude-sonnet-5-5: ince detay — tas sayisi, french/krom ayrimi).
 * Cikti JSON semasina baglanir (output_config.format); API reddederse (400) semasiz tekrar dener ve metinden JSON ayiklar.
 */
class AnthropicGorselSaglayici implements GorselSaglayici
{
    public function analiz(string $b64, string $mime, string $talimat, array $sema, string $metin = ''): array
    {
        $key = config('services.anthropic.key') ?: env('ANTHROPIC_API_KEY');
        if (!$key) return ['json' => null, 'kullanim' => ['girdi' => 0, 'cikti' => 0], 'maliyet_usd' => 0.0, 'hata' => 'anahtar_yok'];
        $model = (string) (config('services.wa_asistan.gorsel_model') ?: 'claude-sonnet-5-5');
        $body = [
            'model' => $model,
            'max_tokens' => 1500,
            'system' => $talimat,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => $b64]],
                    ['type' => 'text', 'text' => $metin !== '' ? "Müşterinin mesajı: \"{$metin}\"\nFotoğrafı şemaya göre analiz et." : 'Fotoğrafı şemaya göre analiz et.'],
                ],
            ]],
            'output_config' => ['effort' => 'low', 'format' => ['type' => 'json_schema', 'schema' => $sema]],
        ];
        [$code, $j, $hata] = $this->istek($body, $key);
        if ($code === 0 || $code === 429 || $code >= 500) { // gecici hata: bir kez daha
            usleep(800000);
            [$code, $j, $hata] = $this->istek($body, $key);
        }
        if ($code === 400) {
            // Sema/format desteklenmiyorsa: semasiz, "yalniz JSON" talimatiyla tekrar
            unset($body['output_config']['format']);
            $body['system'] .= "\n\nCevabın YALNIZCA şu şemaya uyan tek bir JSON nesnesi olsun, başka hiçbir metin yazma:\n" . json_encode($sema, JSON_UNESCAPED_UNICODE);
            [$code, $j, $hata] = $this->istek($body, $key);
        }
        $kullanim = ['girdi' => (int) ($j['usage']['input_tokens'] ?? 0) + (int) ($j['usage']['cache_read_input_tokens'] ?? 0) + (int) ($j['usage']['cache_creation_input_tokens'] ?? 0),
                     'cikti' => (int) ($j['usage']['output_tokens'] ?? 0)];
        $usd = ($kullanim['girdi'] * (float) config('services.wa_asistan.gorsel_girdi_usd_m', 2.0)
              + $kullanim['cikti'] * (float) config('services.wa_asistan.gorsel_cikti_usd_m', 10.0)) / 1000000;
        if ($code !== 200 || !is_array($j)) return ['json' => null, 'kullanim' => $kullanim, 'maliyet_usd' => $usd, 'hata' => $hata ?: "http_{$code}"];
        if (($j['stop_reason'] ?? '') === 'refusal') return ['json' => null, 'kullanim' => $kullanim, 'maliyet_usd' => $usd, 'hata' => 'refusal'];
        $txt = '';
        foreach ((array) ($j['content'] ?? []) as $b) if (($b['type'] ?? '') === 'text') $txt .= $b['text'] ?? '';
        return ['json' => self::jsonAyikla($txt), 'kullanim' => $kullanim, 'maliyet_usd' => $usd, 'hata' => null];
    }

    /** Metindeki ilk JSON nesnesi (```json bloklari dahil). */
    public static function jsonAyikla(string $txt): ?array
    {
        $txt = trim($txt);
        $j = json_decode($txt, true);
        if (is_array($j)) return $j;
        if (preg_match('/\{.*\}/s', $txt, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j)) return $j;
        }
        return null;
    }

    /** @return array [httpKodu, cevapDizisi|null, hata|null] */
    protected function istek(array $body, string $key): array
    {
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_HTTPHEADER => ['content-type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01'],
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
        ]);
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($raw === false || $code !== 200) {
            Log::warning('[WA-GORSEL] analiz hatasi', ['kod' => $code, 'hata' => mb_substr((string) ($raw ?: $err), 0, 300)]);
        }
        return [$code, $raw ? json_decode($raw, true) : null, $raw === false ? $err : null];
    }
}
