<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Planla.co'dan aktarilan salonlarda seans takibi sistemde tutulmamis;
 * seanslar musteri randevularina yazilan NOTLARA (randevular.personel_notu)
 * gore takip edilmis. Bu komut musteri bazli seans takibi raporu uretir.
 *
 * ONEMLI: Bu bir lazer epilasyon / cilt klinigi. Notlardaki "40 - 14 - 6"
 * gibi uclu sayilar CIHAZ AYARIDIR (MS - Joule - Atis), seans numarasi DEGIL.
 * Bu yuzden rapor iki ayri "seans no" uretir:
 *   1) Seans Sira No  : Musteri + hizmet grubunda tarih sirasina gore 1,2,3...
 *                       (Kontrol/ongorusme HARIC). GUVENILIR gercek seans takibi.
 *   2) Nottan Seans   : Sadece notta ACIKCA "seans" kelimesiyle gecen sayi
 *                       ("1 seans", "ilk seans", "3. seans"). Cihaz ayarlarina
 *                       dokunmaz. Cogu notta bos olur, normaldir.
 *
 * Kullanim:
 *   # Ornek not dokumu (pattern kesfi):
 *   /opt/php74/bin/php artisan planla:seans-rapor --salon=355 --ornek=80
 *
 *   # Musteri bazli tam rapor + CSV (Excel):
 *   /opt/php74/bin/php artisan planla:seans-rapor --salon=355 --csv
 */
class PlanlaSeansRapor extends Command
{
    protected $signature = 'planla:seans-rapor
        {--salon=355 : Hedef salon_id}
        {--ornek= : Sadece N adet ornek not dok (pattern kesfi icin). Rapor uretmez.}
        {--csv : Raporu storage/app/planla-seans/ altina CSV olarak yaz}
        {--sadece-notlu=0 : 1 ise sadece notu dolu randevulari dahil et}
        {--limit= : (opsiyonel) Islenecek randevu sayisini sinirla (test icin)}';

    protected $description = 'Planla aktarimli salonda randevu notlarindan musteri bazli seans takibi raporu uretir.';

    public function handle()
    {
        $salonId   = (int) $this->option('salon');
        $sadeceNot = (bool) filter_var($this->option('sadece-notlu'), FILTER_VALIDATE_BOOLEAN);

        $salon = DB::table('salonlar')->where('id', $salonId)->first();
        if (!$salon) {
            $this->error("Salon {$salonId} bulunamadi.");
            return 1;
        }
        $this->info("Salon {$salonId}: " . ($salon->salon_adi ?? '-'));

        // --- Randevulari + musteri ile cek -----------------------------------
        $q = DB::table('randevular as r')
            ->leftJoin('users as u', 'r.user_id', '=', 'u.id')
            ->where('r.salon_id', $salonId);

        if ($sadeceNot) {
            $q->whereNotNull('r.personel_notu')->where('r.personel_notu', '!=', '');
        }
        if ($this->option('limit')) {
            $q->limit((int) $this->option('limit'));
        }

        $randevular = $q->orderBy('u.name')
            ->orderBy('r.tarih')
            ->orderBy('r.saat')
            ->get([
                'r.id', 'r.user_id', 'r.tarih', 'r.saat', 'r.personel_notu',
                'r.randevuya_geldi',
                'u.name as musteri', 'u.cep_telefon as telefon',
            ]);

        if ($randevular->isEmpty()) {
            $this->warn('Kriterlere uyan randevu bulunamadi.');
            return 0;
        }

        // Her randevuya bagli hizmet adlarini toplu cek (tek sorgu)
        $randevuIds = $randevular->pluck('id')->all();
        $hizmetAdlari = []; // randevu_id => ["Hizmet A", "Hizmet B"]
        foreach (array_chunk($randevuIds, 1000) as $chunk) {
            $rows = DB::table('randevu_hizmetler as rh')
                ->leftJoin('hizmetler as h', 'rh.hizmet_id', '=', 'h.id')
                ->whereIn('rh.randevu_id', $chunk)
                ->get(['rh.randevu_id', 'h.hizmet_adi']);
            foreach ($rows as $row) {
                if (!$row->hizmet_adi) continue;
                $hizmetAdlari[$row->randevu_id][] = $row->hizmet_adi;
            }
        }

        // --- ORNEK (pattern kesfi) modu --------------------------------------
        if ($this->option('ornek') !== null && $this->option('ornek') !== '') {
            return $this->ornekDok($randevular, $hizmetAdlari, (int) $this->option('ornek'));
        }

        // --- Her randevuyu zenginlestir --------------------------------------
        // Musteri -> hizmet grubu bazinda seans sira no uretmek icin sayaclar.
        $seansSayac = []; // "user_id|grup" => int

        $detay = [];
        foreach ($randevular as $r) {
            $hizmetListe = isset($hizmetAdlari[$r->id]) ? array_values(array_unique($hizmetAdlari[$r->id])) : [];
            $hizmet = implode(', ', $hizmetListe);

            $tip  = $this->randevuTipi($hizmetListe); // Seans / Kontrol / Ongorusme
            $grup = $this->hizmetGrubu($hizmetListe); // kaba kategori (lazer / cilt / diger)

            $siraNo = null;
            if ($tip === 'Seans') {
                $key = $r->user_id . '|' . $grup;
                $seansSayac[$key] = ($seansSayac[$key] ?? 0) + 1;
                $siraNo = $seansSayac[$key];
            }

            $nottan = $this->nottanSeans($r->personel_notu);

            $detay[] = [
                'user_id'     => $r->user_id,
                'musteri'     => $r->musteri,
                'telefon'     => $r->telefon,
                'tarih'       => $r->tarih,
                'saat'        => substr((string) $r->saat, 0, 5),
                'hizmet'      => $hizmet,
                'grup'        => $grup,
                'tip'         => $tip,
                'seans_sira'  => $siraNo,
                'nottan'      => $nottan,
                'geldi'       => $this->geldiMetin($r->randevuya_geldi),
                'not'         => $this->tekSatir($r->personel_notu),
            ];
        }

        // --- Musteri bazli ozet ----------------------------------------------
        $ozet = []; // user_id => ...
        foreach ($detay as $d) {
            $uid = $d['user_id'];
            if (!isset($ozet[$uid])) {
                $ozet[$uid] = [
                    'musteri' => $d['musteri'], 'telefon' => $d['telefon'],
                    'toplam_randevu' => 0, 'seans' => 0, 'kontrol' => 0, 'ongorusme' => 0,
                    'gruplar' => [], 'hizmetler' => [],
                    'ilk_tarih' => $d['tarih'], 'son_tarih' => $d['tarih'],
                ];
            }
            $o = &$ozet[$uid];
            $o['toplam_randevu']++;
            if ($d['tip'] === 'Seans')      $o['seans']++;
            elseif ($d['tip'] === 'Kontrol') $o['kontrol']++;
            else                             $o['ongorusme']++;
            if ($d['tip'] === 'Seans') {
                $o['gruplar'][$d['grup']] = ($o['gruplar'][$d['grup']] ?? 0) + 1;
            }
            foreach (explode(', ', $d['hizmet']) as $h) {
                $h = trim($h);
                if ($h !== '') $o['hizmetler'][$h] = true;
            }
            if ($d['tarih'] < $o['ilk_tarih']) $o['ilk_tarih'] = $d['tarih'];
            if ($d['tarih'] > $o['son_tarih']) $o['son_tarih'] = $d['tarih'];
            unset($o);
        }

        // --- Konsol ozeti -----------------------------------------------------
        $this->line('');
        $this->info('Toplam randevu    : ' . count($detay));
        $this->info('Benzersiz musteri : ' . count($ozet));
        $tipDagilim = array_count_values(array_column($detay, 'tip'));
        $this->line('Randevu tipi dagilimi:');
        foreach (['Seans', 'Kontrol', 'Ongorusme'] as $t) {
            $this->line("  - {$t}: " . ($tipDagilim[$t] ?? 0));
        }
        $nottanDolu = count(array_filter($detay, function ($d) { return $d['nottan'] !== null; }));
        $this->line("Notta ACIKCA 'seans' sayisi gecen randevu: {$nottanDolu}");

        $this->line('');
        $this->info('Musteri ozeti (ilk 25, en cok seanslilar):');
        $siraliOzet = $ozet;
        uasort($siraliOzet, function ($a, $b) { return $b['seans'] <=> $a['seans']; });
        $satirlar = [];
        $i = 0;
        foreach ($siraliOzet as $o) {
            if ($i++ >= 25) break;
            $satirlar[] = [
                mb_substr((string) $o['musteri'], 0, 24),
                $o['telefon'],
                $o['seans'],
                $o['kontrol'],
                $o['ilk_tarih'],
                $o['son_tarih'],
                mb_substr($this->grupOzet($o['gruplar']), 0, 30),
            ];
        }
        $this->table(
            ['Musteri', 'Telefon', 'Seans', 'Kontrol', 'Ilk', 'Son', 'Gruplar'],
            $satirlar
        );

        // --- CSV yaz ----------------------------------------------------------
        if ($this->option('csv')) {
            $dir = storage_path('app/planla-seans');
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $stamp = date('Ymd_His');

            // Detay: musteri + tarih sirali, her randevu bir satir
            $detayDosya = "{$dir}/salon{$salonId}_seans_detay_{$stamp}.csv";
            $detayRows = [];
            foreach ($detay as $d) {
                $detayRows[] = [
                    $d['musteri'], $d['telefon'], $d['tarih'], $d['saat'],
                    $d['hizmet'], $d['tip'],
                    $d['seans_sira'] !== null ? $d['seans_sira'] : '',
                    $d['nottan'] !== null ? $d['nottan'] : '',
                    $d['geldi'], $d['not'],
                ];
            }
            $this->csvYaz($detayDosya,
                ['Musteri', 'Telefon', 'Tarih', 'Saat', 'Hizmet', 'Tip', 'Seans Sira No', 'Nottan Seans', 'Geldi', 'Not'],
                $detayRows
            );

            // Ozet: musteri bazli
            $ozetDosya = "{$dir}/salon{$salonId}_seans_ozet_{$stamp}.csv";
            $ozetRows = [];
            foreach ($ozet as $o) {
                $ozetRows[] = [
                    $o['musteri'], $o['telefon'],
                    $o['seans'], $o['kontrol'], $o['ongorusme'], $o['toplam_randevu'],
                    $this->grupOzet($o['gruplar']),
                    implode(' | ', array_keys($o['hizmetler'])),
                    $o['ilk_tarih'], $o['son_tarih'],
                ];
            }
            $this->csvYaz($ozetDosya,
                ['Musteri', 'Telefon', 'Toplam Seans', 'Kontrol', 'Ongorusme', 'Toplam Randevu', 'Seans Gruplari', 'Tum Hizmetler', 'Ilk Tarih', 'Son Tarih'],
                $ozetRows
            );

            $this->line('');
            $this->info('CSV yazildi:');
            $this->line("  Detay (randevu satirlari): {$detayDosya}");
            $this->line("  Ozet  (musteri bazli)     : {$ozetDosya}");
        } else {
            $this->line('');
            $this->comment('CSV istiyorsan --csv ekle.');
        }

        return 0;
    }

    // ----------------------------------------------------------------------
    // Yardimcilar
    // ----------------------------------------------------------------------

    /**
     * Randevu tipi: hizmet adlarina gore Seans / Kontrol / Ongorusme.
     * Tedavi hizmeti varsa Seans. Sadece kontrol/ongorusme ise o tip.
     */
    private function randevuTipi(array $hizmetListe)
    {
        if (empty($hizmetListe)) return 'Seans'; // hizmetsiz randevu -> seans say
        $hepsiKontrol = true;
        $hepsiOngorusme = true;
        $varTedavi = false;
        foreach ($hizmetListe as $h) {
            $low = $this->norm($h);
            $kontrol   = (strpos($low, 'kontrol') !== false);
            $ongorusme = (strpos($low, 'ongorusme') !== false || strpos($low, 'on gorusme') !== false);
            if (!$kontrol)   $hepsiKontrol = false;
            if (!$ongorusme) $hepsiOngorusme = false;
            if (!$kontrol && !$ongorusme) $varTedavi = true;
        }
        if ($varTedavi) return 'Seans';
        if ($hepsiOngorusme) return 'Ongorusme';
        if ($hepsiKontrol)   return 'Kontrol';
        return 'Kontrol';
    }

    /**
     * Kaba hizmet grubu (seans sira no'yu bu grup icinde sayariz).
     * Ornek: lazer-govde, lazer-yuz, cilt, diger
     */
    private function hizmetGrubu(array $hizmetListe)
    {
        $metin = $this->norm(implode(' ', $hizmetListe));
        if (strpos($metin, 'lazer') !== false || strpos($metin, 'alex') !== false || strpos($metin, 'epilasyon') !== false) {
            if (strpos($metin, 'yuz') !== false) return 'lazer-yuz';
            return 'lazer';
        }
        if (strpos($metin, 'cilt') !== false || strpos($metin, 'hydrafacial') !== false || strpos($metin, 'bakim') !== false) {
            return 'cilt';
        }
        if (strpos($metin, 'g5') !== false || strpos($metin, 'ems') !== false || strpos($metin, 'masaj') !== false) {
            return 'vucut';
        }
        return 'diger';
    }

    private function grupOzet(array $gruplar)
    {
        if (empty($gruplar)) return '';
        $p = [];
        foreach ($gruplar as $g => $n) $p[] = "{$g}:{$n}";
        return implode(', ', $p);
    }

    /**
     * Sadece notta ACIKCA "seans" kelimesiyle gecen seans numarasini cikarir.
     * Cihaz ayari uclulerine (MS-Joule-Atis) DOKUNMAZ.
     * Donus: int|null
     */
    private function nottanSeans($not)
    {
        $low = $this->norm($not);
        if ($low === '') return null;

        // "ilk seans" -> 1
        if (preg_match('/\bilk\s+seans/u', $low)) return 1;

        // "3. seans" / "3.seans" / "3 seans" / "3 .seans"
        if (preg_match('/(\d{1,2})\s*\.?\s*seans\b/u', $low, $m)) return (int) $m[1];

        // "seans 3" / "seans: 3" / "seans no 3" / "seans : 3 seans"
        if (preg_match('/seans\s*(?:no)?\s*:?\s*(\d{1,2})\b/u', $low, $m)) return (int) $m[1];

        return null;
    }

    /** Turkce buyuk I/i + aksan normalize + kucuk harf. */
    private function norm($s)
    {
        $s = (string) $s;
        $s = str_replace(
            ['İ', 'I', 'ı', 'Ş', 'ş', 'Ğ', 'ğ', 'Ü', 'ü', 'Ö', 'ö', 'Ç', 'ç'],
            ['i', 'i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'],
            $s
        );
        return trim(mb_strtolower($s, 'UTF-8'));
    }

    private function geldiMetin($v)
    {
        if ($v === null || $v === '') return '';
        return ((int) $v === 1) ? 'Geldi' : 'Gelmedi';
    }

    private function tekSatir($s)
    {
        $s = (string) $s;
        $s = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $s);
        return trim(preg_replace('/\s+/u', ' ', $s));
    }

    private function ornekDok($randevular, array $hizmetAdlari, int $n)
    {
        $this->info("--- {$n} adet ornek not ---");
        $sayac = 0;
        foreach ($randevular as $r) {
            if ($sayac >= $n) break;
            $not = trim((string) $r->personel_notu);
            if ($not === '') continue;
            $sayac++;
            $hizmet = isset($hizmetAdlari[$r->id]) ? implode(', ', array_unique($hizmetAdlari[$r->id])) : '-';
            $nottan = $this->nottanSeans($not);
            $this->line(sprintf(
                "%2d) %s | %s %s | %s\n     nottan-seans: %s\n     NOT : %s",
                $sayac,
                $r->musteri ?: '(musteri?)',
                $r->tarih, substr((string) $r->saat, 0, 5),
                $hizmet,
                $nottan !== null ? $nottan : '-',
                $this->tekSatir($not)
            ));
        }
        if ($sayac === 0) $this->warn('Notu dolu randevu bulunamadi.');
        return 0;
    }

    private function csvYaz($path, array $header, array $rows)
    {
        $fp = fopen($path, 'w');
        fwrite($fp, "\xEF\xBB\xBF"); // Excel UTF-8 BOM
        fputcsv($fp, $header);
        foreach ($rows as $row) fputcsv($fp, $row);
        fclose($fp);
    }
}
