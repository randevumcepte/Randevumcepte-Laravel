<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * USD/TRY kuru — TCMB gunluk bulteni (https://www.tcmb.gov.tr/kurlar/today.xml), DOVIZ SATIS (ForexSelling).
 * Kartla dolar odenen servisler (Anthropic, Google) icin gercege en yakin resmi kur.
 *
 * 3 saat onbellek. TCMB'ye ulasilamazsa / bulten bos donerse (hafta sonu, tatil, yayin saati gecisi) son basarili
 * kur (kalici onbellek), o da yoksa AI Kredi sayfasindaki elle girilen kur kullanilir; 15 dk sonra tekrar denenir.
 * Istek yolunda (santral baslat/bitir) usd() CAGIRMAYIN (12 sn bekletebilir) -> usdHizli().
 */
class DovizKuru
{
    const URL = 'https://www.tcmb.gov.tr/kurlar/today.xml';

    /** @return array{kur: float, tarih: ?string, kaynak: string} */
    public static function usdBilgi(): array
    {
        $b = Cache::get('tcmb_usd_kur_v1');
        // Basarisiz deneme sonrasi 15 dk tekrar deneme (TCMB yavas: her istekte 10 sn beklenmesin)
        if (!$b && !Cache::get('tcmb_usd_kur_hata')) {
            $b = self::tcmbCek();
            if ($b) {
                Cache::put('tcmb_usd_kur_v1', $b, 180);       // 3 saat (Laravel 5.6: dakika)
                Cache::forever('tcmb_usd_kur_son', $b);
            } else {
                Cache::put('tcmb_usd_kur_hata', 1, 15);
            }
        }
        if ($b && ($b['kur'] ?? 0) > 0) return $b;

        $son = Cache::get('tcmb_usd_kur_son');
        if ($son && ($son['kur'] ?? 0) > 0) return ['kaynak' => 'tcmb-son'] + $son;

        $elle = (float) (AiKullanimLog::ayar()['kur'] ?? 0);
        return ['kur' => $elle > 0 ? $elle : 40.0, 'tarih' => null, 'kaynak' => 'elle'];
    }

    public static function usd(): float
    {
        return (float) self::usdBilgi()['kur'];
    }

    /**
     * AG ISTEGI YAPMADAN kur: taze onbellek -> son basarili TCMB kuru -> elle kur.
     * Cagri yolunda (santral baslat/bitir; aktarma bitir'i bekler) bunu kullanin; yenileme icin usd()'yi
     * cevap gonderildikten sonra cagirin (app()->terminating).
     */
    public static function usdHizli(): float
    {
        $b = Cache::get('tcmb_usd_kur_v1');
        if ($b && ($b['kur'] ?? 0) > 0) return (float) $b['kur'];
        $son = Cache::get('tcmb_usd_kur_son');
        if ($son && ($son['kur'] ?? 0) > 0) return (float) $son['kur'];
        $elle = (float) (AiKullanimLog::ayar()['kur'] ?? 0);
        return $elle > 0 ? $elle : 40.0;
    }

    /** TCMB bultenini cek: USD ForexSelling. Hata/bicim bozuksa null. */
    protected static function tcmbCek(): ?array
    {
        try {
            $ch = curl_init(self::URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,   // TCMB baglantisi ~5 sn surebiliyor
                CURLOPT_TIMEOUT => 12,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'randevumcepte-kur/1.0',
            ]);
            $xml = curl_exec($ch);
            $kod = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($kod !== 200 || !is_string($xml) || $xml === '') return null;

            $eski = libxml_use_internal_errors(true);
            $doc = simplexml_load_string($xml);
            libxml_use_internal_errors($eski);
            if (!$doc) return null;

            foreach ($doc->Currency as $c) {
                if ((string) $c['CurrencyCode'] !== 'USD') continue;
                $kur = (float) str_replace(',', '.', (string) $c->ForexSelling);
                $birim = max(1, (int) $c->Unit);
                $kur = $kur / $birim;
                // Mantik kontrolu: bozuk/sifir veri kaydedilmesin
                if ($kur < 1 || $kur > 1000) return null;
                $t = (string) $doc['Tarih']; // "18.11.2026"
                $tarih = preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $t, $m) ? "{$m[3]}-{$m[2]}-{$m[1]}" : null;
                return ['kur' => round($kur, 4), 'tarih' => $tarih, 'kaynak' => 'tcmb'];
            }
        } catch (\Throwable $e) {
            Log::warning('[KUR] TCMB kuru alinamadi: ' . $e->getMessage());
        }
        return null;
    }
}
