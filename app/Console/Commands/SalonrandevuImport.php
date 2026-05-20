<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalonrandevuClient;

class SalonrandevuImport extends Command
{
    protected $signature = 'salonrandevu:import
        {--email= : app.salonrandevu.com email}
        {--password= : sifre}
        {--salon= : Hedef salon_id (randevumcepte)}
        {--analyze : Anasayfa + JS bundle analizi (login gerekmez)}
        {--probe : Login + yaygin endpoint kesfi}
        {--inspect : Login + her endpoint ilk kaydin tam yapisini bas}
        {--only= : virgulle: musteri,hizmet,personel,randevu,tahsilat,paket,urun,gider}
        {--from= : Tarih araligi baslangic YYYY-MM-DD (default 2020-01-01)}
        {--to= : Tarih araligi bitis YYYY-MM-DD (default 2030-12-31)}
        {--proxy= : http://user:pass@host:port}
        {--reset-salonrandevu : [salonrandevu:RefId] markerli randevu+adisyon+kalemleri sil}
        {--dry-run : Reset oncesi sayim}';

    protected $description = 'app.salonrandevu.com hesabindan veri cekip randevumcepte\'ye aktarir (Asama 1: kesif).';

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $email    = $this->option('email');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $analyze  = (bool) $this->option('analyze');
        $probe    = (bool) $this->option('probe');
        $inspect  = (bool) $this->option('inspect');
        $only     = $this->option('only');
        $reset    = (bool) $this->option('reset-salonrandevu');

        if (!$analyze && !$reset && (!$email || !$password)) {
            $this->error('--email ve --password zorunlu (veya --analyze / --reset-salonrandevu verin).');
            return 1;
        }
        if (!$analyze && !$probe && !$inspect && !$salonId) {
            $this->error('Import icin --salon zorunlu. Kesif icin --analyze / --probe / --inspect kullanin.');
            return 1;
        }

        $this->info('Salonrandevu client baslatiliyor...');
        $client = new SalonrandevuClient($email ?: 'x', $password ?: 'x', null, $this->option('proxy'));
        $this->line('Dump dizini: ' . $client->dumpDir());

        if ($analyze) {
            $this->info('Anasayfa + JS bundle analizi...');
            $res = $client->analyze();
            if (!$res['ok']) { $this->error($res['detail']); return 3; }
            $s = $res['summary'];
            $this->line('Anasayfa boyut: ' . $s['home_size'] . ' byte');
            $this->line('--- Asset (' . count($s['assets']) . ') ---');
            foreach (array_slice($s['assets'], 0, 30) as $a) $this->line('  ' . $a);
            $this->line('--- HTML icindeki API path adaylari (' . count($s['api_paths_html']) . ') ---');
            foreach ($s['api_paths_html'] as $p) $this->line('  ' . $p);
            $this->line('--- Tum API path adaylari (bundle taramasindan, ilk 80) ---');
            foreach (array_slice($s['api_paths_all'], 0, 80) as $p) $this->line('  ' . $p);
            $this->line('--- Bundle findings (her bundle\'in icindeki hits) ---');
            foreach ($s['bundle_findings'] as $url => $hits) {
                $this->line('### ' . $url . ' (' . count($hits) . ' hit)');
                foreach (array_slice($hits, 0, 30) as $h) $this->line('  ' . $h);
            }
            $this->info('Detayli dump: ' . $client->dumpDir());
            return 0;
        }

        if ($reset) {
            if (!$salonId) { $this->error('--reset-salonrandevu icin --salon zorunlu.'); return 1; }
            return $this->resetSalonrandevu((int) $salonId, (bool) $this->option('dry-run'));
        }

        $this->info('Login deneniyor...');
        $login = $client->login();
        $this->line('Login sonuc: ' . ($login['ok'] ? 'OK' : 'FAIL') . ' - ' . $login['method']);
        $this->line('Detay: ' . $login['detail']);
        if (!$login['ok']) { $this->error('Login basarisiz. Dump dizinini inceleyin: ' . $client->dumpDir()); return 2; }

        if ($inspect) {
            $this->info('Inspect modu: her endpoint ilk kaydin tam yapisi...');
            $res = $client->inspect();
            foreach ($res as $key => $info) {
                $this->line('');
                $this->line('======== ' . strtoupper($key) . '  (' . $info['path'] . ')  count=' . $info['count'] . ' ========');
                if (!empty($info['meta'])) $this->line('META: ' . json_encode($info['meta'], JSON_UNESCAPED_UNICODE));
                $this->line(json_encode($info['first'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            $this->info('Tam JSON dump: ' . $client->dumpDir() . '/inspect_*.json');
            return 0;
        }

        if ($probe) {
            $this->info('Probe modu: yaygin endpoint\'ler taraniyor...');
            $results = $client->probe();
            foreach ($results as $p => $r) $this->line(str_pad($p, 40) . ' -> ' . $r);
            $this->info('Probe tamam. Dump: ' . $client->dumpDir());
            return 0;
        }

        // Concrete import
        $types = $only ? array_map('trim', explode(',', $only))
                       : ['personel', 'hizmet', 'urun', 'musteri', 'randevu', 'receipt', 'gider'];
        $importer = new \App\Imports\SalonrandevuImporter($client, (int) $salonId, $this->output);

        // Sira: personel -> hizmet -> urun -> musteri -> randevu -> receipt -> gider
        if (in_array('personel', $types)) $importer->importPersoneller();
        if (in_array('hizmet', $types))   $importer->importHizmetler();
        if (in_array('urun', $types))     $importer->importUrunler();
        if (in_array('musteri', $types))  $importer->importMusteriler();
        if (in_array('randevu', $types))  $importer->importRandevular();
        if (in_array('receipt', $types) || in_array('tahsilat', $types) || in_array('paket', $types)) {
            $importer->importReceipts();
        }
        if (in_array('gider', $types)) {
            $importer->importGiderler($this->option('from'), $this->option('to'));
        }

        $this->info('Tamam. Ozet: ' . json_encode($importer->summary(), JSON_UNESCAPED_UNICODE));
        return 0;
    }

    private function resetSalonrandevu($salonId, $dryRun)
    {
        $this->info("Salon {$salonId}: [salonrandevu:*] markerli kayitlar bulunuyor...");
        $rIds = \DB::table('randevular')->where('salon_id', $salonId)
            ->whereRaw("personel_notu LIKE '%[salonrandevu:%'")->pluck('id');
        $aIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
            ->where(function ($q) {
                $q->whereRaw("COALESCE(aciklama,'') LIKE '%[salonrandevu:%'")
                  ->orWhereRaw("COALESCE(adisyon_notu,'') LIKE '%[salonrandevu:%'")
                  ->orWhereRaw("COALESCE(notlar,'') LIKE '%[salonrandevu:%'");
            })->pluck('id');
        $tIds = \DB::table('tahsilatlar')->where('salon_id', $salonId)
            ->whereRaw("COALESCE(notlar,'') LIKE '%[salonrandevu:%'")->pluck('id');
        $this->line("randevu={$rIds->count()} adisyon={$aIds->count()} tahsilat={$tIds->count()}");
        if ($dryRun) { $this->warn('DRY-RUN'); return 0; }

        if ($aIds->count()) {
            $ahIds = \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $aIds)->pluck('id');
            if ($ahIds->count()) \DB::table('adisyon_paket_seanslar')->whereIn('adisyon_hizmet_id', $ahIds)->delete();
            \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $aIds)->delete();
            \DB::table('adisyon_urunler')->whereIn('adisyon_id', $aIds)->delete();
        }
        if ($tIds->count()) \DB::table('tahsilatlar')->whereIn('id', $tIds)->delete();
        if ($aIds->count()) \DB::table('adisyonlar')->whereIn('id', $aIds)->delete();
        if ($rIds->count()) {
            \DB::table('randevu_hizmetler')->whereIn('randevu_id', $rIds)->delete();
            \DB::table('randevular')->whereIn('id', $rIds)->delete();
        }
        $this->info('Reset tamam.');
        return 0;
    }
}
