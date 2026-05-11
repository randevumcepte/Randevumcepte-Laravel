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
        {--dump-file= : Tarayici scripti ile indirilen tek JSON dump dosyasi (clients + bookings)}
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

        $dumpFile = $this->option('dump-file');
        $fromFile = $this->option('from-file');
        if (!$analyze && !$token && !$dumpFile && !$fromFile && (!$username || !$password)) {
            $this->error('--username ve --password zorunlu (veya --token / --dump-file / --from-file verin).');
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
        if ($dumpFile = $this->option('dump-file')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            return $this->importFromDump($dumpFile, (int) $salonId);
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
     * Tarayici scriptinin indirdigi tek JSON dump dosyasini import et.
     * Yapi: { clients: [{id, name, phone_number_local, ...}], bookings: { CLIENT_ID: [{session, date, time_text, services_staff_text, products_text, total_amount, total_payment, payment_methods_text, ...}] } }
     */
    private function importFromDump($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: {$file}"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j) || !isset($j['clients'])) { $this->error('Gecersiz JSON.'); return 1; }
        $clients = $j['clients'];
        $bookings = $j['bookings'] ?? [];
        $this->line('Clients: ' . count($clients) . ', Bookings users: ' . count($bookings));

        $apiController = app(\App\Http\Controllers\ApiController::class);

        // 1) Musteri aktarimi
        $idMap = []; $musteriEklenen = 0; $musteriHata = 0;
        foreach ($clients as $idx => $c) {
            $payload = [
                'musteriAdi'   => $c['name'] ?? '',
                'telefon'      => $c['phone_number_local'] ?? $c['phone_number'] ?? '',
                'ePosta'       => $c['email'] ?? '',
                'dogumTarihi'  => $c['birthdate'] ?? '',
                'cinsiyet'     => $c['gender_text'] ?? '',
                'notlar'       => $c['notes'] ?? '',
                'medeniDurum'  => '', 'meslek' => '', 'adres' => '',
                'kayitTarihi'  => $c['created_at'] ?? '',
                'salonId'      => $salonId,
                'salonAppyId'  => $c['id'],
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->aktarimMusteriKontrol($req);
                $userId = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                if ($userId && ctype_digit($userId)) {
                    $idMap[$c['id']] = $userId;
                    $musteriEklenen++;
                } else {
                    $musteriHata++;
                    \Log::warning('[Salonappy] musteri eklenemedi', ['client' => $c['id'], 'resp' => substr($userId, 0, 200)]);
                }
            } catch (\Throwable $e) {
                $musteriHata++;
                \Log::warning('[Salonappy] musteri exception', ['client' => $c['id'], 'err' => $e->getMessage()]);
            }
            if (($idx + 1) % 100 === 0) $this->line("  musteri {$idx}/" . count($clients) . " eklenen={$musteriEklenen} hata={$musteriHata}");
        }
        $this->info("Musteri aktarimi: eklenen={$musteriEklenen}, hata={$musteriHata}");

        // 2) Randevu + Adisyon + Tahsilat
        $randevuEklenen = 0; $randevuAtlanan = 0; $tahsilatEklenen = 0; $randevuDedup = 0;
        $i = 0;
        foreach ($bookings as $clientId => $bookList) {
            $userId = $idMap[$clientId] ?? null;
            if (!$userId) { $randevuAtlanan += count($bookList); continue; }
            foreach ($bookList as $b) {
                $i++;
                try {
                    $tarih = $b['date'] ?? '';
                    $saatStr = $b['time_text'] ?? '00:00';
                    $saat = strlen($saatStr) === 5 ? $saatStr . ':00' : $saatStr;

                    // Idempotent dedup - 3 yontem:
                    // a) [salonappy:session] markeri zaten varsa (Adisyon notlar / Randevu personel_notu)
                    // b) Ayni user + salon + tarih + saat Randevu varsa
                    $session = $b['session'] ?? '';
                    $markerExists = false;
                    if ($session) {
                        $marker = '[salonappy:' . $session . ']';
                        $markerExists = \DB::table('randevular')->where('salon_id', $salonId)
                            ->where('user_id', $userId)
                            ->where('personel_notu', 'LIKE', '%' . $marker . '%')
                            ->exists();
                    }
                    if ($markerExists) { $randevuDedup++; continue; }

                    // Saat dedup
                    $sameTime = \DB::table('randevular')->where('salon_id', $salonId)
                        ->where('user_id', $userId)
                        ->where('tarih', $tarih)
                        ->where('saat', $saat)
                        ->exists();
                    if ($sameTime) { $randevuDedup++; continue; }

                    $hizmetler = $this->parseSalonappyServicesStaff($b['services_staff_text'] ?? '', $b['total_amount'] ?? 0);
                    $urunler   = $this->parseSalonappyProducts($b['products_text'] ?? '');
                    $payload = [
                        'userId'       => $userId,
                        'salonId'      => $salonId,
                        'tarih'        => $tarih,
                        'saat'         => $saatStr,
                        'geldi'        => $b['showup_text'] ?? '',
                        'durum'        => $b['status_text'] ?? '',
                        'olusturan'    => $b['created_by'] ?? '',
                        'olusturulma'  => $b['created_at'] ?? '',
                        'notlar'       => '[salonappy:' . $session . ']',
                        'hizmetler'    => $hizmetler,
                        'urunler'      => $urunler,
                    ];
                    $req = new \Illuminate\Http\Request($payload);
                    $resp = $apiController->salonAppyAdisyonRandevuEkle($req);
                    $adisyonId = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                    $randevuEklenen++;

                    // Tahsilat - total_payment > 0 ise (idempotent: user+salon+tarih+tutar+yontem dedup)
                    if (!empty($b['total_payment']) && $b['total_payment'] > 0 && $adisyonId && ctype_digit($adisyonId)) {
                        $methodsRaw = $b['payment_methods_text'] ?? '';
                        $methods = $methodsRaw ? array_map('trim', explode(',', $methodsRaw)) : ['Nakit'];
                        $perAmount = round(((float) $b['total_payment']) / max(1, count($methods)), 2);
                        foreach ($methods as $m) {
                            $existsT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('user_id', $userId)
                                ->where('odeme_tarihi', $tarih)
                                ->where('tutar', $perAmount)
                                ->exists();
                            if ($existsT) continue;
                            $tReq = new \Illuminate\Http\Request([
                                'userId'         => $userId,
                                'adisyonId'      => $adisyonId,
                                'odemeTarihi'    => $tarih,
                                'tahsilatTutari' => $perAmount,
                                'odemeYontemi'   => $m,
                                'salonId'        => $salonId,
                            ]);
                            try {
                                $apiController->salonAppyTahsilatEkle($tReq);
                                $tahsilatEklenen++;
                            } catch (\Throwable $e) {}
                        }
                    }
                } catch (\Throwable $e) {
                    $randevuAtlanan++;
                    \Log::warning('[Salonappy] randevu hata', ['session' => $b['session'] ?? '?', 'err' => $e->getMessage()]);
                }
                if ($i % 200 === 0) $this->line("  randevu {$i} eklenen={$randevuEklenen} dedup={$randevuDedup} atlanan={$randevuAtlanan} tahsilat={$tahsilatEklenen}");
            }
        }
        $this->info("Randevu aktarimi: eklenen={$randevuEklenen}, dedup={$randevuDedup}, atlanan={$randevuAtlanan}, tahsilat={$tahsilatEklenen}");
        return 0;
    }

    /**
     * "Hizmet1 (Personel1), Hizmet2 (Personel2)" -> [{hizmet,personel,fiyat,sureDk}, ...]
     * total_amount esit olarak hizmetlere dagitilir.
     */
    private function parseSalonappyServicesStaff($text, $totalAmount)
    {
        $out = [];
        if (!$text) return $out;
        // split by ", " ama parantezin disinda olanlar
        // Basit yaklasim: regex "Hizmet (Personel)"
        if (preg_match_all('~([^,()]+?)\s*\(([^)]+)\)~u', $text, $m, PREG_SET_ORDER)) {
            $count = count($m);
            $each = $count > 0 ? round(((float) $totalAmount) / $count, 2) : 0;
            foreach ($m as $row) {
                $out[] = [
                    'hizmet'   => trim($row[1]),
                    'personel' => trim($row[2]),
                    'fiyat'    => $each,
                    'sureDk'   => 30,
                ];
            }
        }
        return $out;
    }

    private function parseSalonappyProducts($text)
    {
        $out = [];
        if (!$text) return $out;
        if (preg_match_all('~([^,()]+?)\s*\(([^)]+)\)~u', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $out[] = [
                    'urun'     => trim($row[1]),
                    'personel' => trim($row[2]),
                    'fiyat'    => 0,
                    'adet'     => 1,
                ];
            }
        }
        return $out;
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
