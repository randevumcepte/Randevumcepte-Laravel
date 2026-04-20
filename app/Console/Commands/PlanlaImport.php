<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlanlaClient;
use App\Imports\PlanlaImporter;

class PlanlaImport extends Command
{
    protected $signature = 'planla:import
        {--email= : Planla.co giris e-mail}
        {--password= : Planla.co sifresi}
        {--salon= : Hedef salon_id (randevumcepte tarafinda)}
        {--probe : Sadece login + endpoint kesif; veri yazmaz}
        {--analyze : Login olmadan Site.js bundle\'ini indirip icinden endpoint ve payload cikarir}
        {--only= : Sadece bu tip(ler)i al (virgulle: musteri,hizmet,randevu)}';

    protected $description = 'Planla.co hesabindan musteri/hizmet/randevu verisini cekip randevumcepte DB sine aktarir.';

    public function handle()
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $probe    = (bool) $this->option('probe');
        $analyze  = (bool) $this->option('analyze');
        $only     = $this->option('only');

        if (!$analyze && (!$email || !$password)) {
            $this->error('--email ve --password zorunlu (analyze disinda).');
            return 1;
        }
        if (!$probe && !$analyze && !$salonId) {
            $this->error('Import icin --salon zorunlu. Kesif icin --probe veya --analyze kullanin.');
            return 1;
        }

        $this->info('Planla client baslatiliyor...');
        $client = new PlanlaClient($email ?: 'x', $password ?: 'x');
        $this->line('Dump dizini: ' . $client->dumpDir());

        if ($analyze) {
            $this->info('Site.js bundle indiriliyor ve analiz ediliyor...');
            $res = $client->analyzeBundle();
            if (!$res['ok']) {
                $this->error('Bundle indirilemedi: ' . $res['detail']);
                return 3;
            }
            $s = $res['summary'];
            $this->line('Bundle boyut: ' . $s['bundle_size'] . ' byte');
            $this->line('Login path adaylari: ' . implode(', ', $s['login_paths']));
            $this->line('Planla URL\'leri: ' . implode(', ', $s['planla_urls']));
            $this->line('Toplam /api endpoint: ' . count($s['api_endpoints']));
            $this->line('Ilk 30 endpoint:');
            foreach (array_slice($s['api_endpoints'], 0, 30) as $e) {
                $this->line('  ' . $e);
            }
            $this->info('Tam analiz: ' . $client->dumpDir() . '/bundle_analysis.body');
            return 0;
        }

        $this->info('Login deneniyor...');
        $login = $client->login();
        $this->line('Login sonuc: ' . ($login['ok'] ? 'OK' : 'FAIL') . ' - ' . $login['method']);
        $this->line('Detay: ' . $login['detail']);
        if (!$login['ok']) {
            $this->error('Login basarisiz. Dump dizinini inceleyin.');
            return 2;
        }

        if ($probe) {
            $this->info('Probe modu: yaygin endpoint\'ler taraniyor...');
            $results = $client->probe();
            foreach ($results as $p => $r) {
                $this->line(str_pad($p, 40) . ' -> ' . $r);
            }
            $this->info('Probe tamam. Dump dizinindeki *.body dosyalarini inceleyin, data donen endpoint\'leri bildirin; importer mapping i ona gore bitirilecek.');
            return 0;
        }

        $types = $only ? array_map('trim', explode(',', $only)) : ['hizmet', 'musteri', 'randevu'];
        $importer = new PlanlaImporter($client, $salonId, $this->output);

        if (in_array('hizmet', $types))  $importer->importHizmetler();
        if (in_array('musteri', $types)) $importer->importMusteriler();
        if (in_array('randevu', $types)) $importer->importRandevular();

        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }
}
