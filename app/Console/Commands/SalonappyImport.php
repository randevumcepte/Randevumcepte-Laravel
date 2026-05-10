<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalonappyClient;

class SalonappyImport extends Command
{
    protected $signature = 'salonappy:import
        {--username= : Salonappy kullanici adi (telefon/eposta)}
        {--password= : Salonappy sifresi}
        {--token= : Tarayicidan kopyalanan Bearer token (login atlanir)}
        {--salon= : Hedef salon_id (randevumcepte tarafinda)}
        {--analyze : Anasayfa + JS bundle analizi (login olmadan)}
        {--probe : Login + yaygin endpoint kesfi}
        {--only= : virgulle: personel,hizmet}
        {--from-file= : Tarayicidan kopyalanan JSON\'ları icerek dizin (staff.json, services.json, service_durations.json, service_prices.json, staff_services.json)}
        {--proxy= : http://user:pass@host:port residential proxy (CF/IP block icin)}';

    protected $description = 'webapp.salonappy.com hesabindan veri cekip randevumcepte\'ye aktarir.';

    public function handle()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        $username = $this->option('username');
        $password = $this->option('password');
        $token    = $this->option('token');
        $salonId  = $this->option('salon');
        $analyze  = (bool) $this->option('analyze');
        $probe    = (bool) $this->option('probe');
        $only     = $this->option('only');

        if (!$analyze && !$token && (!$username || !$password)) {
            $this->error('--username ve --password zorunlu (veya --token verin).');
            return 1;
        }
        if (!$probe && !$analyze && !$salonId) {
            $this->error('Import icin --salon zorunlu. Kesif icin --probe veya --analyze kullanin.');
            return 1;
        }

        $this->info('Salonappy client baslatiliyor...');
        $client = new SalonappyClient($username ?: 'x', $password ?: 'x', null, $this->option('proxy'));
        $this->line('Dump dizini: ' . $client->dumpDir());
        if ($this->option('proxy') || env('SALONAPPY_PROXY')) {
            $this->line('Proxy aktif: ' . preg_replace('~://[^@]+@~', '://***@', $this->option('proxy') ?: env('SALONAPPY_PROXY')));
        }

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
            $this->line('--- Bundle findings ---');
            foreach ($s['bundle_findings'] as $url => $hits) {
                $this->line('### ' . $url);
                foreach ($hits as $h) $this->line('  ' . $h);
            }
            return 0;
        }

        // Dosya bazli mod (CF IP block durumunda)
        if ($fromFile = $this->option('from-file')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            return $this->importFromFiles($fromFile, (int) $salonId, $only);
        }

        if ($token) {
            $client->setBearer($token);
            $this->info('Token verildi, login atlandi.');
        } else {
            $this->info('Login deneniyor...');
            $login = $client->login();
            $this->line('Login sonuc: ' . ($login['ok'] ? 'OK' : 'FAIL') . ' - ' . $login['method']);
            $this->line('Detay: ' . $login['detail']);
            if (!$login['ok']) { $this->error('Login basarisiz. Dump dizinini inceleyin.'); return 2; }
        }

        if ($probe) {
            $this->info('Probe modu: yaygin endpoint\'ler taraniyor...');
            $results = $client->probe();
            foreach ($results as $p => $r) $this->line(str_pad($p, 40) . ' -> ' . $r);
            return 0;
        }

        $this->error('Import metodlari henuz tanimli degil. Once --analyze veya --probe ile endpoint kesfedin.');
        return 0;
    }

    /**
     * JSON dosyalarindan import (CF IP block durumunda kullanici tarayicidan
     * kopyalayip dosya olarak verir).
     * Beklenen dosyalar (varsa): staff.json, services.json,
     * service_durations.json, service_prices.json, staff_services.json
     */
    private function importFromFiles($dir, $salonId, $only)
    {
        if (!is_dir($dir)) { $this->error("Dizin bulunamadi: {$dir}"); return 1; }
        $this->line('Dosya dizini: ' . $dir);

        $loaded = [];
        foreach (['staff','services','service_durations','service_prices','staff_services'] as $name) {
            $f = rtrim($dir, '/') . '/' . $name . '.json';
            if (!file_exists($f)) { $this->line("  {$name}.json yok"); continue; }
            $j = json_decode(file_get_contents($f), true);
            if (!is_array($j)) { $this->warn("  {$name}.json parse hatali"); continue; }
            // data altinda olabilir
            if (isset($j['data']) && is_array($j['data'])) $j = $j['data'];
            $loaded[$name] = $j;
            $cnt = is_array($j) && isset($j[0]) ? count($j) : (is_array($j) ? count($j) : 0);
            $this->line("  {$name}.json yuklendi ({$cnt} kayit)");
        }

        if (empty($loaded)) { $this->warn('Hicbir dosya yuklenmedi.'); return 0; }

        // Once yapilari yazdir (importer'i yapiya gore yazmadan once)
        foreach ($loaded as $name => $data) {
            $first = is_array($data) && isset($data[0]) ? $data[0] : null;
            if ($first && is_array($first)) {
                $this->line("--- {$name} ilk kayit anahtarlari: " . implode(', ', array_keys($first)));
            }
        }

        // TODO: gerçek import mantığı - JSON yapısını gördükten sonra
        $this->warn('Yapi gosterimi tamamlandi. Importer kodunu JSON formatina gore yazacagiz.');
        return 0;
    }
}
