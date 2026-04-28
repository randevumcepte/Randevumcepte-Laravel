<?php

namespace App\SistemYonetim;

use Illuminate\Support\Facades\DB;

/**
 * Salon Saglik Skoru — heuristik
 * Skor: 0-100. Yuksek = saglikli, dusuk = risk altinda.
 *
 * Faktorler:
 *  - Son giris (yetkili son 7/30/90 gunde girdi mi)         max 25
 *  - Son randevu (son 7/30/90 gunde randevu yaratildi mi)   max 30
 *  - Aktif kullanim (son 30 gun randevu hacmi)              max 20
 *  - Acik ticket / sikayet                                  -10
 *  - Askida durumu                                          -100
 *  - WhatsApp aktif                                         +5
 *  - Tahsilat/odeme durumu (uyelik suresi gec ise)          max 20
 */
class SaglikSkoru
{
    /**
     * Tek salon icin detayli skor donur.
     *
     * @param int $salonId
     * @return array ['skor'=>int, 'durum'=>'iyi|orta|riskli|kritik', 'sebepler'=>[...], 'sinyaller'=>[...]]
     */
    public static function hesapla($salonId)
    {
        $skor = 0;
        $sinyaller = [];
        $sebepler = [];
        $bugun = date('Y-m-d');

        $salon = DB::table('salonlar')->where('id', $salonId)->first();
        if (!$salon) {
            return ['skor' => 0, 'durum' => 'kritik', 'sebepler' => ['Salon bulunamadi'], 'sinyaller' => []];
        }

        // Askida ise direkt kritik
        if (!empty($salon->askiya_alindi)) {
            return [
                'skor'   => 0,
                'durum'  => 'kritik',
                'sebepler' => ['Salon askıda: ' . ($salon->askiya_alma_sebebi ?: 'sebep yok')],
                'sinyaller' => ['askida' => true],
            ];
        }

        // Son giris (isletme yetkilisi)
        $sonGiris = null;
        try {
            $sonGiris = DB::table('isletmeyetkilileri')
                ->where('salon_id', $salonId)
                ->whereNotNull('son_giris_tarihi')
                ->max('son_giris_tarihi');
        } catch (\Exception $e) {}

        // son_giris_tarihi yoksa updated_at'a bak
        if (!$sonGiris) {
            try {
                $sonGiris = DB::table('isletmeyetkilileri')->where('salon_id', $salonId)->max('updated_at');
            } catch (\Exception $e) {}
        }

        $sinyaller['son_giris'] = $sonGiris;
        $gunGirisYok = $sonGiris ? floor((time() - strtotime($sonGiris)) / 86400) : 999;

        if ($gunGirisYok <= 7)        { $skor += 25; }
        elseif ($gunGirisYok <= 30)   { $skor += 18; $sebepler[] = "Son giriş {$gunGirisYok} gün önce"; }
        elseif ($gunGirisYok <= 90)   { $skor += 8;  $sebepler[] = "Son giriş {$gunGirisYok} gün önce — riskli"; }
        else                           { $sebepler[] = "Hesaba 90+ gündür giriş yok"; }

        // Son randevu
        $sonRandevu = null;
        try {
            $sonRandevu = DB::table('randevular')->where('salon_id', $salonId)->max('created_at');
        } catch (\Exception $e) {}

        $sinyaller['son_randevu'] = $sonRandevu;
        $gunRandevuYok = $sonRandevu ? floor((time() - strtotime($sonRandevu)) / 86400) : 999;

        if ($gunRandevuYok <= 3)       { $skor += 30; }
        elseif ($gunRandevuYok <= 7)   { $skor += 24; }
        elseif ($gunRandevuYok <= 30)  { $skor += 14; $sebepler[] = "Son randevu {$gunRandevuYok} gün önce"; }
        elseif ($gunRandevuYok <= 90)  { $skor += 5;  $sebepler[] = "Son randevu {$gunRandevuYok} gün önce — riskli"; }
        else                            { $sebepler[] = "90+ gündür randevu eklenmemiş"; }

        // 30 gunluk randevu hacmi
        $hacim30 = 0;
        try {
            $hacim30 = (int) DB::table('randevular')
                ->where('salon_id', $salonId)
                ->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
                ->count();
        } catch (\Exception $e) {}

        $sinyaller['hacim_30g'] = $hacim30;
        if ($hacim30 >= 100)      { $skor += 20; }
        elseif ($hacim30 >= 50)   { $skor += 16; }
        elseif ($hacim30 >= 20)   { $skor += 12; }
        elseif ($hacim30 >= 5)    { $skor += 6; }
        elseif ($hacim30 > 0)     { $skor += 2; $sebepler[] = "30 günde sadece {$hacim30} randevu"; }
        else                       { $sebepler[] = "30 günde hiç randevu yok"; }

        // Acik ticket
        $acikTicket = 0;
        try {
            $acikTicket = (int) DB::table('sistemyonetim_destek_talepleri')
                ->where('salon_id', $salonId)
                ->whereIn('durum', ['acik', 'islemde', 'bekliyor'])
                ->count();
        } catch (\Exception $e) {}

        $sinyaller['acik_ticket'] = $acikTicket;
        if ($acikTicket > 0) {
            $skor -= min(15, $acikTicket * 5);
            $sebepler[] = "{$acikTicket} açık talep var";
        }

        // Sikayet notu (son 30 gun)
        $sikayetNotu = 0;
        try {
            $sikayetNotu = (int) DB::table('sistemyonetim_salon_notlari')
                ->where('salon_id', $salonId)
                ->where('tip', 'sikayet')
                ->where('created_at', '>=', date('Y-m-d', strtotime('-30 days')))
                ->count();
        } catch (\Exception $e) {}

        if ($sikayetNotu > 0) {
            $skor -= min(10, $sikayetNotu * 5);
            $sebepler[] = "Son 30 günde {$sikayetNotu} şikayet kaydı";
        }

        // WhatsApp aktif
        if (!empty($salon->whatsapp_aktif)) {
            $skor += 5;
        }

        // Sinir kontrolu
        $skor = max(0, min(100, $skor));

        // Durum
        $durum = 'iyi';
        if ($skor < 25)      $durum = 'kritik';
        elseif ($skor < 50)  $durum = 'riskli';
        elseif ($skor < 75)  $durum = 'orta';

        return [
            'skor' => (int) $skor,
            'durum' => $durum,
            'sebepler' => $sebepler,
            'sinyaller' => $sinyaller,
        ];
    }

    /**
     * Risk altindaki salonlari listele (skor < 50).
     *
     * @param int $limit
     * @return array
     */
    public static function riskAltindakiler($limit = 50)
    {
        // Performans icin: askida olmayan, son 30 gun randevusu az olanlari getir
        $aday = DB::table('salonlar')
            ->where('askiya_alindi', 0)
            ->select('id', 'salon_adi', 'musteri_yetkili_id')
            ->get();

        $sonuc = [];
        foreach ($aday as $s) {
            $info = self::hesapla($s->id);
            if ($info['skor'] >= 50) continue;
            $sonuc[] = [
                'salon_id'   => $s->id,
                'salon_adi'  => $s->salon_adi,
                'mt_id'      => $s->musteri_yetkili_id,
                'skor'       => $info['skor'],
                'durum'      => $info['durum'],
                'sebepler'   => $info['sebepler'],
                'sinyaller'  => $info['sinyaller'],
            ];
            if (count($sonuc) >= $limit * 3) break; // erken cikis
        }

        // Skora gore sirala (en kotuden iyiye)
        usort($sonuc, function ($a, $b) { return $a['skor'] - $b['skor']; });

        return array_slice($sonuc, 0, $limit);
    }
}
