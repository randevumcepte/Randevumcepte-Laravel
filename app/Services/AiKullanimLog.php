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
            // Gercek (cache olmayan) harcamada dusuk-kredi kontrolu (throttle'li).
            if (!$cache) self::esikKontrol();
        } catch (\Throwable $e) {
            // sessiz
        }
    }

    /** Ayar: yuklenen kredi + kur + dusuk-kredi esigi + son uyari tarihi (JSON dosya). */
    public static function ayar()
    {
        $d = ['yuklenen_usd' => 0.0, 'kur' => 40.0, 'esik_usd' => 1.0, 'son_uyari' => null, 'guncelleme' => null];
        try {
            $yol = storage_path('app/ai_kredi_ayar.json');
            if (is_file($yol)) {
                $j = json_decode(@file_get_contents($yol), true);
                if (is_array($j)) $d = array_merge($d, $j);
            }
        } catch (\Throwable $e) {}
        return $d;
    }

    /** Ayari MEVCUT degerleri koruyarak (merge) yazar. */
    public static function ayarYaz(array $yeni)
    {
        try {
            $data = array_merge(self::ayar(), $yeni);
            $data['guncelleme'] = date('Y-m-d H:i:s');
            file_put_contents(storage_path('app/ai_kredi_ayar.json'), json_encode($data, JSON_UNESCAPED_UNICODE));
            return $data;
        } catch (\Throwable $e) {
            return self::ayar();
        }
    }

    /** Yuklenen kredi + kur kaydet (kredi degisince son_uyari sifirlanir -> tekrar uyarabilir). */
    public static function ayarKaydet($yuklenenUsd, $kur, $esik = null)
    {
        $yeni = [
            'yuklenen_usd' => (float) $yuklenenUsd,
            'kur'          => ((float) $kur > 0) ? (float) $kur : 40.0,
            'son_uyari'    => null,
        ];
        if ($esik !== null) $yeni['esik_usd'] = (float) $esik;
        return self::ayarYaz($yeni);
    }

    /** Tum zaman harcama (USD). */
    public static function tumHarcama()
    {
        try {
            if (!\Schema::hasTable('ai_kullanim')) return 0.0;
            return (float) DB::table('ai_kullanim')->sum('maliyet_usd');
        } catch (\Throwable $e) { return 0.0; }
    }

    /** Kalan kredi (USD) = yuklenen - tum harcama. */
    public static function kalan()
    {
        $a = self::ayar();
        return (float) $a['yuklenen_usd'] - self::tumHarcama();
    }

    /**
     * Dusuk kredi kontrolu: kalan < esik ise GUNDE 1 kez sistem WhatsApp'ina alarm at.
     * Throttle: \Cache ile en fazla 30 dk'da bir hesaplanir (her AI cagrisinda sorgu olmasin).
     */
    public static function esikKontrol()
    {
        try {
            $kilit = 'ai_esik_kontrol_kilit';
            if (\Cache::get($kilit)) return;
            \Cache::put($kilit, 1, 30); // 30 dk (L5.6 dakika)

            $a = self::ayar();
            if ((float) $a['yuklenen_usd'] <= 0) return;        // kredi girilmemis
            $kalan = (float) $a['yuklenen_usd'] - self::tumHarcama();
            $esik  = (float) ($a['esik_usd'] ?? 1.0);
            if ($kalan >= $esik) return;                        // sorun yok
            if (($a['son_uyari'] ?? null) === date('Y-m-d')) return; // bugun uyarildi

            self::alarmGonder($kalan, $esik, $a['kur']);
            self::ayarYaz(['son_uyari' => date('Y-m-d')]);
        } catch (\Throwable $e) {}
    }

    /** Sistem WhatsApp + SMS ile dusuk kredi alarmi. */
    public static function alarmGonder($kalan, $esik, $kur = 40)
    {
        $tl = number_format(max(0, (float) $kalan) * (float) $kur, 2, ',', '.');
        $mesaj = "⚠️ AI KREDİSİ AZALDI\n"
            . "Kalan: \$" . number_format((float) $kalan, 4, ',', '.') . " (yaklaşık {$tl} ₺)\n"
            . "Eşik: \$" . number_format((float) $esik, 2, ',', '.') . "\n"
            . "Patron Asistanı AI kredin bitmek üzere. Sistem Yönetimi > AI Kredi panelinden yükleme yapın.\n"
            . date('d.m.Y H:i');
        try { return \App\Services\SistemBildirim::gonder($mesaj); }
        catch (\Throwable $e) { return ['ok' => false]; }
    }
}
