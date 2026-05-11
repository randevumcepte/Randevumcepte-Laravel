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
        {--repair-seans-sayisi : Mevcut adisyon_hizmetler.seans_sayisi NULL ise APS sayisindan doldur}
        {--cleanup-dummy-aps : Tuketilmemis (geldi=0, randevu_id NULL) APS kayitlarini sil}
        {--inspect-musid= : Bir musid icin drklinik detayini indir, basliklar/sutunlar yazdir}
        {--debug-seans-musid= : Bir musid icin seans dusumu satirlarini adim adim logla}
        {--inspect-kasa : kasa_islemleri.aspx icin tarih araliginda gelen tum tip/sutun yapilarini yazdir}
        {--repair-masraf-kategori : Bos isimli MasrafKategorisi kayitlarini drklinik gider tipi ile yeniden adlandir}
        {--repair-gider-dedup : Mevcut Masraflar kayitlarina drklinik hash marker yaz ve eksik gider satirlarini ekle}
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
        if ((bool) $this->option('repair-seans-sayisi')) {
            if (!$salonId) { $this->error('--repair-seans-sayisi icin --salon zorunlu.'); return 1; }
            return $this->repairSeansSayisi((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ((bool) $this->option('cleanup-dummy-aps')) {
            if (!$salonId) { $this->error('--cleanup-dummy-aps icin --salon zorunlu.'); return 1; }
            return $this->cleanupDummyAps((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ($musid = $this->option('inspect-musid')) {
            if (!$username || !$password) { $this->error('--inspect-musid icin --username ve --password zorunlu.'); return 1; }
            return $this->inspectMusid((string) $musid, $username, $password);
        }
        if ($musid = $this->option('debug-seans-musid')) {
            if (!$username || !$password || !$salonId) { $this->error('--debug-seans-musid icin --username, --password, --salon zorunlu.'); return 1; }
            return $this->debugSeansMusid((string) $musid, $username, $password, (int) $salonId);
        }
        if ((bool) $this->option('inspect-kasa')) {
            if (!$username || !$password) { $this->error('--inspect-kasa icin --username/--password zorunlu.'); return 1; }
            return $this->inspectKasaIslemleri($username, $password, $this->option('from'), $this->option('to'));
        }
        if ((bool) $this->option('repair-masraf-kategori')) {
            if (!$username || !$password || !$salonId) { $this->error('--repair-masraf-kategori icin --username/--password/--salon zorunlu.'); return 1; }
            return $this->repairMasrafKategori($username, $password, (int) $salonId, $this->option('from'), $this->option('to'));
        }
        if ((bool) $this->option('repair-gider-dedup')) {
            if (!$username || !$password || !$salonId) { $this->error('--repair-gider-dedup icin --username/--password/--salon zorunlu.'); return 1; }
            return $this->repairGiderDedup($username, $password, (int) $salonId, $this->option('from'), $this->option('to'));
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
        if (in_array('gider', $types) || in_array('masraf', $types)) $importer->importGiderler($this->option('from'), $this->option('to'));
        if (in_array('satis-tahsilat', $types) || in_array('musteri-detay', $types)) {
            $importer->importSatisVeTahsilat($this->option('from'), $this->option('to'));
        }
        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }

    /**
     * Drklinik markerli adisyonlardaki AdisyonHizmetler kayitlarinda
     * seans_sayisi NULL ise:
     *  - bagli AdisyonPaketSeanslar varsa o sayidan doldur
     *  - yoksa default 1
     * Boylece /seanstakip sayfasi (whereNotNull seans_sayisi) gosterir.
     */
    private function repairSeansSayisi($salonId, $dryRun)
    {
        $tAh  = (new \App\AdisyonHizmetler)->getTable();
        $tAd  = (new \App\Adisyonlar)->getTable();
        $tAps = (new \App\AdisyonPaketSeanslar)->getTable();

        if (!\Schema::hasColumn($tAh, 'seans_sayisi')) {
            $this->error("'{$tAh}.seans_sayisi' kolonu yok.");
            return 1;
        }

        $notKol = null;
        foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
            if (\Schema::hasColumn($tAd, $col)) { $notKol = $col; break; }
        }

        $q = \DB::table("{$tAh} as ah")
            ->join("{$tAd} as a", 'ah.adisyon_id', '=', 'a.id')
            ->where('a.salon_id', $salonId)
            ->whereNull('ah.seans_sayisi');
        if ($notKol) $q->where("a.{$notKol}", 'LIKE', '%drklinik:%');
        $rows = $q->select('ah.id', 'ah.hizmet_id')->get();

        $this->line("Salon {$salonId} drklinik adisyon_hizmetler (seans_sayisi NULL): " . $rows->count());
        if ($rows->isEmpty()) { $this->info('Yapilacak is yok.'); return 0; }

        $updated = 0; $defaultBir = 0;
        foreach ($rows as $r) {
            $cnt = \DB::table($tAps)->where('adisyon_hizmet_id', $r->id)->count();
            $seansSayisi = $cnt > 0 ? $cnt : 1;
            if ($cnt === 0) $defaultBir++;
            if (!$dryRun) {
                \DB::table($tAh)->where('id', $r->id)->update(['seans_sayisi' => $seansSayisi]);
            }
            $updated++;
        }
        $tag = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$tag}Tamam. seans_sayisi yazildi: {$updated} (APS yok olan: {$defaultBir} -> seans_sayisi=1).");
        return 0;
    }

    /**
     * Bos isimli (kategoriler kolonu NULL/bos) MasrafKategorisi kayitlarini
     * drklinik'ten yeniden ceken gider listesi ile eslestirip isimlendir.
     */
    /**
     * Drklinik gider listesini cekip her satir icin hash uretir.
     * Mevcut Masraflar kayitlari ile (tarih+tutar+aciklama) ilk eslesen
     * notlar'i NULL/bos olan kayda hash yazar (n-to-n eslestirme).
     * Eslesemeyen satirlar -> yeni Masraflar olarak eklenir.
     */
    private function repairGiderDedup($username, $password, $salonId, $from = null, $to = null)
    {
        $from = $from ?: '2018-01-01';
        $to   = $to   ?: date('Y-m-d');
        $client = new \App\Services\DrklinikClient($username, $password);
        $login = $client->login();
        if (!$login['ok']) { $this->error('Login fail: ' . $login['detail']); return 1; }
        $this->line("Drklinik gider listesi cekiliyor ({$from} - {$to})...");

        $h = $client->postBack('/kasa_islemleri.aspx', 'BTN_GiderHepsi', '', [
            'TB_GiderTarihBas' => date('d.m.Y', strtotime($from)),
            'TB_GiderTarihBit' => date('d.m.Y', strtotime($to)),
            'DDL_GiderTipi'    => '0',
            'DDL_KasaGider'    => '0',
            'DDL_Giderler'     => 'Ödeme Şekli',
            'DDL_GenelTip'     => '',
        ]);
        if (!$h) { $this->error('Sayfa cekilemedi.'); return 1; }

        $bodies = $this->extractAllTables($h);
        $best = ''; $bestTrs = 0;
        foreach ($bodies as $body) {
            $trc = preg_match_all('~<tr[^>]*>~i', $body, $r) ? count($r[0]) : 0;
            if ($trc > $bestTrs) { $bestTrs = $trc; $best = $body; }
        }
        if ($best === '') { $this->error('Gider tablosu bulunamadi.'); return 1; }

        $defaultPers = \App\Personeller::where('salon_id', $salonId)->where('aktif', 1)->orderBy('id')->value('id')
            ?: \App\Personeller::where('salon_id', $salonId)->orderBy('id')->value('id');

        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $best, $rows);
        $rescaned = 0; $linked = 0; $added = 0; $alreadyMarked = 0;

        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 9) continue;

            $tarih = $this->__d($cells[0]);
            $aciklama = $cells[1] ?? '';
            $odemeSekli = $cells[2] ?? '';
            $tutarStr = $cells[3] ?? '';
            $genelTip = $cells[4] ?? '';
            $giderTipi = $cells[5] ?? '';
            $odenen = $cells[7] ?? '';
            $saat = $cells[8] ?? '';
            if (strlen($saat) === 5) $saat .= ':00';

            $tutar = 0.0;
            if (preg_match('~([\d.]+),(\d{1,2})~', $tutarStr, $m)) {
                $tutar = (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
            }
            if (!$tarih || $tutar <= 0) continue;
            $rescaned++;

            $kategoriAdi = trim($giderTipi) ?: (trim($genelTip) ?: 'Diğer');
            $hashKey = md5($tarih . '|' . $tutar . '|' . $saat . '|' . $aciklama . '|' . $kategoriAdi . '|' . $odemeSekli);
            $marker = 'drk:' . $hashKey;

            // Bu marker zaten var mi?
            $hasMark = \DB::table('masraflar')
                ->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%' . $marker . '%')
                ->exists();
            if ($hasMark) { $alreadyMarked++; continue; }

            // Marker yok - bu satira karsi gelen ilk markersiz Masraflar'i bul ve marker'i yaz
            $candidate = \DB::table('masraflar')
                ->where('salon_id', $salonId)
                ->where('tarih', $tarih)
                ->where('tutar', $tutar)
                ->where('aciklama', $aciklama)
                ->where(function ($q) { $q->whereNull('notlar')->orWhere('notlar', ''); })
                ->orderBy('id')
                ->first();
            if ($candidate) {
                \DB::table('masraflar')->where('id', $candidate->id)->update(['notlar' => $marker]);
                $linked++;
                continue;
            }

            // Eslesmedi - yeni Masraflar olustur
            $kategoriId = $this->ensureMasrafKategoriRepair($kategoriAdi);
            $odemeYontemi = $this->odemeYontemiMapRepair($odemeSekli);
            $harcayanId = null;
            if (mb_stripos($kategoriAdi, 'Maaş', 0, 'UTF-8') !== false) {
                $harcayanAd = trim($odenen) ?: trim($aciklama);
                if ($harcayanAd) {
                    $harcayanId = \App\Personeller::where('salon_id', $salonId)
                        ->where('personel_adi', 'LIKE', '%' . $harcayanAd . '%')->value('id');
                }
            }
            if (!$harcayanId) $harcayanId = $defaultPers;

            \DB::table('masraflar')->insert([
                'salon_id' => $salonId,
                'tarih' => $tarih,
                'tutar' => $tutar,
                'aciklama' => $aciklama,
                'notlar' => $marker,
                'masraf_kategori_id' => $kategoriId,
                'odeme_yontemi_id' => $odemeYontemi,
                'harcayan_id' => $harcayanId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $added++;
        }

        $this->info("Tamam. tarama={$rescaned}, markeryazildi={$linked}, yenieklenen={$added}, zatenmarkerli={$alreadyMarked}");
        return 0;
    }

    private function ensureMasrafKategoriRepair($ad)
    {
        $ad = trim((string) $ad) ?: 'Diğer';
        $row = \DB::table('masraf_kategorileri')->where('kategori', $ad)->first();
        if ($row) return $row->id;
        return \DB::table('masraf_kategorileri')->insertGetId([
            'kategori' => $ad,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function odemeYontemiMapRepair($s)
    {
        $s = mb_strtolower($s, 'UTF-8');
        if (strpos($s, 'nakit') !== false) return 1;
        if (strpos($s, 'kredi') !== false) return 2;
        if (strpos($s, 'havale') !== false || strpos($s, 'eft') !== false) return 3;
        return 1;
    }

    private function repairMasrafKategori($username, $password, $salonId, $from = null, $to = null)
    {
        $from = $from ?: '2018-01-01';
        $to   = $to   ?: date('Y-m-d');

        $client = new \App\Services\DrklinikClient($username, $password);
        $login = $client->login();
        if (!$login['ok']) { $this->error('Login fail: ' . $login['detail']); return 1; }
        $this->line("Drklinik'ten gider listesi cekiliyor ({$from} - {$to})...");

        $h = $client->postBack('/kasa_islemleri.aspx', 'BTN_GiderHepsi', '', [
            'TB_GiderTarihBas' => date('d.m.Y', strtotime($from)),
            'TB_GiderTarihBit' => date('d.m.Y', strtotime($to)),
            'DDL_GiderTipi'    => '0',
            'DDL_KasaGider'    => '0',
            'DDL_Giderler'     => 'Ödeme Şekli',
            'DDL_GenelTip'     => '',
        ]);
        if (!$h) { $this->error('Sayfa cekilemedi.'); return 1; }

        $bodies = $this->extractAllTables($h);
        $best = ''; $bestTrs = 0;
        foreach ($bodies as $body) {
            $trc = preg_match_all('~<tr[^>]*>~i', $body, $r) ? count($r[0]) : 0;
            if ($trc > $bestTrs) { $bestTrs = $trc; $best = $body; }
        }
        if ($best === '') { $this->error('Gider tablosu bulunamadi.'); return 1; }

        $table = (new \App\MasrafKategorisi)->getTable();
        $nameCol = null;
        foreach (['kategori', 'kategoriler', 'kategori_adi', 'kategori_ad', 'ad', 'name', 'adi'] as $c) {
            if (\Schema::hasColumn($table, $c)) { $nameCol = $c; break; }
        }
        if (!$nameCol) { $this->error('MasrafKategorisi tablosunda isim kolonu yok.'); return 1; }
        $this->line("Isim kolonu: {$nameCol}");

        // kategori_id -> {oncelik adi}
        $voteByKategori = [];
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $best, $rows);
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 9) continue;
            $tarih = $this->__d($cells[0]);
            $aciklama = $cells[1] ?? '';
            $tutarStr = $cells[3] ?? '';
            $genelTip = $cells[4] ?? '';
            $giderTipi = $cells[5] ?? '';
            $saat = $cells[8] ?? '';
            if (strlen($saat) === 5) $saat .= ':00';

            $tutar = 0.0;
            if (preg_match('~([\d.]+),(\d{1,2})~', $tutarStr, $m)) {
                $tutar = (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
            }
            $kategoriAdi = trim($giderTipi) ?: (trim($genelTip) ?: 'Diğer');
            if (!$tarih || $tutar <= 0) continue;

            // Masraf'i bul
            $masraf = \DB::table('masraflar')
                ->where('salon_id', $salonId)
                ->where('tarih', $tarih)
                ->where('tutar', $tutar)
                ->where('aciklama', $aciklama)
                ->select('masraf_kategori_id')->first();
            if (!$masraf || !$masraf->masraf_kategori_id) continue;
            $kid = $masraf->masraf_kategori_id;
            $voteByKategori[$kid][$kategoriAdi] = ($voteByKategori[$kid][$kategoriAdi] ?? 0) + 1;
        }

        $updated = 0;
        foreach ($voteByKategori as $kid => $names) {
            arsort($names);
            $topName = array_key_first($names);
            $current = \DB::table($table)->where('id', $kid)->value($nameCol);
            if (trim((string) $current) !== '') continue; // dolu olanlara dokunma
            \DB::table($table)->where('id', $kid)->update([$nameCol => $topName]);
            $this->line("  kategori id={$kid} -> '{$topName}'");
            $updated++;
        }
        $this->info("Tamam. {$updated} kategori isimlendirildi.");
        return 0;
    }

    private function __d($s)
    {
        if (preg_match('~^(\d{2})\.(\d{2})\.(\d{4})~', trim((string) $s), $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return null;
    }

    private function inspectKasaIslemleri($username, $password, $from = null, $to = null)
    {
        $from = $from ?: date('Y-m-d', strtotime('-7 days'));
        $to   = $to   ?: date('Y-m-d');

        $client = new \App\Services\DrklinikClient($username, $password);
        $login = $client->login();
        if (!$login['ok']) { $this->error('Login fail: ' . $login['detail']); return 1; }

        // BTN_GiderHepsi -> tum giderler (gider arama icin TB_GiderTarihBas/Bit)
        $h = $client->postBack('/kasa_islemleri.aspx', 'BTN_GiderHepsi', '', [
            'TB_GiderTarihBas' => date('d.m.Y', strtotime($from)),
            'TB_GiderTarihBit' => date('d.m.Y', strtotime($to)),
            'DDL_GiderTipi' => '0',
            'DDL_KasaGider' => '0',
            'DDL_Giderler' => 'Ödeme Şekli',
            'DDL_GenelTip' => '',
        ]);
        if (!$h) { $this->error('Sayfa cekilemedi.'); return 1; }
        $this->line('Dump: ' . $client->dumpDir());

        // Tum tablolari sirayla bul (iç içe tablo durumunda regex eksik bulabilir,
        // o yuzden <table ile </table> pozisyonlarini elle eslestir)
        $tableBodies = $this->extractAllTables($h);
        $this->line("Toplam " . count($tableBodies) . " tablo bulundu.");
        // En cok tr olan tabloyu sec
        $bestBody = ''; $bestTrs = 0; $bestIdx = -1;
        foreach ($tableBodies as $idx => $body) {
            $trc = preg_match_all('~<tr[^>]*>~i', $body, $r) ? count($r[0]) : 0;
            $this->line("  Tablo #{$idx}: {$trc} satir, " . strlen($body) . " byte");
            if ($trc > $bestTrs) { $bestTrs = $trc; $bestBody = $body; $bestIdx = $idx; }
        }
        $this->line("En buyuk tablo: #{$bestIdx}, {$bestTrs} satir");

        // Basliklari yazdir
        preg_match_all('~<th[^>]*>(.*?)</th>~is', $bestBody, $th);
        $headers = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
        $this->line('--- Basliklar ---');
        foreach ($headers as $i => $hd) $this->line("  [{$i}] '{$hd}'");

        // Satirlari topla, GenelTip benzeri kolonu bulup unique degerleri ve ornek satirlari yaz
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $bestBody, $rows);
        $tipler = [];
        $shown = 0;
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            // Sondan onceki kolon GenelTip olabilir
            $tip = $cells[count($cells) - 1] ?: ($cells[count($cells) - 2] ?? '');
            $tipler[$tip] = ($tipler[$tip] ?? 0) + 1;
            if ($shown < 6) {
                $this->line("--- Satir ornek (" . count($cells) . " td, tip='{$tip}') ---");
                foreach ($cells as $i => $c) $this->line("    [{$i}] '" . mb_substr($c, 0, 80) . "'");
                $shown++;
            }
        }
        $this->line('--- Tip dagilimi ---');
        foreach ($tipler as $tip => $sayi) $this->line("  '{$tip}' : {$sayi} satir");

        // Tutar sum analizi
        $sum = 0.0; $sayi = 0;
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cs = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cs[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cs) < 4) continue;
            $tutarStr = $cs[3] ?? '';
            if (preg_match('~([\d.]+),(\d{1,2})~', $tutarStr, $m)) {
                $t = (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
                if ($t > 0) { $sum += $t; $sayi++; }
            }
        }
        $this->line(sprintf('--- TOPLAM (HTML\'den hesap): %d satir, %s TL ---', $sayi, number_format($sum, 2, ',', '.')));
        return 0;
    }

    /**
     * Nested table destekli table extractor. Her ust seviye table'nin
     * icini (body) liste olarak dondur.
     */
    private function extractAllTables($html)
    {
        $bodies = [];
        $offset = 0;
        $len = strlen($html);
        while ($offset < $len) {
            $start = stripos($html, '<table', $offset);
            if ($start === false) break;
            // Acilis tag'ini bul (>)
            $tagEnd = strpos($html, '>', $start);
            if ($tagEnd === false) break;
            $bodyStart = $tagEnd + 1;
            // Iç içe table'lari dengeli say
            $depth = 1;
            $pos = $bodyStart;
            while ($depth > 0 && $pos < $len) {
                $nextOpen = stripos($html, '<table', $pos);
                $nextClose = stripos($html, '</table>', $pos);
                if ($nextClose === false) break;
                if ($nextOpen !== false && $nextOpen < $nextClose) {
                    $depth++;
                    $pos = $nextOpen + 6;
                } else {
                    $depth--;
                    if ($depth === 0) {
                        $bodies[] = substr($html, $bodyStart, $nextClose - $bodyStart);
                        $offset = $nextClose + 8;
                        break;
                    }
                    $pos = $nextClose + 8;
                }
            }
            if ($depth > 0) break; // Eslesmeyen acilis - dur
        }
        return $bodies;
    }

    private function debugSeansMusid($musid, $username, $password, $salonId)
    {
        $client = new \App\Services\DrklinikClient($username, $password);
        $login = $client->login();
        if (!$login['ok']) { $this->error('Login fail'); return 1; }

        $importer = new \App\Imports\DrklinikImporter($client, $salonId, $this->output);
        $userId = null;

        // ensureUserByMusid yerine direct yontem
        $h = $client->getHtml('/musteri.aspx?musid=' . $musid);
        if (strlen($h) < 5000) { $this->error('Sayfa cekilemedi'); return 1; }

        // Telefonu al, user'i bul
        if (preg_match('~name="TB_CepTel"[^>]*value="([^"]*)"~i', $h, $m)) {
            $tel = preg_replace('~\D~', '', $m[1]);
            $tel = ltrim($tel, '0');
            if (substr($tel, 0, 2) === '90') $tel = substr($tel, 2);
            $u = \App\User::where('cep_telefon', $tel)->first();
            if ($u) {
                $userId = $u->id;
                $this->line("User: id={$u->id}, ad='{$u->name}', tel='{$tel}'");
            } else {
                $this->warn("Telefon '{$tel}' icin user bulunamadi");
                return 1;
            }
        } else {
            $this->error('TB_CepTel bulunamadi'); return 1;
        }

        // AdisyonHizmetler kayitlarini listele
        $rows = \DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
            ->where('a.user_id', $userId)
            ->where('a.salon_id', $salonId)
            ->select('ah.id', 'ah.hizmet_id', 'ah.seans_sayisi', 'a.tarih')
            ->orderBy('a.tarih')->get();
        $this->line('Bu user icin AdisyonHizmetler:');
        foreach ($rows as $r) {
            $kullanilan = \DB::table('adisyon_paket_seanslar')->where('adisyon_hizmet_id', $r->id)->count();
            $this->line("  ah_id={$r->id} hizmet_id={$r->hizmet_id} seans_sayisi=" . ($r->seans_sayisi ?? 'NULL') . " kullanilan={$kullanilan} tarih={$r->tarih}");
        }

        // Tablo #3 (Randevular) -> Seans Dusumu satirlarini parse et
        preg_match_all('~<table[^>]*class="[^"]*table[^"]*"[^>]*>(.*?)</table>~is', $h, $tm);
        foreach ($tm[1] as $idx => $body) {
            preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th);
            $headers = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
            if (!in_array('Seans Düşümü', $headers, true)) continue;
            $this->line("--- Tablo #{$idx} (Seans Dusumu var) ---");

            preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $body, $rs);
            $sayac = 0;
            foreach ($rs[1] as $tr) {
                if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
                preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
                if (empty($tds[1])) continue;
                $cells = [];
                foreach ($tds[1] as $tdRaw) {
                    if (preg_match('~<(?:button|input|a)\b[^>]*>~i', $tdRaw)) { $cells[] = ''; continue; }
                    $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                    $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $i = 0; while ($i < count($cells) && $cells[$i] === '') $i++;
                $data = array_values(array_slice($cells, $i));
                if (count($data) < 9) { $this->line("  satir td<9 atlandi"); continue; }
                $tarihStr = preg_replace('~\s*\([^)]+\)~u', '', $data[0]);
                $hizmetlerStr = $data[3] ?? '';
                $seansDusumu = $data[8] ?? '';
                $hasDus = mb_stripos($seansDusumu, 'Düş', 0, 'UTF-8') !== false;
                $this->line("  satir: tarih='{$data[0]}' saat='{$data[1]}' hizmet='{$hizmetlerStr}' dusumu='{$seansDusumu}' hasDus=" . ($hasDus ? '1' : '0'));
                if ($hasDus) $sayac++;
            }
            $this->line("Toplam Dus satiri: {$sayac}");
        }
        return 0;
    }

    private function inspectMusid($musid, $username, $password)
    {
        $client = new \App\Services\DrklinikClient($username, $password);
        $login = $client->login();
        if (!$login['ok']) { $this->error('Login fail: ' . $login['detail']); return 1; }
        $h = $client->getHtml('/musteri.aspx?musid=' . $musid, 'inspect_' . $musid);
        $this->line('Sayfa boyut: ' . strlen($h));
        $this->line('Dump: ' . $client->dumpDir());

        preg_match_all('~<table[^>]*class="[^"]*table[^"]*"[^>]*>(.*?)</table>~is', $h, $tm);
        $this->line('Tablo sayisi: ' . count($tm[1]));
        foreach ($tm[1] as $idx => $body) {
            preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th);
            $headers = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
            $this->line("--- Tablo #{$idx} basliklari (" . count($headers) . ") ---");
            foreach ($headers as $i => $hd) $this->line("  [{$i}] '{$hd}'");

            // Ilk veri satirini ornek olarak goster
            preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $body, $rows);
            $shown = 0;
            foreach ($rows[1] as $tr) {
                if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
                preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
                if (empty($tds[1])) continue;
                $cells = [];
                foreach ($tds[1] as $tdRaw) {
                    $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                    $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                }
                $this->line("  Satir ornek (" . count($cells) . " td):");
                foreach ($cells as $i => $c) $this->line("    [{$i}] '" . mb_substr($c, 0, 80) . "'");
                $shown++;
                if ($shown >= 2) break;
            }
        }
        return 0;
    }

    /**
     * Drklinik markerli adisyonlardaki "tuketilmemis" AdisyonPaketSeanslar
     * kayitlarini sil. Tuketilmemis = geldi != 1 ve randevu_id NULL.
     * Boylece sadece gercekten kullanilmis (geldi=1, randevu_id dolu) seanslar
     * kalir; seanstakip sayfasinda kalan = seans_sayisi - kullanilan dogru hesaplanir.
     */
    private function cleanupDummyAps($salonId, $dryRun)
    {
        $tAh  = (new \App\AdisyonHizmetler)->getTable();
        $tAd  = (new \App\Adisyonlar)->getTable();
        $tAps = (new \App\AdisyonPaketSeanslar)->getTable();

        $notKol = null;
        foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
            if (\Schema::hasColumn($tAd, $col)) { $notKol = $col; break; }
        }

        $apsQ = \DB::table("{$tAps} as aps")
            ->join("{$tAh} as ah", 'aps.adisyon_hizmet_id', '=', 'ah.id')
            ->join("{$tAd} as a", 'ah.adisyon_id', '=', 'a.id')
            ->where('a.salon_id', $salonId)
            ->whereNull('aps.randevu_id')
            ->where(function ($q) {
                $q->whereNull('aps.geldi')->orWhere('aps.geldi', 0);
            });
        if ($notKol) $apsQ->where("a.{$notKol}", 'LIKE', '%drklinik:%');

        $cnt = (clone $apsQ)->count();
        $this->line("Salon {$salonId} drklinik dummy APS sayisi: {$cnt}");
        if ($cnt === 0) { $this->info('Yapilacak is yok.'); return 0; }

        if ($dryRun) { $this->warn('DRY-RUN: silme yapilmadi.'); return 0; }

        // ID listesi cikarip chunk ile sil
        $ids = (clone $apsQ)->select('aps.id')->pluck('id')->all();
        foreach (array_chunk($ids, 1000) as $chunk) {
            \DB::table($tAps)->whereIn('id', $chunk)->delete();
        }
        $this->info("Silindi: " . count($ids));
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
                    $t->adisyon_id = $adId; // dry-run'da memory'de tut, save sadece gercek modda
                    if (!$dryRun) {
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
            if ((float) $t->tutar <= 0) continue;

            if (!$dryRun) {
                $this->dagitVeYazContent($t, $hizmetler, $urunler);
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

    /**
     * Tahsilat tutarini, adisyon kalemlerine fiyat-orantili olarak dagit;
     * tahsilat_hizmetler / tahsilat_urunler kayitlarini uretir. Yuvarlama
     * farki son kaleme yazilir.
     */
    private function dagitVeYazContent($tahsilat, $hizmetler, $urunler)
    {
        $items = [];
        foreach ($hizmetler as $h) {
            $f = (float) ($h->fiyat ?? 0);
            if ($f > 0) $items[] = ['hizmet', $h, $f];
        }
        foreach ($urunler as $u) {
            $f = (float) ($u->fiyat ?? 0) * max(1, (int) ($u->adet ?? 1));
            if ($f > 0) $items[] = ['urun', $u, $f];
        }
        if (empty($items)) return false;
        $toplamFiyat = array_sum(array_column($items, 2));
        if ($toplamFiyat <= 0) return false;
        $oran = (float) $tahsilat->tutar / $toplamFiyat;
        $paylar = []; $payToplam = 0.0;
        foreach ($items as $i => $it) {
            $pay = round($it[2] * $oran, 2);
            $paylar[$i] = $pay;
            $payToplam += $pay;
        }
        $fark = round((float) $tahsilat->tutar - $payToplam, 2);
        if (abs($fark) > 0.001 && !empty($paylar)) {
            $sonIdx = array_key_last($paylar);
            $paylar[$sonIdx] = round($paylar[$sonIdx] + $fark, 2);
        }
        foreach ($items as $i => $it) {
            $pay = $paylar[$i];
            if ($pay <= 0) continue;
            if ($it[0] === 'hizmet') {
                $th = new \App\TahsilatHizmetler();
                $th->tahsilat_id = $tahsilat->id;
                $th->adisyon_hizmet_id = $it[1]->id;
                $th->tutar = $pay;
                $th->save();
            } else {
                $tu = new \App\TahsilatUrunler();
                $tu->tahsilat_id = $tahsilat->id;
                $tu->adisyon_urun_id = $it[1]->id;
                $tu->tutar = $pay;
                $tu->save();
            }
        }
        return true;
    }

    private function findAdisyonForTahsilat($t)
    {
        $sameDate = \App\Adisyonlar::where('user_id', $t->user_id)
            ->where('salon_id', $t->salon_id)
            ->where('tarih', $t->odeme_tarihi)->orderBy('id')->get();
        foreach ($sameDate as $ad) {
            if (abs($this->adisyonTutar($ad) - (float) $t->tutar) < 0.01) return $ad->id;
        }
        if ($sameDate->count() > 0) return $sameDate->first()->id;
        $oncesi = \App\Adisyonlar::where('user_id', $t->user_id)
            ->where('salon_id', $t->salon_id)
            ->whereDate('tarih', '<=', $t->odeme_tarihi)
            ->whereDate('tarih', '>=', date('Y-m-d', strtotime($t->odeme_tarihi . ' -30 days')))
            ->orderBy('tarih', 'desc')->get();
        foreach ($oncesi as $ad) {
            if (abs($this->adisyonTutar($ad) - (float) $t->tutar) < 0.01) return $ad->id;
        }
        return null;
    }

    private function adisyonTutar($ad)
    {
        static $hasCol = null;
        if ($hasCol === null) $hasCol = \Schema::hasColumn((new \App\Adisyonlar)->getTable(), 'toplam_tutar');
        if ($hasCol && (float) ($ad->toplam_tutar ?? 0) > 0) return (float) $ad->toplam_tutar;
        $sumH = (float) \App\AdisyonHizmetler::where('adisyon_id', $ad->id)->sum('fiyat');
        $sumU = (float) \App\AdisyonUrunler::where('adisyon_id', $ad->id)
            ->selectRaw('COALESCE(SUM(fiyat * GREATEST(adet,1)), 0) as t')->value('t');
        return $sumH + $sumU;
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
