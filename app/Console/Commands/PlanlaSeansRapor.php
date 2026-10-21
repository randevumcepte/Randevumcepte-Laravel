<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Planla.co'dan aktarilan salonlarda seans takibi sistemde tutulmamis;
 * seanslar musteri randevularina yazilan NOTLARA (randevular.personel_notu)
 * gore takip edilmis. Bu komut o notlari okuyup seans takibi raporu uretir.
 *
 * Kullanim:
 *   # 1) ONCE notlarin gercek formatini gor (pattern kesfi):
 *   /opt/php74/bin/php artisan planla:seans-rapor --salon=355 --ornek=80
 *
 *   # 2) Tam rapor + CSV (Excel'de acilir):
 *   /opt/php74/bin/php artisan planla:seans-rapor --salon=355 --csv
 *
 * Notlardan cikarilan alanlar:
 *   - seans_no      : "3. seans", "seans 3", "3/10", "3-10" gibi ifadelerden
 *   - seans_toplam  : "3/10" / "3-10" gibi ifadelerdeki ikinci sayi
 *   - seans_notu_mu : notta seans/kez gecen bir ipucu var mi
 */
class PlanlaSeansRapor extends Command
{
    protected $signature = 'planla:seans-rapor
        {--salon=355 : Hedef salon_id}
        {--ornek= : Sadece N adet ornek not dok (pattern kesfi icin). Rapor uretmez.}
        {--csv : Raporu storage/app/planla-seans/ altina CSV olarak yaz}
        {--sadece-notlu=1 : Sadece notu dolu randevulari dahil et (0=hepsi)}
        {--limit= : (opsiyonel) Islenecek randevu sayisini sinirla (test icin)}';

    protected $description = 'Planla aktarimli salonda randevu notlarindan seans takibi raporu uretir.';

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

        // --- Randevulari + musteri + hizmet adlari ile cek -------------------
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
        $hizmetAdlari = []; // randevu_id => "Hizmet A, Hizmet B"
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

        // --- Tam rapor: her randevuyu parse et -------------------------------
        $detay = [];
        $patternSayac = ['seans_no' => 0, 'kesirli' => 0, 'anahtar_kelime' => 0, 'bos' => 0];

        foreach ($randevular as $r) {
            $parse = $this->notParse($r->personel_notu);
            $hizmet = isset($hizmetAdlari[$r->id]) ? implode(', ', array_unique($hizmetAdlari[$r->id])) : '';

            if ($parse['seans_toplam'] !== null) $patternSayac['kesirli']++;
            elseif ($parse['seans_no'] !== null) $patternSayac['seans_no']++;
            elseif ($parse['seans_ipucu'])       $patternSayac['anahtar_kelime']++;
            else                                  $patternSayac['bos']++;

            $detay[] = [
                'musteri'       => $r->musteri,
                'telefon'       => $r->telefon,
                'tarih'         => $r->tarih,
                'saat'          => substr((string) $r->saat, 0, 5),
                'hizmet'        => $hizmet,
                'geldi'         => $this->geldiMetin($r->randevuya_geldi),
                'seans_no'      => $parse['seans_no'],
                'seans_toplam'  => $parse['seans_toplam'],
                'seans_notu_mu' => $parse['seans_ipucu'] ? 'evet' : '',
                'not'           => $this->tekSatir($r->personel_notu),
            ];
        }

        // --- Musteri bazli ozet ----------------------------------------------
        $ozet = []; // musteri+tel => ...
        foreach ($detay as $d) {
            $key = $d['musteri'] . '|' . $d['telefon'];
            if (!isset($ozet[$key])) {
                $ozet[$key] = [
                    'musteri' => $d['musteri'], 'telefon' => $d['telefon'],
                    'randevu_sayisi' => 0, 'notlu_randevu' => 0,
                    'max_seans_no' => null, 'son_toplam' => null,
                    'hizmetler' => [], 'ilk_tarih' => $d['tarih'], 'son_tarih' => $d['tarih'],
                ];
            }
            $o = &$ozet[$key];
            $o['randevu_sayisi']++;
            if ($d['not'] !== '') $o['notlu_randevu']++;
            if ($d['seans_no'] !== null) {
                $o['max_seans_no'] = max((int) $o['max_seans_no'], (int) $d['seans_no']);
            }
            if ($d['seans_toplam'] !== null) $o['son_toplam'] = $d['seans_toplam'];
            if ($d['hizmet'] !== '') {
                foreach (explode(', ', $d['hizmet']) as $h) $o['hizmetler'][$h] = true;
            }
            if ($d['tarih'] < $o['ilk_tarih']) $o['ilk_tarih'] = $d['tarih'];
            if ($d['tarih'] > $o['son_tarih']) $o['son_tarih'] = $d['tarih'];
            unset($o);
        }

        // --- Konsol ozeti -----------------------------------------------------
        $this->line('');
        $this->info("Toplam randevu    : " . count($detay));
        $this->info("Benzersiz musteri : " . count($ozet));
        $this->line("Not pattern dagilimi:");
        $this->line("  - X/Y kesirli seans (seans no + toplam): {$patternSayac['kesirli']}");
        $this->line("  - Sadece seans no (3. seans / seans 3)  : {$patternSayac['seans_no']}");
        $this->line("  - Seans ipucu var ama sayi yok          : {$patternSayac['anahtar_kelime']}");
        $this->line("  - Seansla ilgisiz / bos                 : {$patternSayac['bos']}");

        // Ilk birkac musteri ozetini konsolda goster
        $this->line('');
        $this->info('Musteri ozeti (ilk 20):');
        $satirlar = [];
        $i = 0;
        foreach ($ozet as $o) {
            if ($i++ >= 20) break;
            $satirlar[] = [
                $o['musteri'], $o['telefon'], $o['randevu_sayisi'],
                $o['max_seans_no'] !== null ? $o['max_seans_no'] : '-',
                $o['son_toplam'] !== null ? $o['son_toplam'] : '-',
                mb_substr(implode(', ', array_keys($o['hizmetler'])), 0, 40),
            ];
        }
        $this->table(
            ['Musteri', 'Telefon', 'Rnd', 'MaxSeans', 'Toplam', 'Hizmetler'],
            $satirlar
        );

        // --- CSV yaz ----------------------------------------------------------
        if ($this->option('csv')) {
            $dir = storage_path('app/planla-seans');
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $stamp = date('Ymd_His');

            $detayDosya = "{$dir}/salon{$salonId}_seans_detay_{$stamp}.csv";
            $this->csvYaz($detayDosya,
                ['Musteri', 'Telefon', 'Tarih', 'Saat', 'Hizmet', 'Geldi', 'Seans No', 'Seans Toplam', 'Seans Notu mu', 'Not'],
                array_map('array_values', $detay)
            );

            $ozetDosya = "{$dir}/salon{$salonId}_seans_ozet_{$stamp}.csv";
            $ozetSatir = [];
            foreach ($ozet as $o) {
                $ozetSatir[] = [
                    $o['musteri'], $o['telefon'], $o['randevu_sayisi'], $o['notlu_randevu'],
                    $o['max_seans_no'] !== null ? $o['max_seans_no'] : '',
                    $o['son_toplam'] !== null ? $o['son_toplam'] : '',
                    implode(' | ', array_keys($o['hizmetler'])),
                    $o['ilk_tarih'], $o['son_tarih'],
                ];
            }
            $this->csvYaz($ozetDosya,
                ['Musteri', 'Telefon', 'Randevu Sayisi', 'Notlu Randevu', 'Max Seans No', 'Son Toplam Seans', 'Hizmetler', 'Ilk Tarih', 'Son Tarih'],
                $ozetSatir
            );

            $this->line('');
            $this->info("CSV yazildi:");
            $this->line("  Detay: {$detayDosya}");
            $this->line("  Ozet : {$ozetDosya}");
        } else {
            $this->line('');
            $this->comment('CSV istiyorsan --csv ekle.');
        }

        return 0;
    }

    /**
     * Ornek not dokumu: gercek not formatlarini gormek ve parse'in nasil
     * cozdugunu dogrulamak icin.
     */
    private function ornekDok($randevular, array $hizmetAdlari, int $n)
    {
        $this->info("--- {$n} adet ornek not (pattern kesfi) ---");
        $sayac = 0;
        foreach ($randevular as $r) {
            if ($sayac >= $n) break;
            $not = trim((string) $r->personel_notu);
            if ($not === '') continue;
            $sayac++;
            $parse = $this->notParse($not);
            $hizmet = isset($hizmetAdlari[$r->id]) ? implode(', ', array_unique($hizmetAdlari[$r->id])) : '-';
            $this->line(sprintf(
                "%2d) %s | %s %s | %s\n     parse-> no:%s toplam:%s ipucu:%s\n     NOT : %s",
                $sayac,
                $r->musteri ?: '(musteri?)',
                $r->tarih, substr((string) $r->saat, 0, 5),
                $hizmet,
                $parse['seans_no'] !== null ? $parse['seans_no'] : '-',
                $parse['seans_toplam'] !== null ? $parse['seans_toplam'] : '-',
                $parse['seans_ipucu'] ? 'E' : '-',
                $this->tekSatir($not)
            ));
        }
        if ($sayac === 0) $this->warn('Notu dolu randevu bulunamadi.');
        else $this->info("Toplam {$sayac} ornek gosterildi. Pattern'ler bekledigin gibi degilse soyle, parser'i ona gore guncelleyelim.");
        return 0;
    }

    /**
     * Bir nottan seans bilgisini cikarir.
     * Donus: ['seans_no'=>int|null, 'seans_toplam'=>int|null, 'seans_ipucu'=>bool]
     */
    private function notParse($not)
    {
        $out = ['seans_no' => null, 'seans_toplam' => null, 'seans_ipucu' => false];
        $not = trim((string) $not);
        if ($not === '') return $out;

        // Turkce buyuk I/i normalizasyonu + kucuk harf
        $low = mb_strtolower(str_replace(['İ', 'I'], ['i', 'i'], $not), 'UTF-8');

        $seansKelime = (strpos($low, 'seans') !== false)
            || preg_match('/\bkez\b/u', $low)
            || preg_match('/\bkur\b/u', $low);
        if ($seansKelime) $out['seans_ipucu'] = true;

        // 1) Kesirli: "3/10", "3 / 10", "3-10" (seans baglamiysa)
        //    seans/kez kelimesi yaninda ya da tek basina X/Y
        if (preg_match('#(\d{1,3})\s*[/\-]\s*(\d{1,3})#u', $low, $m)) {
            $no = (int) $m[1];
            $top = (int) $m[2];
            // mantikli aralik: no<=toplam ve toplam makul (<=60)
            if ($top > 0 && $top <= 60 && $no >= 0 && $no <= $top) {
                $out['seans_no'] = $no;
                $out['seans_toplam'] = $top;
                $out['seans_ipucu'] = true;
                return $out;
            }
        }

        // 2) "3. seans" / "3.seans" / "3 seans" / "3.kez" / "3. kur"
        if (preg_match('/(\d{1,3})\s*\.?\s*(seans|kez|kur)\b/u', $low, $m)) {
            $out['seans_no'] = (int) $m[1];
            $out['seans_ipucu'] = true;
            return $out;
        }

        // 3) "seans 3" / "seans: 3" / "seans no 3"
        if (preg_match('/(seans|kez|kur)\s*(?:no|:)?\s*(\d{1,3})\b/u', $low, $m)) {
            $out['seans_no'] = (int) $m[2];
            $out['seans_ipucu'] = true;
            return $out;
        }

        return $out;
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

    private function csvYaz($path, array $header, array $rows)
    {
        $fp = fopen($path, 'w');
        // Excel'in UTF-8 Turkce karakterleri dogru okumasi icin BOM
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $header);
        foreach ($rows as $row) fputcsv($fp, $row);
        fclose($fp);
    }
}
