<?php

namespace App\SistemYonetim;

use App\Salonlar;
use App\SabitNumaralar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * DakikaHesap — e-santral musterileri icin GIDEN (dis hat) konusma dakikasi
 * hesabini tek yerde toplar. Hem sistem yonetim admin paneli (PanelController)
 * hem isletme santral sayfasi (StoreAdminController) bunu kullanir.
 *
 * Kaynak: santral CDR ozet API'si (dakikaOzet.php) — trunk (did) bazli
 * SUM(billsec). Opsiyonel dahili ile personel bazli daraltilir.
 *
 * Olcum "havuz" mantigi: tanimli dakika eksi, sayim baslangicindan bugune
 * harcanan. Sayim baslangici = paket kaydindaki ozel tarih, yoksa salonun
 * olusturma tarihi (created_at).
 */
class DakikaHesap
{
    const ENDPOINT = 'https://santral.randevumcepte.com.tr/monitor/api/dakikaOzet.php';
    const CACHE_SN = 300; // 5 dk

    /**
     * Bir salon icin dakika ozeti.
     *
     * @param int         $salonId
     * @param string|null $dahili  Verilirse yalnizca bu dahilinin giden dakikasi
     *                             (personel bazli gorunum). tanimli/kalan salon
     *                             havuzuna gore hesaplanmaya devam eder; cagiran
     *                             taraf personel modunda bunlari gizleyebilir.
     * @return array{
     *   trunk:?string, tanimli:int, kullanilan:float, kalan:float,
     *   yuzde:int, adet:int, sayim_baslangic:string, dahili:?string, hata:bool
     * }
     */
    public static function hesapla($salonId, $dahili = null)
    {
        $trunk = SabitNumaralar::where('salon_id', $salonId)->value('numara');

        $paket = SalonDakikaPaketi::where('salon_id', $salonId)->first();
        $salon = Salonlar::find($salonId);

        $bas = self::sayimBaslangic($salon, $paket);

        // Olcum: santral CDR (dstchannel/billsec). Operator faturasindan ~%10
        // fazla olabilir (early-media/ringback billsec'e giriyor) ama hizli ve
        // olcegeklenebilir; operator panelinin yavas CDR'ina bagimli degil.
        $kullanim = self::cdrKullanim($trunk, $bas, date('Y-m-d'), $dahili);

        $tanimli    = (int) ($paket->tanimli_dakika ?? 0);
        $kullanilan = $kullanim['dakika'];
        $kalan      = round($tanimli - $kullanilan, 1);
        $yuzde      = $tanimli > 0 ? (int) min(100, round($kullanilan / $tanimli * 100)) : 0;

        return [
            'trunk'           => $trunk,
            'tanimli'         => $tanimli,
            'kullanilan'      => round($kullanilan, 1),
            'kalan'           => $kalan,
            'yuzde'           => $yuzde,
            'adet'            => $kullanim['adet'],
            'sayim_baslangic' => $bas,
            'dahili'          => $dahili,
            'hata'            => $kullanim['hata'],
        ];
    }

    /**
     * Sayim baslangic tarihi: paket ozel tarihi > salon created_at > sabit.
     */
    public static function sayimBaslangic($salon, $paket = null)
    {
        if ($paket && !empty($paket->sayim_baslangic)) {
            return date('Y-m-d', strtotime($paket->sayim_baslangic));
        }
        if ($salon && !empty($salon->created_at)) {
            return date('Y-m-d', strtotime((string) $salon->created_at));
        }
        return '2020-01-01';
    }

    /**
     * CDR ozet API'sinden giden konusma dakikasini ceker (5 dk cache).
     *
     * @return array{dakika: float, adet: int, hata: bool}
     */
    public static function cdrKullanim($trunk, $tarih1 = null, $tarih2 = null, $dahili = null)
    {
        if (empty($trunk) || !preg_match('/^\d+$/', (string) $trunk)) {
            return ['dakika' => 0.0, 'adet' => 0, 'hata' => false];
        }

        $qs = 'did=' . urlencode($trunk);
        if ($tarih1) $qs .= '&tarih1=' . urlencode($tarih1);
        if ($tarih2) $qs .= '&tarih2=' . urlencode($tarih2);
        if ($dahili !== null && $dahili !== '' && preg_match('/^\d+$/', (string) $dahili)) {
            $qs .= '&dahili=' . urlencode($dahili);
        }

        $cacheKey = 'sy.dakika.' . md5($qs);
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) return $cached;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::ENDPOINT . '?' . $qs);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $raw = curl_exec($ch);
        if (curl_errno($ch)) {
            Log::error('DakikaHesap curl: ' . curl_error($ch));
            curl_close($ch);
            return ['dakika' => 0.0, 'adet' => 0, 'hata' => true];
        }
        curl_close($ch);

        $d = json_decode($raw, true);
        if (!is_array($d) || isset($d['error'])) {
            return ['dakika' => 0.0, 'adet' => 0, 'hata' => true];
        }

        $sonuc = [
            'dakika' => (float) ($d['toplam_dakika'] ?? 0),
            'adet'   => (int) ($d['giden_cevaplanan_adet'] ?? 0),
            'hata'   => false,
        ];
        Cache::put($cacheKey, $sonuc, self::CACHE_SN);
        return $sonuc;
    }
}
