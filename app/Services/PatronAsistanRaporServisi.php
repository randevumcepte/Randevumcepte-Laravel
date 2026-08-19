<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * PATRON ASISTANI — MOBIL veri katmani (salt-okunur).
 *
 * Web tarafinda asistan, StoreAdminController'in dashboard/isletmeRaporlari
 * fonksiyonlarina delege ediyor; ama o fonksiyonlar WEB oturum guard'ina
 * (Auth::guard('isletmeyonetim')) bagli. Mobil uc (auth:isletmeyonetim-api)
 * icin ayni sorgulari, cozulmus $salonId ile bagimsiz calistiran bu servis
 * kullanilir. Sorgular web rapor fonksiyonlariyla BIREBIR ayni (ayni tablo/kolon).
 *
 * Cikti sekilleri PatronAsistanServisi'nin cevapX() metotlarinin bekledigiyle
 * ayni tutulmustur (kasa: nakit/kart/havale/diger/toplam, personel: personeller[]
 * vb.) — boylece niyet+cevap "beyni" web ile mobil arasinda tek yerde paylasilir.
 */
class PatronAsistanRaporServisi
{
    /** Kanonik donem (gun|hafta|ay) -> [t1, t2] tarih araligi (raporPeriodDates ile ayni). */
    public function donemTarih($donem)
    {
        $bugun = date('Y-m-d');
        if ($donem === 'ay') {
            return [date('Y-m-01'), date('Y-m-t')];
        }
        if ($donem === 'hafta') {
            return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
        }
        return [$bugun, $bugun]; // gun
    }

    /** Kasa/ciro — tahsilatlar tablosu, odeme yontemine gore kirilim (dashboardKasa ile ayni). */
    public function kasa($salonId, $t1, $t2)
    {
        $rows = DB::table('tahsilatlar')
            ->leftJoin('odeme_yontemleri', 'tahsilatlar.odeme_yontemi_id', '=', 'odeme_yontemleri.id')
            ->where('tahsilatlar.salon_id', $salonId)
            ->whereBetween('tahsilatlar.odeme_tarihi', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
            ->select('odeme_yontemleri.odeme_yontemi as yontem', DB::raw('SUM(tahsilatlar.tutar) as toplam'))
            ->groupBy('odeme_yontemleri.odeme_yontemi')
            ->get();

        return $this->tahsilatKirilim($rows);
    }

    /** Personel performansi (isletmeRaporlariPersonel ile ayni). */
    public function personel($salonId, $t1, $t2)
    {
        $hizmetRows = DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->where('a.salon_id', $salonId)
            ->whereBetween('a.created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
            ->whereNotNull('ah.personel_id')
            ->select('ah.personel_id',
                DB::raw('COUNT(*) as hizmet_say'),
                DB::raw('SUM(ah.fiyat - COALESCE(ah.indirim_tutari,0)) as ciro'))
            ->groupBy('ah.personel_id')
            ->get()->keyBy('personel_id');

        $randevuRows = DB::table('randevu_hizmetler as rh')
            ->join('randevular as r', 'r.id', '=', 'rh.randevu_id')
            ->where('r.salon_id', $salonId)
            ->whereBetween('r.tarih', [$t1, $t2])
            ->whereNotNull('rh.personel_id')
            ->where(function ($q) { $q->where('rh.yardimci_personel', '!=', true)->orWhereNull('rh.yardimci_personel'); })
            ->select('rh.personel_id', DB::raw('COUNT(DISTINCT r.id) as randevu_say'))
            ->groupBy('rh.personel_id')
            ->get()->keyBy('personel_id');

        $personeller = DB::table('salon_personelleri')
            ->where('salon_id', $salonId)->where('aktif', 1)
            ->select('id', 'personel_adi')->get();

        $sonuc = [];
        foreach ($personeller as $p) {
            $h = $hizmetRows[$p->id] ?? null;
            $r = $randevuRows[$p->id] ?? null;
            $sonuc[] = [
                'id' => $p->id,
                'personel_adi' => $p->personel_adi,
                'randevu_say' => $r ? (int) $r->randevu_say : 0,
                'hizmet_say' => $h ? (int) $h->hizmet_say : 0,
                'ciro' => $h ? (float) $h->ciro : 0,
            ];
        }
        usort($sonuc, function ($a, $b) { return $b['ciro'] <=> $a['ciro']; });
        return ['personeller' => $sonuc];
    }

    /** Hizmet karlilik (isletmeRaporlariHizmet ile ayni). */
    public function hizmet($salonId, $t1, $t2)
    {
        $rows = DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->join('hizmetler as h', 'h.id', '=', 'ah.hizmet_id')
            ->where('a.salon_id', $salonId)
            ->whereBetween('a.created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
            ->select('h.id', 'h.hizmet_adi',
                DB::raw('COUNT(*) as adet'),
                DB::raw('SUM(ah.fiyat - COALESCE(ah.indirim_tutari,0)) as ciro'))
            ->groupBy('h.id', 'h.hizmet_adi')
            ->orderByDesc('ciro')->limit(20)->get();

        return ['hizmetler' => $this->objToArr($rows)];
    }

    /** Urun satis (isletmeRaporlariUrun ile ayni). */
    public function urun($salonId, $t1, $t2)
    {
        $rows = DB::table('adisyon_urunler as au')
            ->join('adisyonlar as a', 'a.id', '=', 'au.adisyon_id')
            ->leftJoin('urunler as u', 'u.id', '=', 'au.urun_id')
            ->where('a.salon_id', $salonId)
            ->whereBetween('a.created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
            ->select(
                DB::raw('COALESCE(u.id, 0) as urun_id'),
                DB::raw("COALESCE(u.urun_adi,'Silinmiş Ürün') as urun_adi"),
                DB::raw('SUM(COALESCE(au.adet,1)) as adet'),
                DB::raw('SUM(au.fiyat - COALESCE(au.indirim_tutari,0)) as ciro'))
            ->groupBy('u.id', 'u.urun_adi')
            ->orderByDesc('ciro')->limit(20)->get();

        return ['urunler' => $this->objToArr($rows)];
    }

    /** Musteri ozeti (isletmeRaporlariMusteri ile ayni; hassas top-liste haric). */
    public function musteri($salonId, $t1, $t2)
    {
        $aktifMusteriler = DB::table('randevular')
            ->where('salon_id', $salonId)
            ->whereBetween('tarih', [$t1, $t2])
            ->distinct()->pluck('user_id');
        $toplamAktif = $aktifMusteriler->count();

        $yeniMusteri = 0;
        if (\Schema::hasTable('musteri_portfoy')) {
            $yeniMusteri = DB::table('musteri_portfoy')
                ->where('salon_id', $salonId)
                ->whereBetween('created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
                ->count();
        }
        $tekrarGelen = max(0, $toplamAktif - $yeniMusteri);

        $cinsiyet = ['kadin' => 0, 'erkek' => 0, 'belirsiz' => 0];
        if ($toplamAktif > 0) {
            $cinsiyetRows = DB::table('users')
                ->whereIn('id', $aktifMusteriler)
                ->select('cinsiyet', DB::raw('COUNT(*) as adet'))
                ->groupBy('cinsiyet')->get();
            foreach ($cinsiyetRows as $c) {
                $cins = mb_strtolower($c->cinsiyet ?? '', 'UTF-8');
                if (strpos($cins, 'kad') !== false || $cins === 'k' || $cins === 'female' || $cins === 'f') {
                    $cinsiyet['kadin'] += (int) $c->adet;
                } elseif (strpos($cins, 'erk') !== false || $cins === 'e' || $cins === 'male' || $cins === 'm') {
                    $cinsiyet['erkek'] += (int) $c->adet;
                } else {
                    $cinsiyet['belirsiz'] += (int) $c->adet;
                }
            }
        }

        return [
            'toplam_aktif' => $toplamAktif,
            'yeni_musteri' => $yeniMusteri,
            'tekrar_gelen' => $tekrarGelen,
            'cinsiyet' => $cinsiyet,
        ];
    }

    /** Genel ozet / gun sonu (isletmeRaporlariOzet ile ayni). */
    public function ozet($salonId, $t1, $t2)
    {
        $toplamRandevu = DB::table('randevular')->where('salon_id', $salonId)
            ->whereBetween('tarih', [$t1, $t2])->count();
        $toplamAdisyon = DB::table('adisyonlar')->where('salon_id', $salonId)
            ->whereBetween('created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])->count();
        $satilanUrun = DB::table('adisyon_urunler')
            ->join('adisyonlar', 'adisyonlar.id', '=', 'adisyon_urunler.adisyon_id')
            ->where('adisyonlar.salon_id', $salonId)
            ->whereBetween('adisyonlar.created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])->count();
        $uygulananHizmet = DB::table('adisyon_hizmetler')
            ->join('adisyonlar', 'adisyonlar.id', '=', 'adisyon_hizmetler.adisyon_id')
            ->where('adisyonlar.salon_id', $salonId)
            ->whereBetween('adisyonlar.created_at', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])->count();

        $rows = DB::table('tahsilatlar')
            ->leftJoin('odeme_yontemleri', 'tahsilatlar.odeme_yontemi_id', '=', 'odeme_yontemleri.id')
            ->where('tahsilatlar.salon_id', $salonId)
            ->whereBetween('tahsilatlar.odeme_tarihi', [$t1 . ' 00:00:00', $t2 . ' 23:59:59'])
            ->select('odeme_yontemleri.odeme_yontemi as yontem', DB::raw('SUM(tahsilatlar.tutar) as toplam'))
            ->groupBy('odeme_yontemleri.odeme_yontemi')->get();
        $k = $this->tahsilatKirilim($rows);

        return [
            'toplam_randevu' => $toplamRandevu,
            'toplam_adisyon' => $toplamAdisyon,
            'satilan_urun' => $satilanUrun,
            'uygulanan_hizmet' => $uygulananHizmet,
            'toplam_gelir' => $k['toplam'],
            'nakit' => $k['nakit'],
            'kart' => $k['kart'],
            'havale' => $k['havale'],
            'diger' => $k['diger'],
        ];
    }

    /** Bugunku randevular (dashboardBugun ile ayni; telefon KVKK icin cekilmez). */
    public function bugun($salonId)
    {
        $rows = DB::table('randevular')
            ->leftJoin('users', 'randevular.user_id', '=', 'users.id')
            ->leftJoin('randevu_hizmetler', 'randevu_hizmetler.randevu_id', '=', 'randevular.id')
            ->leftJoin('hizmetler', 'randevu_hizmetler.hizmet_id', '=', 'hizmetler.id')
            ->leftJoin('salon_personelleri', 'randevu_hizmetler.personel_id', '=', 'salon_personelleri.id')
            ->where('randevular.salon_id', $salonId)
            ->where('randevular.tarih', date('Y-m-d'))
            ->select(
                'randevular.saat', 'randevular.durum',
                'users.name as musteri',
                'hizmetler.hizmet_adi as hizmet',
                'salon_personelleri.personel_adi as personel'
            )
            ->orderBy('randevular.saat')->limit(50)->get();

        return ['liste' => $this->objToArr($rows)];
    }

    // ---- yardimcilar ----

    /** Tahsilat satirlarini nakit/kart/havale/diger/toplam kirilimina cevir (dashboardKasa mantigi). */
    protected function tahsilatKirilim($rows)
    {
        $nakit = 0; $kart = 0; $havale = 0; $diger = 0; $toplam = 0;
        foreach ($rows as $r) {
            $tutar = (float) $r->toplam;
            $toplam += $tutar;
            $yontem = mb_strtolower($r->yontem ?? '', 'UTF-8');
            if (strpos($yontem, 'nakit') !== false || strpos($yontem, 'cash') !== false) {
                $nakit += $tutar;
            } elseif (strpos($yontem, 'kart') !== false || strpos($yontem, 'kredi') !== false || strpos($yontem, 'pos') !== false || strpos($yontem, 'card') !== false) {
                $kart += $tutar;
            } elseif (strpos($yontem, 'havale') !== false || strpos($yontem, 'eft') !== false || strpos($yontem, 'transfer') !== false || strpos($yontem, 'iban') !== false) {
                $havale += $tutar;
            } else {
                $diger += $tutar;
            }
        }
        return ['nakit' => $nakit, 'kart' => $kart, 'havale' => $havale, 'diger' => $diger, 'toplam' => $toplam];
    }

    /** stdClass koleksiyonunu diziye cevir (cevap katmani dizi bekliyor). */
    protected function objToArr($rows)
    {
        $out = [];
        foreach ($rows as $r) { $out[] = (array) $r; }
        return $out;
    }
}
