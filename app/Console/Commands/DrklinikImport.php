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
        {--fix-randevu : Mevcut randevulara eksik oda/personel doldur (yeni eklemez)}';

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
        if (in_array('tahsilat', $types)) $importer->importTahsilatlar($this->option('from'), $this->option('to'));
        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }
}
