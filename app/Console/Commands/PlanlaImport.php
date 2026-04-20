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
        {--only= : Sadece bu tip(ler)i al (virgulle: musteri,hizmet,randevu)}';

    protected $description = 'Planla.co hesabindan musteri/hizmet/randevu verisini cekip randevumcepte DB sine aktarir.';

    public function handle()
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $probe    = (bool) $this->option('probe');
        $only     = $this->option('only');

        if (!$email || !$password) {
            $this->error('--email ve --password zorunlu.');
            return 1;
        }
        if (!$probe && !$salonId) {
            $this->error('Import icin --salon zorunlu. Kesif icin --probe kullanin.');
            return 1;
        }

        $this->info('Planla client baslatiliyor...');
        $client = new PlanlaClient($email, $password);
        $this->line('Dump dizini: ' . $client->dumpDir());

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
