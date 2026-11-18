<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Asistanın Size Kazandırdıkları" raporu (isletme paneli > WhatsApp Asistanı).
 * Hesap SAF fonksiyonda (ozetHesapla; testli); veriYukle yalniz satirlari toplar.
 */
class WaAsistanRapor
{
    const PERSONEL_DK_MESAJ = 1.5; // personelin bir mesaji cevaplamasi (okuma + yazma) ~ dakika

    /** Donem raporu. $bas/$bit: 'Y-m-d H:i:s' [bas, bit). */
    public static function ozet(int $isletmeId, string $bas, string $bit): array
    {
        return self::ozetHesapla(self::veriYukle($isletmeId, $bas, $bit), WaPaket::paket($isletmeId));
    }

    public static function veriYukle(int $isletmeId, string $bas, string $bit): array
    {
        $v = ['cevaplar' => [], 'oturumlar' => [], 'randevular' => [], 'geri_kazanim' => [], 'gorseller' => []];
        if (!WaAsistanVeri::semaGaranti()) return $v;
        $v['cevaplar'] = DB::table('wa_asistan_cevaplar')->where('isletme_id', $isletmeId)
            ->where('created_at', '>=', $bas)->where('created_at', '<', $bit)
            ->get(['oturum_id', 'kaynak', 'ai', 'sure_sn', 'mesai_disi', 'aksiyon', 'randevu_id', 'maliyet_usd', 'created_at'])
            ->map(function ($r) { return (array) $r; })->all();
        $v['oturumlar'] = DB::table('wa_asistan_oturumlari')->where('isletme_id', $isletmeId)
            ->where('created_at', '>=', $bas)->where('created_at', '<', $bit)
            ->get(['id', 'anahtar', 'ad', 'telefon', 'musteri_tel', 'sonuc', 'randevu_id', 'fiyat_verildi', 'bilgi_verildi', 'saat_onerildi',
                   'gorsel_sayisi', 'son_hizmet_ad', 'memnuniyet', 'ret', 'sessiz_bitis', 'sessiz_neden', 'geri_kazanim_id', 'son_mesaj_at', 'created_at'])
            ->map(function ($r) { return (array) $r; })->all();
        $rids = array_values(array_unique(array_filter(array_merge(
            array_column($v['oturumlar'], 'randevu_id'), array_column($v['cevaplar'], 'randevu_id')))));
        if ($rids) {
            $tutar = DB::table('randevu_hizmetler')->whereIn('randevu_id', $rids)->groupBy('randevu_id')
                ->select('randevu_id', DB::raw('SUM(fiyat) as t'))->pluck('t', 'randevu_id')->all();
            foreach (DB::table('randevular as r')->leftJoin('users as u', 'u.id', '=', 'r.user_id')->whereIn('r.id', $rids)
                ->get(['r.id', 'r.durum', 'r.randevuya_geldi', 'r.created_at', 'u.created_at as u_created']) as $r) {
                $v['randevular'][(int) $r->id] = [
                    'tutar' => (float) ($tutar[$r->id] ?? 0),
                    'geldi' => $r->randevuya_geldi !== null && (int) $r->randevuya_geldi === 1,
                    'iptal' => (int) $r->durum === 2,
                    // Musteri kaydi randevudan en fazla 1 saat once -> asistan uzerinden gelen YENI musteri
                    'yeni' => $r->u_created && strtotime($r->u_created) >= strtotime($r->created_at) - 3600,
                ];
            }
        }
        if (WaGeriKazanim::semaGaranti()) {
            $v['geri_kazanim'] = DB::table(WaGeriKazanim::TABLO)->where('isletme_id', $isletmeId)
                ->where('gonderildi_at', '>=', $bas)->where('gonderildi_at', '<', $bit)
                ->get(['id', 'durum', 'cevap_at', 'randevu_id', 'geldi', 'tutar', 'ad', 'telefon', 'hizmet_ad', 'neden', 'gonderildi_at'])
                ->map(function ($r) { return (array) $r; })->all();
        }
        if (WaGorselDanisman::semaGaranti()) {
            $v['gorseller'] = DB::table('wa_gorsel_analizler')->where('isletme_id', $isletmeId)
                ->where('created_at', '>=', $bas)->where('created_at', '<', $bit)
                ->get(['id', 'oturum_id', 'durum', 'toplam', 'onbellek'])->map(function ($r) { return (array) $r; })->all();
        }
        return $v;
    }

    /** SAF. $v = veriYukle cikisi; $paket = wa_asistan_paketleri satiri (nesne) ya da null. */
    public static function ozetHesapla(array $v, $paket = null): array
    {
        $cev = $v['cevaplar']; $otr = $v['oturumlar']; $rnd = $v['randevular']; $gk = $v['geri_kazanim']; $gor = $v['gorseller'] ?? [];

        // --- cevaplar
        $toplamCevap = count($cev);
        $ai = count(array_filter($cev, function ($c) { return (int) $c['ai'] === 1; }));
        $sureler = array_values(array_filter(array_map(function ($c) {
            return ($c['kaynak'] !== 'geri_kazanim' && $c['sure_sn'] !== null) ? (int) $c['sure_sn'] : null;
        }, $cev), function ($x) { return $x !== null; }));
        sort($sureler);
        $ortSure = $sureler ? (int) round(array_sum($sureler) / count($sureler)) : null;
        $medyan = $sureler ? $sureler[(int) floor((count($sureler) - 1) / 2)] : null;
        $mesaiDisiCevap = count(array_filter($cev, function ($c) { return (int) $c['mesai_disi'] === 1 && $c['kaynak'] !== 'geri_kazanim'; }));
        $aksiyonSay = function ($a) use ($cev) { return count(array_filter($cev, function ($c) use ($a) { return $c['aksiyon'] === $a; })); };
        $maliyet = array_sum(array_map(function ($c) { return (float) $c['maliyet_usd']; }, $cev));

        // --- asistanin aldigi randevular (sohbet basina tek)
        $asistanRandevu = [];
        foreach ($otr as $o) if (!empty($o['randevu_id'])) $asistanRandevu[(int) $o['randevu_id']] = (int) $o['id'];
        foreach ($cev as $c) if ($c['aksiyon'] === 'randevu' && !empty($c['randevu_id'])) $asistanRandevu[(int) $c['randevu_id']] = (int) ($c['oturum_id'] ?? 0);
        // geri kazanma randevulari ayri sayilir (cift sayma yok)
        $gkRandevu = array_flip(array_filter(array_map(function ($g) { return (int) ($g['randevu_id'] ?? 0); }, $gk)));
        $mesaiDisiRandevu = [];
        foreach ($cev as $c) if ($c['aksiyon'] === 'randevu' && !empty($c['randevu_id']) && (int) $c['mesai_disi'] === 1) $mesaiDisiRandevu[(int) $c['randevu_id']] = 1;
        $r = ['adet' => 0, 'tutar' => 0.0, 'geldi' => 0, 'geldi_tutar' => 0.0, 'iptal' => 0, 'yeni_musteri' => 0, 'mesai_disi' => 0, 'mesai_disi_tutar' => 0.0];
        foreach ($asistanRandevu as $rid => $_) {
            if (isset($gkRandevu[$rid])) continue; // geri kazanma kartinda sayilir
            $x = $rnd[$rid] ?? null;
            if (!$x) continue;
            if ($x['iptal']) { $r['iptal']++; continue; }
            $r['adet']++; $r['tutar'] += $x['tutar'];
            if ($x['geldi']) { $r['geldi']++; $r['geldi_tutar'] += $x['tutar']; }
            if ($x['yeni']) $r['yeni_musteri']++;
            if (isset($mesaiDisiRandevu[$rid])) { $r['mesai_disi']++; $r['mesai_disi_tutar'] += $x['tutar']; }
        }

        // --- geri kazanma
        $g = ['yazildi' => count($gk), 'cevap' => 0, 'randevu' => 0, 'geldi' => 0, 'tutar' => 0.0, 'beklenen_tutar' => 0.0, 'ret' => 0];
        foreach ($gk as $x) {
            if (!empty($x['cevap_at'])) $g['cevap']++;
            if ($x['durum'] === 'ret') $g['ret']++;
            if (!empty($x['randevu_id'])) { $g['randevu']++; $g['beklenen_tutar'] += (float) $x['tutar']; }
            if ((int) ($x['geldi'] ?? 0) === 1) { $g['geldi']++; $g['tutar'] += (float) $x['tutar']; }
        }

        // --- sohbetler / huni / kacan firsatlar
        $yazisma = count($otr);
        $bilgiAldi = 0; $fiyatAlip = 0; $fiyatRandevu = 0; $aktarBekleyen = []; $kacan = []; $memnun = 0; $memnunDegil = 0; $hizmetSay = [];
        $now = time();
        foreach ($otr as $o) {
            $bilgi = !empty($o['fiyat_verildi']) || !empty($o['bilgi_verildi']) || !empty($o['saat_onerildi']) || (int) $o['gorsel_sayisi'] > 0;
            if ($bilgi) $bilgiAldi++;
            if (!empty($o['fiyat_verildi'])) { $fiyatAlip++; if (!empty($o['randevu_id'])) $fiyatRandevu++; }
            if ($o['memnuniyet'] !== null && $o['memnuniyet'] !== '') { if ((int) $o['memnuniyet'] === 1) $memnun++; else $memnunDegil++; }
            if (!empty($o['son_hizmet_ad'])) $hizmetSay[$o['son_hizmet_ad']] = ($hizmetSay[$o['son_hizmet_ad']] ?? 0) + 1;
            if ($o['sonuc'] === 'aktar' && $o['sessiz_neden'] === 'aktar' && $o['sessiz_bitis'] && strtotime($o['sessiz_bitis']) > $now) $aktarBekleyen[] = $o;
            if ($bilgi && empty($o['randevu_id']) && empty($o['geri_kazanim_id']) && !in_array($o['sonuc'], ['randevu', 'guncelle', 'iptal', 'teyit', 'aktar'], true)) $kacan[] = $o;
        }
        arsort($hizmetSay);
        $gorselFiyat = count(array_filter($gor, function ($x) { return $x['durum'] === 'fiyat'; }));
        $gorselOturum = array_flip(array_filter(array_map(function ($x) { return (int) $x['oturum_id']; }, $gor)));
        $gorselRandevu = 0;
        foreach ($otr as $o) if (isset($gorselOturum[(int) $o['id']]) && !empty($o['randevu_id'])) $gorselRandevu++;
        $saatler = [];
        foreach ($cev as $c) { $h = (int) date('G', strtotime($c['created_at'])); $saatler[$h] = ($saatler[$h] ?? 0) + 1; }
        arsort($saatler);

        // --- kazanc / geri donus
        $kazanc = $r['geldi_tutar'] + $g['tutar'];                       // gerceklesen (geldi isaretli)
        $beklenen = $r['tutar'] + $g['beklenen_tutar'];                   // alinan randevularin toplami
        $paketUcret = $paket ? (float) $paket->aylik_ucret : 0.0;
        $roiTaban = $kazanc > 0 ? $kazanc : $beklenen;
        $cozulen = $yazisma ? $yazisma - count(array_filter($otr, function ($o) { return $o['sonuc'] === 'aktar'; })) : 0;

        return [
            'cevap' => [
                'toplam' => $toplamCevap, 'ucretsiz' => $toplamCevap - $ai, 'ai' => $ai,
                'ort_sure_sn' => $ortSure, 'medyan_sure_sn' => $medyan, 'mesai_disi' => $mesaiDisiCevap,
                'personel_saat' => round($toplamCevap * self::PERSONEL_DK_MESAJ / 60, 1),
                'maliyet_usd' => round($maliyet, 4),
            ],
            'randevu' => $r + ['guncelle' => $aksiyonSay('guncelle'), 'iptal_islem' => $aksiyonSay('iptal'), 'teyit' => $aksiyonSay('teyit')],
            'geri_kazanim' => $g,
            'sohbet' => [
                'yazisma' => $yazisma, 'bilgi_aldi' => $bilgiAldi, 'fiyat_aldi' => $fiyatAlip, 'fiyattan_randevu' => $fiyatRandevu,
                'cozulen_orani' => $yazisma ? (int) round($cozulen / $yazisma * 100) : null,
                'memnun' => $memnun, 'memnun_degil' => $memnunDegil,
                'memnuniyet_orani' => ($memnun + $memnunDegil) ? (int) round($memnun / ($memnun + $memnunDegil) * 100) : null,
                'aktar_bekleyen' => count($aktarBekleyen),
            ],
            'gorsel' => ['toplam' => count($gor), 'fiyat_verildi' => $gorselFiyat, 'randevu' => $gorselRandevu,
                'onbellek' => count(array_filter($gor, function ($x) { return (int) $x['onbellek'] === 1; }))],
            'huni' => ['yazdi' => $yazisma, 'bilgi' => $bilgiAldi, 'randevu' => $r['adet'] + $g['randevu'], 'geldi' => $r['geldi'] + $g['geldi']],
            'kazanc' => [
                'gerceklesen' => round($kazanc, 2), 'beklenen' => round($beklenen, 2), 'mesai_disi' => round($r['mesai_disi_tutar'], 2),
                'paket_ucret' => $paketUcret, 'kat' => ($paketUcret > 0 && $roiTaban > 0) ? round($roiTaban / $paketUcret, 1) : null,
            ],
            'populer_hizmet' => array_slice($hizmetSay, 0, 5, true),
            'yogun_saat' => array_slice($saatler, 0, 3, true),
            'kacan' => array_map(function ($o) {
                return ['oturum_id' => (int) $o['id'], 'ad' => $o['ad'], 'telefon' => $o['musteri_tel'] ?: $o['telefon'], 'hizmet' => $o['son_hizmet_ad'],
                    'neden' => (int) $o['gorsel_sayisi'] > 0 ? 'Fotoğrafa fiyat aldı' : (!empty($o['fiyat_verildi']) ? 'Fiyat aldı' : (!empty($o['saat_onerildi']) ? 'Saatlere baktı' : 'Bilgi aldı')),
                    'tarih' => $o['son_mesaj_at'], 'ret' => (int) $o['ret']];
            }, array_slice(array_reverse($kacan), 0, 50)),
            'aktar_bekleyen' => array_map(function ($o) {
                return ['oturum_id' => (int) $o['id'], 'ad' => $o['ad'], 'telefon' => $o['musteri_tel'] ?: $o['telefon'], 'tarih' => $o['son_mesaj_at']];
            }, $aktarBekleyen),
        ];
    }
}
