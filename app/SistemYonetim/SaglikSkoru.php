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

        // Son giris: kanonik olarak personeller.yetkili_id uzerinden bagli yetkililer
        $sonGiris = null;
        try {
            $sonGiris = DB::table('isletmeyetkilileri')
                ->join('personeller', 'isletmeyetkilileri.id', '=', 'personeller.yetkili_id')
                ->where('personeller.salon_id', $salonId)
                ->whereNotNull('isletmeyetkilileri.son_giris_tarihi')
                ->max('isletmeyetkilileri.son_giris_tarihi');
        } catch (\Exception $e) {}

        // Fallback: updated_at, yine personeller uzerinden
        if (!$sonGiris) {
            try {
                $sonGiris = DB::table('isletmeyetkilileri')
                    ->join('personeller', 'isletmeyetkilileri.id', '=', 'personeller.yetkili_id')
                    ->where('personeller.salon_id', $salonId)
                    ->max('isletmeyetkilileri.updated_at');
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
     * Risk altindaki salonlari listele (skor < 50). 5 dk cache.
     * Onceki versiyon her salon icin 6 query yapiyordu (300 salon = 1800+ query).
     * Yeni versiyon: 5 toplu query + PHP scoring.
     *
     * @param int $limit
     * @return array
     */
    public static function riskAltindakiler($limit = 50)
    {
        return \Cache::remember('sy.risk.list.'.$limit, 300, function () use ($limit) {
            return self::riskAltindakilerHesapla($limit);
        });
    }

    private static function riskAltindakilerHesapla($limit)
    {
        $aday = DB::table('salonlar')
            ->where('askiya_alindi', 0)
            ->select('id', 'salon_adi', 'musteri_yetkili_id', 'whatsapp_aktif')
            ->get();

        if ($aday->isEmpty()) return [];

        $salonIds = $aday->pluck('id')->all();
        $sinir30 = date('Y-m-d 00:00:00', strtotime('-30 days'));

        // Bulk Q1: son giris (kanonik B yolu — personeller↔isletmeyetkilileri)
        $sonGirisMap = [];
        try {
            $rows = DB::table('salon_personelleri')
                ->join('isletmeyetkilileri', 'salon_personelleri.yetkili_id', '=', 'isletmeyetkilileri.id')
                ->whereIn('salon_personelleri.salon_id', $salonIds)
                ->whereNotNull('isletmeyetkilileri.son_giris_tarihi')
                ->groupBy('salon_personelleri.salon_id')
                ->selectRaw('salon_personelleri.salon_id, MAX(isletmeyetkilileri.son_giris_tarihi) as son_giris')
                ->get();
            foreach ($rows as $r) $sonGirisMap[$r->salon_id] = $r->son_giris;
        } catch (\Exception $e) {}

        // Bulk Q2: randevu istatistikleri (son tarih + 30 gun adet)
        $randevuMap = [];
        try {
            $rows = DB::table('randevular')
                ->whereIn('salon_id', $salonIds)
                ->groupBy('salon_id')
                ->selectRaw('salon_id, MAX(created_at) as son, SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as adet30', [$sinir30])
                ->get();
            foreach ($rows as $r) $randevuMap[$r->salon_id] = $r;
        } catch (\Exception $e) {}

        // Bulk Q3: acik ticket sayisi
        $ticketMap = [];
        try {
            $rows = DB::table('sistemyonetim_destek_talepleri')
                ->whereIn('salon_id', $salonIds)
                ->whereIn('durum', ['acik', 'islemde', 'bekliyor'])
                ->groupBy('salon_id')
                ->selectRaw('salon_id, COUNT(*) as adet')
                ->get();
            foreach ($rows as $r) $ticketMap[$r->salon_id] = (int) $r->adet;
        } catch (\Exception $e) {}

        // Bulk Q4: sikayet notlari (son 30 gun)
        $sikayetMap = [];
        try {
            $rows = DB::table('sistemyonetim_salon_notlari')
                ->whereIn('salon_id', $salonIds)
                ->where('tip', 'sikayet')
                ->where('created_at', '>=', $sinir30)
                ->groupBy('salon_id')
                ->selectRaw('salon_id, COUNT(*) as adet')
                ->get();
            foreach ($rows as $r) $sikayetMap[$r->salon_id] = (int) $r->adet;
        } catch (\Exception $e) {}

        $now = time();
        $sonuc = [];

        foreach ($aday as $s) {
            $skor = 0;
            $sebepler = [];

            // Son giris
            $sonGiris = $sonGirisMap[$s->id] ?? null;
            $gunGirisYok = $sonGiris ? floor(($now - strtotime($sonGiris)) / 86400) : 999;
            if ($gunGirisYok <= 7)        { $skor += 25; }
            elseif ($gunGirisYok <= 30)   { $skor += 18; $sebepler[] = "Son giriş {$gunGirisYok} gün önce"; }
            elseif ($gunGirisYok <= 90)   { $skor += 8;  $sebepler[] = "Son giriş {$gunGirisYok} gün önce — riskli"; }
            else                          { $sebepler[] = "Hesaba 90+ gündür giriş yok"; }

            // Randevu
            $r = $randevuMap[$s->id] ?? null;
            $sonRandevu = $r ? $r->son : null;
            $hacim30 = $r ? (int) $r->adet30 : 0;

            $gunRandevuYok = $sonRandevu ? floor(($now - strtotime($sonRandevu)) / 86400) : 999;
            if ($gunRandevuYok <= 3)       { $skor += 30; }
            elseif ($gunRandevuYok <= 7)   { $skor += 24; }
            elseif ($gunRandevuYok <= 30)  { $skor += 14; $sebepler[] = "Son randevu {$gunRandevuYok} gün önce"; }
            elseif ($gunRandevuYok <= 90)  { $skor += 5;  $sebepler[] = "Son randevu {$gunRandevuYok} gün önce — riskli"; }
            else                            { $sebepler[] = "90+ gündür randevu eklenmemiş"; }

            if ($hacim30 >= 100)      { $skor += 20; }
            elseif ($hacim30 >= 50)   { $skor += 16; }
            elseif ($hacim30 >= 20)   { $skor += 12; }
            elseif ($hacim30 >= 5)    { $skor += 6; }
            elseif ($hacim30 > 0)     { $skor += 2; $sebepler[] = "30 günde sadece {$hacim30} randevu"; }
            else                       { $sebepler[] = "30 günde hiç randevu yok"; }

            $acikTicket = $ticketMap[$s->id] ?? 0;
            if ($acikTicket > 0) {
                $skor -= min(15, $acikTicket * 5);
                $sebepler[] = "{$acikTicket} açık talep var";
            }

            $sikayet = $sikayetMap[$s->id] ?? 0;
            if ($sikayet > 0) {
                $skor -= min(10, $sikayet * 5);
                $sebepler[] = "Son 30 günde {$sikayet} şikayet kaydı";
            }

            if (!empty($s->whatsapp_aktif)) $skor += 5;

            $skor = max(0, min(100, $skor));
            if ($skor >= 50) continue; // sadece risk altinda olanlar

            $durum = $skor < 25 ? 'kritik' : 'riskli';

            $sonuc[] = [
                'salon_id'   => $s->id,
                'salon_adi'  => $s->salon_adi,
                'mt_id'      => $s->musteri_yetkili_id,
                'skor'       => (int) $skor,
                'durum'      => $durum,
                'sebepler'   => $sebepler,
                'sinyaller'  => [
                    'son_giris'   => $sonGiris,
                    'son_randevu' => $sonRandevu,
                    'hacim_30g'   => $hacim30,
                    'acik_ticket' => $acikTicket,
                ],
            ];
        }

        usort($sonuc, function ($a, $b) { return $a['skor'] - $b['skor']; });
        return array_slice($sonuc, 0, $limit);
    }
}
