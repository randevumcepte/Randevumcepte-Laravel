<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DrklinikClient;
use App\Imports\DrklinikImporter;

class DrklinikImport extends Command
{
    protected $signature = 'drklinik:import
        {--username= : drklinik kullanici adi}
        {--password= : drklinik sifresi}
        {--salon= : Hedef salon_id (randevumcepte tarafinda)}
        {--analyze : Anasayfa + JS bundle analizi (login olmadan)}
        {--probe : Login + yaygin endpoint kesfi}
        {--only= : virgulle: musteri,hizmet,personel,urun,oda,randevu,tahsilat}
        {--from= : Randevu icin baslangic tarihi YYYY-MM-DD (default 2018-01-01)}
        {--to= : Randevu icin bitis tarihi YYYY-MM-DD (default 2026-12-31)}
        {--fix-randevu : Mevcut randevulara eksik oda/personel doldur (yeni eklemez)}
        {--cleanup-urun-hizmet : Urunler ile ayni isimdeki Hizmetler kayitlarini temizle}
        {--reset-drklinik-satis : Drklinik markerli adisyonlari ve alt kayitlari sil}
        {--repair-tahsilat-icerik : Mevcut tahsilatlari adisyona bagla ve icerigini uret}
        {--dry-run : Sadece raporla, silme}';

    protected $description = 'uygulama.drklinik.net hesabindan veri cekip randevumcepte\'ye aktarir.';

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $username = $this->option('username');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $analyze  = (bool) $this->option('analyze');
        $probe    = (bool) $this->option('probe');
        $only     = $this->option('only');

        if ((bool) $this->option('cleanup-urun-hizmet')) {
            if (!$salonId) { $this->error('--cleanup-urun-hizmet icin --salon zorunlu.'); return 1; }
            return $this->cleanupUrunHizmet((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ((bool) $this->option('reset-drklinik-satis')) {
            if (!$salonId) { $this->error('--reset-drklinik-satis icin --salon zorunlu.'); return 1; }
            return $this->resetDrklinikSatis((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ((bool) $this->option('repair-tahsilat-icerik')) {
            if (!$salonId) { $this->error('--repair-tahsilat-icerik icin --salon zorunlu.'); return 1; }
            return $this->repairTahsilatIcerik((int) $salonId, (bool) $this->option('dry-run'));
        }

        if (!$analyze && (!$username || !$password)) {
            $this->error('--username ve --password zorunlu (analyze disinda).');
            return 1;
        }
        if (!$probe && !$analyze && !$salonId) {
            $this->error('Import icin --salon zorunlu. Kesif icin --probe veya --analyze kullanin.');
            return 1;
        }

        $this->info('Drklinik client baslatiliyor...');
        $client = new DrklinikClient($username ?: 'x', $password ?: 'x');
        $this->line('Dump dizini: ' . $client->dumpDir());

        if ($analyze) {
            $this->info('Anasayfa + JS bundle analizi...');
            $res = $client->analyze();
            if (!$res['ok']) { $this->error($res['detail']); return 3; }
            $s = $res['summary'];
            $this->line('Anasayfa boyut: ' . $s['home_size'] . ' byte');
            $this->line('--- Asset (js/css) ---');
            foreach ($s['assets'] as $a) $this->line('  ' . $a);
            $this->line('--- HTML icindeki API path adaylari ---');
            foreach ($s['api_paths_html'] as $p) $this->line('  ' . $p);
            $this->line('--- Bundle findings (her bundle icin endpoint adaylari) ---');
            foreach ($s['bundle_findings'] as $url => $hits) {
                $this->line('### ' . $url);
                foreach (array_slice($hits, 0, 50) as $h) $this->line('  ' . $h);
            }
            return 0;
        }

        $this->info('Login deneniyor...');
        $login = $client->login();
        $this->line('Login sonuc: ' . ($login['ok'] ? 'OK' : 'FAIL') . ' - ' . $login['method']);
        $this->line('Detay: ' . $login['detail']);
        if (!$login['ok']) { $this->error('Login basarisiz. Dump dizinini inceleyin.'); return 2; }

        if ($probe) {
            $this->info('Probe modu: yaygin endpoint\'ler taraniyor...');
            $results = $client->probe();
            foreach ($results as $p => $r) $this->line(str_pad($p, 40) . ' -> ' . $r);
            return 0;
        }

        $types = $only ? array_map('trim', explode(',', $only)) : ['hizmet', 'personel', 'urun', 'oda', 'randevu'];
        $importer = new DrklinikImporter($client, $salonId, $this->output);
        if (in_array('oda', $types))      $importer->importOdalar();
        if (in_array('personel', $types)) $importer->importPersoneller();
        if (in_array('hizmet', $types))   $importer->importHizmetler();
        if (in_array('urun', $types))     $importer->importUrunler();
        if (in_array('musteri', $types))  $importer->importMusteriler();
        if (in_array('randevu', $types))  $importer->importRandevular($this->option('from'), $this->option('to'));
        if ((bool) $this->option('fix-randevu')) $importer->fixRandevuEksikler($this->option('from'), $this->option('to'));
        if (in_array('satis', $types))    $importer->importSatislar($this->option('from'), $this->option('to'));
        if (in_array('tahsilat', $types)) $importer->importTahsilatlar($this->option('from'), $this->option('to'));
        if (in_array('satis-tahsilat', $types) || in_array('musteri-detay', $types)) {
            $importer->importSatisVeTahsilat($this->option('from'), $this->option('to'));
        }
        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }

    /**
     * Mevcut tahsilatlari adisyona baglayip tahsilat_hizmetler/tahsilat_urunler
     * iceriklerini uret. Drklinik scrape gerektirmez; sadece DB tarafinda calisir.
     */
    private function repairTahsilatIcerik($salonId, $dryRun)
    {
        $tahsilatTbl = (new \App\Tahsilatlar)->getTable();
        $adisyonTbl  = (new \App\Adisyonlar)->getTable();
        $ahTbl       = (new \App\AdisyonHizmetler)->getTable();
        $auTbl       = (new \App\AdisyonUrunler)->getTable();
        $thTbl       = (new \App\TahsilatHizmetler)->getTable();
        $tuTbl       = (new \App\TahsilatUrunler)->getTable();

        $allTahsilat = \App\Tahsilatlar::where('salon_id', $salonId)->get();
        $linkedNew = 0; $propagated = 0; $skipped = 0;

        $this->line("Salon {$salonId} icin {$allTahsilat->count()} tahsilat taraniyor...");

        foreach ($allTahsilat as $idx => $t) {
            // 1) adisyon_id NULL ise eslesen adisyonu bul
            if (!$t->adisyon_id) {
                $adId = $this->findAdisyonForTahsilat($t);
                if ($adId) {
                    if (!$dryRun) {
                        $t->adisyon_id = $adId;
                        $t->save();
                    }
                    $linkedNew++;
                } else {
                    $skipped++;
                    continue;
                }
            }
            // 2) tahsilat_hizmetler/tahsilat_urunler bos mu, doldurabilir miyiz
            if (!$t->adisyon_id) continue;
            $hasContent = \App\TahsilatHizmetler::where('tahsilat_id', $t->id)->exists()
                       || \App\TahsilatUrunler::where('tahsilat_id', $t->id)->exists();
            if ($hasContent) continue;

            $hizmetler = \App\AdisyonHizmetler::where('adisyon_id', $t->adisyon_id)->get();
            $urunler   = \App\AdisyonUrunler::where('adisyon_id', $t->adisyon_id)->get();
            if ($hizmetler->isEmpty() && $urunler->isEmpty()) continue;

            $toplam = 0.0;
            foreach ($hizmetler as $h) $toplam += (float) ($h->fiyat ?? 0);
            foreach ($urunler as $u)   $toplam += (float) ($u->fiyat ?? 0) * max(1, (int) ($u->adet ?? 1));
            if (abs($toplam - (float) $t->tutar) > 0.01) continue;

            if (!$dryRun) {
                foreach ($hizmetler as $h) {
                    $th = new \App\TahsilatHizmetler();
                    $th->tahsilat_id = $t->id;
                    $th->adisyon_hizmet_id = $h->id;
                    $th->tutar = (float) ($h->fiyat ?? 0);
                    $th->save();
                }
                foreach ($urunler as $u) {
                    $tu = new \App\TahsilatUrunler();
                    $tu->tahsilat_id = $t->id;
                    $tu->adisyon_urun_id = $u->id;
                    $tu->tutar = (float) ($u->fiyat ?? 0) * max(1, (int) ($u->adet ?? 1));
                    $tu->save();
                }
            }
            $propagated++;
            if (($idx + 1) % 200 === 0) {
                $this->line("  ..{$idx} taranan, link={$linkedNew} prop={$propagated} skip={$skipped}");
            }
        }
        $tag = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$tag}Tamam. yeni-link={$linkedNew}, icerik-uretildi={$propagated}, eslesmeyen={$skipped}");
        return 0;
    }

    private function findAdisyonForTahsilat($t)
    {
        $base = \App\Adisyonlar::where('user_id', $t->user_id)->where('salon_id', $t->salon_id);
        $hit = (clone $base)->where('tarih', $t->odeme_tarihi)
            ->whereRaw('ABS(toplam_tutar - ?) < 0.01', [$t->tutar])
            ->orderBy('id')->first();
        if ($hit) return $hit->id;
        $hit = (clone $base)->where('tarih', $t->odeme_tarihi)->orderBy('id')->first();
        if ($hit) return $hit->id;
        $hit = (clone $base)
            ->whereDate('tarih', '<=', $t->odeme_tarihi)
            ->whereDate('tarih', '>=', date('Y-m-d', strtotime($t->odeme_tarihi . ' -30 days')))
            ->whereRaw('ABS(toplam_tutar - ?) < 0.01', [$t->tutar])
            ->orderBy('tarih', 'desc')->first();
        return $hit ? $hit->id : null;
    }

    /**
     * Drklinik markerli adisyonlari ve bagli alt kayitlari (adisyon_hizmetler,
     * adisyon_urunler, adisyon_paket_seanslar) sil. Tahsilatlar.adisyon_id ->
     * NULL (tahsilat tablosuna dokunulmaz, satis-tahsilat yeniden import edilince
     * dedup'tan gecip kalir).
     */
    private function resetDrklinikSatis($salonId, $dryRun)
    {
        $db = \DB::connection();
        $tAd  = (new \App\Adisyonlar)->getTable();
        $tAh  = (new \App\AdisyonHizmetler)->getTable();
        $tAu  = (new \App\AdisyonUrunler)->getTable();
        $tAps = (new \App\AdisyonPaketSeanslar)->getTable();
        $tTh  = (new \App\Tahsilatlar)->getTable();

        $notKol = null;
        foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
            if (\Schema::hasColumn($tAd, $col)) { $notKol = $col; break; }
        }
        if (!$notKol) { $this->error('Adisyonlar not kolonu tespit edilemedi.'); return 1; }

        $ids = $db->table($tAd)
            ->where('salon_id', $salonId)
            ->where($notKol, 'LIKE', '%drklinik:%')
            ->pluck('id')->all();
        $this->line("Salon {$salonId} drklinik adisyonlari: " . count($ids) . " ({$notKol} kolonundan)");
        if (empty($ids)) { $this->info('Silinecek adisyon yok.'); return 0; }

        $cntAh = $db->table($tAh)->whereIn('adisyon_id', $ids)->count();
        $cntAu = $db->table($tAu)->whereIn('adisyon_id', $ids)->count();
        $cntAps = \Schema::hasColumn($tAps, 'adisyon_hizmet_id')
            ? $db->table($tAps)
                ->whereIn('adisyon_hizmet_id', function ($q) use ($tAh, $ids) {
                    $q->select('id')->from($tAh)->whereIn('adisyon_id', $ids);
                })->count() : 0;
        $cntT = $db->table($tTh)->whereIn('adisyon_id', $ids)->count();

        $this->line("  adisyon_hizmetler: {$cntAh} (silinecek)");
        $this->line("  adisyon_urunler: {$cntAu} (silinecek)");
        $this->line("  adisyon_paket_seanslar: {$cntAps} (silinecek)");
        $this->line("  tahsilatlar (adisyon_id NULL'a cekilecek): {$cntT}");

        if ($dryRun) { $this->warn('DRY-RUN: silme yapilmadi.'); return 0; }

        $db->beginTransaction();
        try {
            // paket seanslar -> adisyon_hizmet_id ile
            if (\Schema::hasColumn($tAps, 'adisyon_hizmet_id')) {
                $ahIds = $db->table($tAh)->whereIn('adisyon_id', $ids)->pluck('id')->all();
                if (!empty($ahIds)) {
                    foreach (array_chunk($ahIds, 1000) as $ck) {
                        $db->table($tAps)->whereIn('adisyon_hizmet_id', $ck)->delete();
                    }
                }
            }
            foreach (array_chunk($ids, 1000) as $chunk) {
                $db->table($tAh)->whereIn('adisyon_id', $chunk)->delete();
                $db->table($tAu)->whereIn('adisyon_id', $chunk)->delete();
                $db->table($tTh)->whereIn('adisyon_id', $chunk)->update(['adisyon_id' => null]);
                $db->table($tAd)->whereIn('id', $chunk)->delete();
            }
            $db->commit();
            $this->info('Reset tamam. Yeniden --only=satis-tahsilat ile import edebilirsiniz.');
            return 0;
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->error('Hata: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Urunler tablosunda kayitli isimle ayni Hizmetler kayitlarini temizle.
     * Bagli randevu_hizmetler -> hizmet_id NULL,
     * adisyon_hizmetler / tahsilat_hizmetler / salon_hizmetler -> sil,
     * hizmetler -> sil.
     */
    private function cleanupUrunHizmet($salonId, $dryRun)
    {
        $db = \DB::connection();
        $tHizmet  = (new \App\Hizmetler)->getTable();
        $tSh      = (new \App\SalonHizmetler)->getTable();
        $tRh      = (new \App\RandevuHizmetler)->getTable();
        $tAh      = (new \App\AdisyonHizmetler)->getTable();
        $tTh      = (new \App\TahsilatHizmetler)->getTable();
        $tUrun    = (new \App\Urunler)->getTable();
        $trKey = function ($s) {
            $s = (string) $s;
            $s = preg_replace('~\s*\((?:H|U|P)\)\s*$~iu', '', $s);
            $s = mb_strtolower($s, 'UTF-8');
            $s = preg_replace('/\p{M}+/u', '', $s);
            $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
            $s = preg_replace('~[^a-z0-9]+~', ' ', $s);
            return trim($s);
        };

        $urunler = $db->table($tUrun)->where('salon_id', $salonId)->pluck('urun_adi')->all();
        $urunSet = [];
        foreach ($urunler as $u) {
            $k = $trKey($u);
            if ($k !== '') $urunSet[$k] = $u;
        }
        $this->line("Salon {$salonId}: " . count($urunSet) . " unique urun.");

        $hizmetler = $db->table($tHizmet)
            ->where(function ($q) use ($salonId) {
                $q->where('salon_id', $salonId)->orWhere('ozel_hizmet', 1);
            })
            ->select('id', 'hizmet_adi')
            ->get();

        $dupIds = [];
        $rows = [];
        foreach ($hizmetler as $h) {
            $k = $trKey($h->hizmet_adi);
            if ($k !== '' && isset($urunSet[$k])) {
                $dupIds[] = $h->id;
                $rows[] = [$h->id, $h->hizmet_adi, $urunSet[$k]];
            }
        }

        $this->line('Eslesen Hizmetler kayit sayisi: ' . count($dupIds));
        foreach (array_slice($rows, 0, 50) as $r) {
            $this->line("  hizmet_id={$r[0]}  hizmet_adi='{$r[1]}'  <-> urun_adi='{$r[2]}'");
        }
        if (count($rows) > 50) $this->line('  (ilk 50 gosterildi)');

        if (empty($dupIds)) { $this->info('Temizlenecek kayit yok.'); return 0; }

        $cntRh = $db->table($tRh)->whereIn('hizmet_id', $dupIds)->count();
        $cntAh = $db->table($tAh)->whereIn('hizmet_id', $dupIds)->count();
        $thHasHizmetId = \Schema::hasTable($tTh) && \Schema::hasColumn($tTh, 'hizmet_id');
        $cntTh = $thHasHizmetId
            ? $db->table($tTh)->whereIn('hizmet_id', $dupIds)->count() : 0;
        $cntSh = $db->table($tSh)->whereIn('hizmet_id', $dupIds)->count();
        $this->line("Randevu_hizmetler (dokunulmayacak): {$cntRh}");
        $this->line("Adisyon_hizmetler (silinecek): {$cntAh}");
        $this->line("Tahsilat_hizmetler (silinecek): {$cntTh}");
        $this->line("Salon_sunulan_hizmetler (silinecek): {$cntSh}");
        $this->line('Hizmetler (dokunulmayacak): randevu_hizmetler FK referanslari korunsun diye birakiliyor.');

        if ($dryRun) { $this->warn('DRY-RUN: kayitlar silinmedi. Gercek calistirma icin --dry-run kaldirin.'); return 0; }

        $db->beginTransaction();
        try {
            // randevu_hizmetler ve hizmetler tablolarina dokunmuyoruz:
            // randevu kayitlarinin gorunumunu degistirmemek ve FK orphan
            // olusmamasi icin.
            $db->table($tAh)->whereIn('hizmet_id', $dupIds)->delete();
            if ($thHasHizmetId) {
                $db->table($tTh)->whereIn('hizmet_id', $dupIds)->delete();
            }
            $db->table($tSh)->whereIn('hizmet_id', $dupIds)->delete();
            $db->commit();
            $this->info('Temizlik tamam.');
            return 0;
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->error('Hata: ' . $e->getMessage());
            return 1;
        }
    }
}
