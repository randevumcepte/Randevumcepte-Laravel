<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * AI (Haiku) kullanim kaydi + maliyet hesabi + yuklenen kredi ayari.
 *
 * Her AI cagrisi (niyet/sohbet/karne/yorum/kampanya) buraya loglanir. Anthropic
 * "kalan bakiye"yi API'den vermedigi icin: KALAN = (elle girilen yuklenen kredi) -
 * (loglardan hesaplanan harcama). Yuklenen kredi + kur, storage/app/ai_kredi_ayar.json.
 */
class AiKullanimLog
{
    /** Haiku 4.5 fiyat (USD / 1M token). Gerekirse guncellenir. */
    const GIRDI_USD_M = 1.0;
    const CIKTI_USD_M = 5.0;

    /** Token sayisindan USD maliyet. */
    public static function maliyet($girdi, $cikti)
    {
        return ((int) $girdi / 1000000) * self::GIRDI_USD_M
             + ((int) $cikti / 1000000) * self::CIKTI_USD_M;
    }

    /** Bir AI cagrisini logla (hatalari yut; ana akisi asla bozma). */
    public static function yaz($salonId, $tur, $girdi = 0, $cikti = 0, $model = null, $cache = false, $basarili = true)
    {
        try {
            if (!\Schema::hasTable('ai_kullanim')) return;
            DB::table('ai_kullanim')->insert([
                'salon_id'    => $salonId ? (int) $salonId : null,
                'tur'         => (string) $tur,
                'model'       => $model ? mb_substr((string) $model, 0, 40) : null,
                'girdi_token' => (int) $girdi,
                'cikti_token' => (int) $cikti,
                'maliyet_usd' => $cache ? 0 : self::maliyet($girdi, $cikti),
                'cache'       => $cache ? 1 : 0,
                'basarili'    => $basarili ? 1 : 0,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // sessiz
        }
    }

    /** Yuklenen kredi + kur ayari (JSON dosya). */
    public static function ayar()
    {
        $d = ['yuklenen_usd' => 0.0, 'kur' => 40.0, 'guncelleme' => null];
        try {
            $yol = storage_path('app/ai_kredi_ayar.json');
            if (is_file($yol)) {
                $j = json_decode(@file_get_contents($yol), true);
                if (is_array($j)) $d = array_merge($d, $j);
            }
        } catch (\Throwable $e) {}
        return $d;
    }

    /** Yuklenen kredi + kur kaydet. */
    public static function ayarKaydet($yuklenenUsd, $kur)
    {
        try {
            file_put_contents(storage_path('app/ai_kredi_ayar.json'), json_encode([
                'yuklenen_usd' => (float) $yuklenenUsd,
                'kur'          => (float) $kur > 0 ? (float) $kur : 40.0,
                'guncelleme'   => date('Y-m-d H:i:s'),
            ], JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {}
    }
}
