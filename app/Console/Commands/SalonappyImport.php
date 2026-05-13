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
        {--reset-salonappy : [salonappy:session] markerli randevu+adisyon+kalemleri sil (musteriler kalir)}
        {--dry-run : Reset/import oncesi sadece sayim}
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
        $resetMode = (bool) $this->option('reset-salonappy');
        if (!$analyze && !$token && !$dumpFile && !$fromFile && !$resetMode && (!$username || !$password)) {
            $this->error('--username ve --password zorunlu (veya --token / --dump-file / --from-file / --reset-salonappy verin).');
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
        if ((bool) $this->option('reset-salonappy')) {
            if (!$salonId) { $this->error('--reset-salonappy icin --salon zorunlu.'); return 1; }
            return $this->resetSalonappy((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ($dumpFile = $this->option('dump-file')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            // v5 yapısını otomatik dedect et: visits + bookingDetails
            $peek = json_decode(file_get_contents($dumpFile), true);
            if (isset($peek['visits']) && isset($peek['bookingDetails'])) {
                return $this->importFromDumpV5($dumpFile, (int) $salonId);
            }
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
     * v5 dump (visits + bookingDetails): itemized hizmet/urun/tahsilat/paket
     * Yapi:
     * {
     *   clients: [...],
     *   clientDetails: { CLIENT_ID: {... notes ...} },
     *   visits: [{session, client_name, phone_number, date, time_text, ...}],
     *   bookingDetails: { SESSION: { details: {client_id, notes, ...},
     *                                 services: [{service_text, staff_name, price, duration}],
     *                                 product_sales: [...], package_sales: [...],
     *                                 package_usages: [...], payments: [{amount, payment_method_text, date}] } }
     * }
     */
    private function importFromDumpV5($file, $salonId)
    {
        $j = json_decode(file_get_contents($file), true);
        $clients = $j['clients'] ?? [];
        $clientDetails = $j['clientDetails'] ?? [];
        $visits = $j['visits'] ?? [];
        $bookingDetails = $j['bookingDetails'] ?? [];
        // Visits descending date order'da geliyor; paket satislari kullanimlarindan ONCE islensin diye ASC sirala
        usort($visits, function ($a, $b) {
            $ka = ($a['date'] ?? '') . ' ' . ($a['time_text'] ?? '');
            $kb = ($b['date'] ?? '') . ' ' . ($b['time_text'] ?? '');
            return strcmp($ka, $kb);
        });
        $this->line("v5 dump: clients={" . count($clients) . "}, visits={" . count($visits) . "}, bookingDetails={" . count($bookingDetails) . "}, clientDetails={" . count($clientDetails) . "}");

        $apiController = app(\App\Http\Controllers\ApiController::class);

        // 1) Müşteri aktarımı - clientDetails varsa zengin notlar
        $idMap = [];
        $mEklenen = 0; $mHata = 0;
        foreach ($clients as $idx => $c) {
            $cd = $clientDetails[$c['id']] ?? null;
            $notes = $this->pickFirst($cd, ['notes','note','client_note','description']) ?? ($c['notes'] ?? '');
            $birthdate = $this->pickFirst($cd, ['birthdate','birth_date','dogum_tarihi']) ?? ($c['birthdate'] ?? '');
            $email = $this->pickFirst($cd, ['email']) ?? ($c['email'] ?? '');
            $payload = [
                'musteriAdi'  => $c['name'] ?? '',
                'telefon'     => $c['phone_number_local'] ?? $c['phone_number'] ?? '',
                'ePosta'      => $email,
                'dogumTarihi' => $birthdate,
                'cinsiyet'    => $c['gender_text'] ?? '',
                'notlar'      => $notes,
                'medeniDurum' => '', 'meslek' => '', 'adres' => '',
                'kayitTarihi' => $c['created_at'] ?? '',
                'salonId'     => $salonId,
                'salonAppyId' => $c['id'],
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->aktarimMusteriKontrol($req);
                $userId = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                if ($userId && ctype_digit($userId)) { $idMap[$c['id']] = $userId; $mEklenen++; }
                else { $mHata++; }
            } catch (\Throwable $e) { $mHata++; \Log::warning('[Salonappy v5] müşteri', ['err' => $e->getMessage(), 'client' => $c['id']]); }
            if (($idx + 1) % 200 === 0) $this->line("  müşteri {$idx}/" . count($clients) . " eklenen={$mEklenen} hata={$mHata}");
        }
        $this->info("Musteri: eklenen={$mEklenen}, hata={$mHata}");

        // 2) Visits (her biri için Randevu + Adisyon + itemized hizmet/urun/tahsilat)
        $rEklenen = 0; $rDedup = 0; $rHata = 0; $tEklenen = 0;
        $i = 0;
        foreach ($visits as $v) {
            $i++;
            $session = $v['session'] ?? '';
            if (!$session) continue;
            $bd = $bookingDetails[$session] ?? null;
            $detail = $bd['details'] ?? $bd['detail'] ?? null;
            $clientId = $detail['client_id'] ?? null;
            $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
            // Fallback: visit'in client_name + phone_number ile match
            if (!$userId && isset($v['phone_number'])) {
                $phone = preg_replace('~\D~', '', $v['phone_number']);
                $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
            }
            if (!$userId) { $rHata++; continue; }

            $tarih = $v['date'] ?? '';
            $saatStr = $v['time_text'] ?? '00:00';
            $saat = strlen($saatStr) === 5 ? $saatStr . ':00' : $saatStr;
            $marker = '[salonappy:' . $session . ']';

            // Dedup: marker
            if (\DB::table('randevular')->where('salon_id', $salonId)->where('user_id', $userId)
                ->where('personel_notu', 'LIKE', '%' . $marker . '%')->exists()) { $rDedup++; continue; }

            // Hizmetler itemized
            $hizmetler = [];
            foreach (($bd['services'] ?? []) as $s) {
                $ad = trim((string) ($s['service_text'] ?? ''));
                // Salonappy'de silinmis hizmet -> service_text bos ama service_id var.
                // Randevu kaybolmasin diye placeholder isim ata.
                if ($ad === '' && !empty($s['service_id'])) {
                    $ad = 'Salonappy Hizmet #' . $s['service_id'];
                }
                $hizmetler[] = [
                    'hizmet'   => $ad,
                    'personel' => $s['staff_name'] ?? '',
                    'fiyat'    => (float) ($s['price'] ?? 0),
                    'sureDk'   => (int) ($s['duration'] ?? 30),
                ];
            }
            if (empty($hizmetler)) $hizmetler = $this->parseSalonappyServicesStaff($v['services_staff_text'] ?? '', $v['total_amount'] ?? 0);

            // Paket satislari: hizmetler dizisine eklenir, controller call'undan sonra
            // ilgili AdisyonHizmetler.seans_sayisi = quantity set edilir.
            $paketSales = $bd['package_sales'] ?? [];
            $paketHizmetAdlari = [];
            foreach ($paketSales as $pkg) {
                $ad = trim((string) ($pkg['service_text'] ?? ''));
                if ($ad === '' && !empty($pkg['service_id'])) {
                    $ad = 'Salonappy Hizmet #' . $pkg['service_id'];
                }
                if ($ad === '') continue;
                $quantity = (int) ($pkg['quantity'] ?? 1);
                $amount = (float) ($pkg['amount'] ?? 0);
                $hizmetler[] = [
                    'hizmet'   => $ad,
                    'personel' => $pkg['staff_name'] ?? '',
                    'fiyat'    => $amount,
                    'sureDk'   => 30,
                ];
                $paketHizmetAdlari[] = ['ad' => $ad, 'quantity' => $quantity, 'amount' => $amount];
            }

            // Ürünler itemized
            $urunler = [];
            foreach (($bd['product_sales'] ?? []) as $p) {
                $urunler[] = [
                    'urun'     => $p['product_text'] ?? $p['product_name'] ?? $p['name'] ?? '',
                    'personel' => $p['staff_name'] ?? '',
                    'fiyat'    => (float) ($p['amount'] ?? $p['price'] ?? 0),
                    'adet'     => (int) ($p['quantity'] ?? $p['qty'] ?? 1),
                ];
            }

            $randevuNotu = $detail['notes'] ?? '';
            $finalNotlar = trim(($randevuNotu ? $randevuNotu . ' ' : '') . $marker);

            // Status/showup normalize (Salonappy locale EN veya TR olabilir)
            $statusNorm = $this->normalizeStatus($v['status_text'] ?? '', $detail['status'] ?? null);
            $geldiNorm = $this->normalizeShowup($v['showup_text'] ?? '', $detail['showup'] ?? null);

            // created_by "Salon (Eşem Avcı)" -> "Eşem Avcı" normalize
            $olusturan = (string) ($v['created_by'] ?? '');
            if (preg_match('~Salon\s*\(([^)]+)\)~iu', $olusturan, $m)) $olusturan = trim($m[1]);
            if (!empty($olusturan)) $this->ensurePersonel($salonId, $olusturan);

            // Eksik hizmet ve personelleri otomatik olustur ve isimleri canonical'a normalize et.
            // Controller exact-match yapiyor (Hizmetler::where('hizmet_adi', ...)), trKey/case farkinda
            // null donerse $salonHizmet->id null reference firlatir ve randevu_hizmetler bos kalir.
            $hizmetlerFiltered = [];
            foreach ($hizmetler as $h) {
                if (empty($h['hizmet'])) continue;
                $canon = $h['hizmet'];
                $hid = $this->ensureSalonHizmet($salonId, $h['hizmet'], $h['sureDk'] ?? 30, $h['fiyat'] ?? 0, $canon);
                if (!$hid) continue; // hizmet olusturulamadiysa skip et (controller crash etmesin)
                $h['hizmet'] = $canon;
                if (!empty($h['personel'])) {
                    $canonP = $h['personel'];
                    $this->ensurePersonel($salonId, $h['personel'], $canonP);
                    $h['personel'] = $canonP;
                }
                $hizmetlerFiltered[] = $h;
            }
            $hizmetler = $hizmetlerFiltered;
            // Eksik urunleri ve personelleri otomatik olustur ve canonical'a normalize et
            $urunlerFiltered = [];
            foreach ($urunler as $u) {
                if (empty($u['urun'])) continue;
                $canon = $u['urun'];
                $uid = $this->ensureUrun($salonId, $u['urun'], $u['fiyat'] ?? 0, $canon);
                if (!$uid) continue;
                $u['urun'] = $canon;
                if (!empty($u['personel'])) {
                    $canonP = $u['personel'];
                    $this->ensurePersonel($salonId, $u['personel'], $canonP);
                    $u['personel'] = $canonP;
                }
                $urunlerFiltered[] = $u;
            }
            $urunler = $urunlerFiltered;
            // paketHizmetAdlari da canonical kullanmali (post-call lookup'ta hizmet bulunsun)
            foreach ($paketHizmetAdlari as $k => $pkg) {
                if (!empty($pkg['ad'])) {
                    $canon = $pkg['ad'];
                    $this->ensureSalonHizmet($salonId, $pkg['ad'], 30, $pkg['amount'] ?? 0, $canon);
                    $paketHizmetAdlari[$k]['ad'] = $canon;
                }
            }

            $payload = [
                'userId'      => $userId,
                'salonId'     => $salonId,
                'tarih'       => $tarih,
                'saat'        => $saatStr,
                'geldi'       => $geldiNorm,
                'durum'       => $statusNorm,
                'olusturan'   => $olusturan,
                'olusturulma' => $v['created_at'] ?? '',
                'notlar'      => $finalNotlar,
                'hizmetler'   => $hizmetler,
                'urunler'     => $urunler,
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->salonAppyAdisyonRandevuEkle($req);
                $adisyonId = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                $rEklenen++;

                // Adisyona da marker yaz (reset icin)
                if ($adisyonId && ctype_digit($adisyonId)) {
                    $adisyonTable = (new \App\Adisyonlar)->getTable();
                    foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
                        if (\Schema::hasColumn($adisyonTable, $col)) {
                            \DB::table($adisyonTable)->where('id', $adisyonId)->update([$col => $marker]);
                            break;
                        }
                    }
                }

                // Paket satislari: ilgili AdisyonHizmetler.seans_sayisi'ni set et
                if ($adisyonId && ctype_digit($adisyonId) && !empty($paketHizmetAdlari)) {
                    foreach ($paketHizmetAdlari as $pkg) {
                        $hizmet = \App\Hizmetler::where('hizmet_adi', $pkg['ad'])->first();
                        if (!$hizmet) continue;
                        // Bu adisyondaki ilgili AdisyonHizmet'i bul, seans_sayisi yaz
                        \DB::table('adisyon_hizmetler')
                            ->where('adisyon_id', $adisyonId)
                            ->where('hizmet_id', $hizmet->id)
                            ->whereNull('seans_sayisi')
                            ->limit(1)
                            ->update(['seans_sayisi' => $pkg['quantity']]);
                    }
                }

                // Paket kullanimlari: package_usages'den seans dusumu
                if (!empty($bd['package_usages'])) {
                    foreach ($bd['package_usages'] as $use) {
                        $hizmetAd = trim((string) ($use['service_text'] ?? ''));
                        if ($hizmetAd === '' && !empty($use['service_id'])) {
                            $hizmetAd = 'Salonappy Hizmet #' . $use['service_id'];
                        }
                        if ($hizmetAd === '') continue;
                        // Canonical'a normalize et (yoksa create) - seans hizmet_id eslestirmesi icin
                        $canonAd = $hizmetAd;
                        $this->ensureSalonHizmet($salonId, $hizmetAd, 30, 0, $canonAd);
                        $kullanimSayisi = (int) ($use['quantity'] ?? 1);
                        $kullanimTarih = $use['date'] ?? $tarih;
                        $this->salonappySeansiTuket($userId, $salonId, $canonAd, $kullanimTarih, $saat, $kullanimSayisi);
                    }
                }

                // Tahsilatlar itemized (payments[] dolu ise her birini ayrı ekle)
                if ($adisyonId && ctype_digit($adisyonId) && !empty($bd['payments'])) {
                    foreach ($bd['payments'] as $p) {
                        $tutar = (float) ($p['amount'] ?? 0);
                        if ($tutar <= 0) continue;
                        $odemeYontem = $p['payment_method_text'] ?? $p['payment_method'] ?? 'Nakit';
                        $odemeTarih = $p['date'] ?? $tarih;
                        $existsT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                            ->where('user_id', $userId)->where('odeme_tarihi', $odemeTarih)
                            ->where('tutar', $tutar)->exists();
                        if ($existsT) continue;
                        try {
                            $tReq = new \Illuminate\Http\Request([
                                'userId' => $userId, 'adisyonId' => $adisyonId,
                                'odemeTarihi' => $odemeTarih, 'tahsilatTutari' => $tutar,
                                'odemeYontemi' => $odemeYontem, 'salonId' => $salonId,
                            ]);
                            $apiController->salonAppyTahsilatEkle($tReq);
                            $tEklenen++;
                            // Tahsilata marker yaz (reset icin)
                            $newT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('user_id', $userId)->where('odeme_tarihi', $odemeTarih)
                                ->where('tutar', $tutar)->orderByDesc('id')->first();
                            if ($newT && \Schema::hasColumn('tahsilatlar', 'notlar')) {
                                \DB::table('tahsilatlar')->where('id', $newT->id)->update(['notlar' => $marker]);
                            }
                        } catch (\Throwable $e) {}
                    }
                }
            } catch (\Throwable $e) {
                $rHata++;
                \Log::warning('[Salonappy v5] randevu', ['session' => $session, 'err' => $e->getMessage()]);
            }
            if ($i % 200 === 0) $this->line("  visit {$i}/" . count($visits) . " eklenen={$rEklenen} dedup={$rDedup} hata={$rHata} tahsilat={$tEklenen}");
        }
        $this->info("Visit: eklenen={$rEklenen}, dedup={$rDedup}, hata={$rHata}, tahsilat={$tEklenen}");
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
        $clientDetails = $j['clientDetails'] ?? [];  // notlari icerir
        $bookingDetails = $j['bookingDetails'] ?? []; // randevu notlari + tam hizmet bilgisi
        $this->line('Clients: ' . count($clients) . ', Bookings users: ' . count($bookings) . ', ClientDetails: ' . count($clientDetails) . ', BookingDetails: ' . count($bookingDetails));

        $apiController = app(\App\Http\Controllers\ApiController::class);

        // 1) Musteri aktarimi
        $idMap = []; $musteriEklenen = 0; $musteriHata = 0;
        foreach ($clients as $idx => $c) {
            // clientDetails varsa oradan notları al (daha zengin); yoksa list'teki
            $cd = $clientDetails[$c['id']] ?? null;
            $notes = $this->pickFirst($cd, ['notes','note','client_note','description']) ?? ($c['notes'] ?? '');
            $birthdate = $this->pickFirst($cd, ['birthdate','birth_date','dogum_tarihi']) ?? ($c['birthdate'] ?? '');
            $email = $this->pickFirst($cd, ['email']) ?? ($c['email'] ?? '');

            $payload = [
                'musteriAdi'   => $c['name'] ?? '',
                'telefon'      => $c['phone_number_local'] ?? $c['phone_number'] ?? '',
                'ePosta'       => $email,
                'dogumTarihi'  => $birthdate,
                'cinsiyet'     => $c['gender_text'] ?? '',
                'notlar'       => $notes,
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

                    // Detay varsa zengin hizmet/urun/notes
                    $bd = $bookingDetails[$session] ?? null;
                    $detail = $bd['detail'] ?? null;
                    $sess   = $bd['session'] ?? null;

                    // Randevu notu (varsa detail veya session'dan)
                    $randevuNotu = $this->pickFirst($detail, ['note','notes','client_note','customer_note'])
                                ?: $this->pickFirst($sess, ['note','notes','client_note','customer_note'])
                                ?: '';

                    // Hizmetler: detail/session'dan zengin liste, yoksa text'ten parse
                    $hizmetler = $this->extractServicesFromDetail($detail, $sess);
                    if (empty($hizmetler)) {
                        $hizmetler = $this->parseSalonappyServicesStaff($b['services_staff_text'] ?? '', $b['total_amount'] ?? 0);
                    }
                    $urunler = $this->extractProductsFromDetail($detail, $sess);
                    if (empty($urunler)) {
                        $urunler = $this->parseSalonappyProducts($b['products_text'] ?? '');
                    }

                    $finalNotlar = trim(($randevuNotu ? $randevuNotu . ' ' : '') . '[salonappy:' . $session . ']');

                    $payload = [
                        'userId'       => $userId,
                        'salonId'      => $salonId,
                        'tarih'        => $tarih,
                        'saat'         => $saatStr,
                        'geldi'        => $b['showup_text'] ?? '',
                        'durum'        => $b['status_text'] ?? '',
                        'olusturan'    => $b['created_by'] ?? '',
                        'olusturulma'  => $b['created_at'] ?? '',
                        'notlar'       => $finalNotlar,
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
     * Salonappy markerli (personel_notu LIKE '%[salonappy:%') randevu ve
     * adisyon kayitlarini ve bagli alt kayitlari sil. Tahsilatlar'da bagli olanlarin
     * adisyon_id'sini NULL'a cek (tahsilat'i tutup, sonra reimport'ta dedup gecer).
     */
    private function resetSalonappy($salonId, $dryRun)
    {
        $tR = (new \App\Randevular)->getTable();
        $tA = (new \App\Adisyonlar)->getTable();
        $tRh = (new \App\RandevuHizmetler)->getTable();
        $tAh = (new \App\AdisyonHizmetler)->getTable();
        $tAu = (new \App\AdisyonUrunler)->getTable();
        $tT = (new \App\Tahsilatlar)->getTable();
        $tAps = (new \App\AdisyonPaketSeanslar)->getTable();

        $randevuIds = \DB::table($tR)->where('salon_id', $salonId)
            ->where('personel_notu', 'LIKE', '%[salonappy:%')->pluck('id')->all();

        // Adisyon not kolonu
        $notKol = null;
        foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
            if (\Schema::hasColumn($tA, $col)) { $notKol = $col; break; }
        }
        $adisyonIds = [];
        if ($notKol) {
            $adisyonIds = \DB::table($tA)->where('salon_id', $salonId)
                ->where($notKol, 'LIKE', '%[salonappy:%')->pluck('id')->all();
        }
        // Fallback: markersiz adisyonlar icin marker'li randevu user+tarih ile eslestir
        if (!empty($randevuIds)) {
            $rPairs = \DB::table($tR)->whereIn('id', $randevuIds)
                ->select('user_id', 'tarih')->distinct()->get();
            foreach ($rPairs as $p) {
                $more = \DB::table($tA)->where('salon_id', $salonId)
                    ->where('user_id', $p->user_id)->where('tarih', $p->tarih)
                    ->pluck('id')->all();
                foreach ($more as $aid) if (!in_array($aid, $adisyonIds)) $adisyonIds[] = $aid;
            }
        }

        $this->line("Salon {$salonId}: " . count($randevuIds) . " randevu, " . count($adisyonIds) . " adisyon silinecek (markerli)");
        if ($dryRun) { $this->warn('DRY-RUN'); return 0; }

        // Tahsilatlar: marker'li veya adisyon_id eslesen
        $tahsilatIds = \DB::table($tT)->where('salon_id', $salonId)
            ->where(function ($q) use ($adisyonIds) {
                $q->where('notlar', 'LIKE', '%[salonappy:%');
                if (!empty($adisyonIds)) $q->orWhereIn('adisyon_id', $adisyonIds);
            })->pluck('id')->all();
        $this->line("Tahsilat (markerli veya adisyon_id eslesen): " . count($tahsilatIds));

        // AdisyonHizmetler -> AdisyonPaketSeanslar -> AdisyonUrunler -> Tahsilatlar -> Adisyonlar
        if (!empty($adisyonIds)) {
            $ahIds = \DB::table($tAh)->whereIn('adisyon_id', $adisyonIds)->pluck('id')->all();
            if (!empty($ahIds)) {
                foreach (array_chunk($ahIds, 1000) as $ck) {
                    \DB::table($tAps)->whereIn('adisyon_hizmet_id', $ck)->delete();
                }
            }
            foreach (array_chunk($adisyonIds, 1000) as $ck) {
                \DB::table($tAh)->whereIn('adisyon_id', $ck)->delete();
                \DB::table($tAu)->whereIn('adisyon_id', $ck)->delete();
                \DB::table($tA)->whereIn('id', $ck)->delete();
            }
        }
        // Tahsilatlar ve bagli kalemleri sil
        if (!empty($tahsilatIds)) {
            foreach (array_chunk($tahsilatIds, 1000) as $ck) {
                \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $ck)->delete();
                \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $ck)->delete();
                \DB::table($tT)->whereIn('id', $ck)->delete();
            }
        }
        // RandevuHizmetler -> Randevular
        if (!empty($randevuIds)) {
            foreach (array_chunk($randevuIds, 1000) as $ck) {
                \DB::table($tRh)->whereIn('randevu_id', $ck)->delete();
                \DB::table($tR)->whereIn('id', $ck)->delete();
            }
        }
        $this->info('Reset tamam. Simdi --dump-file ile re-import yapabilirsiniz.');
        return 0;
    }

    private function saTrKey($s)
    {
        $s = (string) $s;
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/\p{M}+/u', '', $s);
        $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
        $s = preg_replace('~[^a-z0-9]+~', ' ', $s);
        return trim($s);
    }

    /**
     * Salonappy status_text -> controller'in bekledigi TR string.
     * Salon hesabinda EN/TR locale farkli olabilir.
     */
    private function normalizeStatus($text, $statusCode = null)
    {
        $t = mb_strtolower(trim((string) $text), 'UTF-8');
        if ($t === 'onaylandı' || $t === 'onaylandi' || $t === 'approved') return 'Onaylandı';
        if ($t === 'reddedildi' || $t === 'rejected') return 'Reddedildi';
        if ($t === 'iptal edildi' || $t === 'iptal' || $t === 'cancelled' || $t === 'canceled') return 'İptal edildi';
        if ($t === 'müşteri iptal etti' || $t === 'musteri iptal etti' || $t === 'cancelled by client' || $t === 'client cancelled') return 'Müşteri iptal etti';
        // Status code fallback
        if ($statusCode !== null) {
            $sc = (string) $statusCode;
            if ($sc === '1') return 'Beklemede';
            if ($sc === '2') return 'Onaylandı';
            if ($sc === '3') return 'Reddedildi';
            if ($sc === '4' || $sc === '5') return 'İptal edildi';
        }
        return $text ?: '';
    }

    private function normalizeShowup($text, $showupCode = null)
    {
        $t = mb_strtolower(trim((string) $text), 'UTF-8');
        if ($t === 'geldi' || $t === 'showed up' || $t === 'attended') return 'Geldi';
        if ($t === 'gelmedi' || $t === 'did not show' || $t === 'no show' || $t === 'no-show') return 'Gelmedi';
        if ($showupCode !== null) {
            $sc = (string) $showupCode;
            if ($sc === '1') return 'Geldi';
            if ($sc === '2') return 'Gelmedi';
        }
        return $text ?: '';
    }

    private function ensureSalonHizmet($salonId, $ad, $sureDk = 30, $fiyat = 0, &$canonicalAd = null)
    {
        $canonicalAd = $ad;
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        static $canonCache = [];
        static $trKeyMap = null; // hizmet trKey -> id (lazy yuklenir)
        $needle = $this->saTrKey($ad);
        $cacheKey = $salonId . '|' . $needle;
        if (isset($cache[$cacheKey])) { $canonicalAd = $canonCache[$cacheKey] ?? $ad; return $cache[$cacheKey]; }

        // Exact match
        $hizmet = \App\Hizmetler::where('hizmet_adi', $ad)->first();
        // trKey match (case/diacritic-insensitive) - tum hizmetleri tek seferde yukle
        if (!$hizmet) {
            if ($trKeyMap === null) {
                $trKeyMap = [];
                foreach (\DB::table('hizmetler')->select('id','hizmet_adi')->get() as $h) {
                    $k = $this->saTrKey($h->hizmet_adi);
                    if ($k && !isset($trKeyMap[$k])) $trKeyMap[$k] = $h->id;
                }
            }
            if (isset($trKeyMap[$needle])) {
                $hizmet = \App\Hizmetler::find($trKeyMap[$needle]);
            }
        }
        if (!$hizmet) {
            try {
                $hizmet = new \App\Hizmetler();
                $hizmet->hizmet_adi = $ad;
                // Hizmet_Kategorisi modelini kullan (tablo: hizmet_kategorisi, kolon: hizmet_kategorisi_adi)
                $kategori = \App\Hizmet_Kategorisi::where('hizmet_kategorisi_adi', 'Salonappy')->first();
                if (!$kategori) {
                    $kategori = new \App\Hizmet_Kategorisi();
                    $kategori->hizmet_kategorisi_adi = 'Salonappy';
                    $kategori->save();
                }
                $hizmet->hizmet_kategori_id = $kategori->id;
                $hizmet->ozel_hizmet = true;
                if (\Schema::hasColumn('hizmetler', 'salon_id')) $hizmet->salon_id = $salonId;
                if (\Schema::hasColumn('hizmetler', 'aktif'))    $hizmet->aktif = 0;
                $hizmet->save();
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy] hizmet eklenemedi', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        // SalonHizmet kayit
        $sh = \App\SalonHizmetler::where('salon_id', $salonId)->where('hizmet_id', $hizmet->id)->first();
        if (!$sh) {
            try {
                $sh = new \App\SalonHizmetler();
                $sh->salon_id = $salonId;
                $sh->hizmet_id = $hizmet->id;
                $sh->hizmet_kategori_id = $hizmet->hizmet_kategori_id;
                $sh->aktif = 0;
                $sh->bolum = 2;
                $sh->sure_dk = $sureDk ?: 30;
                $sh->baslangic_fiyat = $fiyat;
                $sh->son_fiyat = $fiyat;
                $sh->save();
            } catch (\Throwable $e) {}
        }
        $cache[$cacheKey] = $hizmet->id;
        $canonCache[$cacheKey] = $hizmet->hizmet_adi;
        $canonicalAd = $hizmet->hizmet_adi;
        return $hizmet->id;
    }

    private function ensureUrun($salonId, $ad, $fiyat = 0, &$canonicalAd = null)
    {
        $canonicalAd = $ad;
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        static $canonCache = [];
        $needle = $this->saTrKey($ad);
        $cacheKey = $salonId . '|' . $needle;
        if (isset($cache[$cacheKey])) { $canonicalAd = $canonCache[$cacheKey] ?? $ad; return $cache[$cacheKey]; }

        // Exact match (salon-bazli)
        $urun = \App\Urunler::where('salon_id', $salonId)->where('urun_adi', $ad)->first();
        // trKey match (case/diacritic-insensitive)
        if (!$urun) {
            foreach (\App\Urunler::where('salon_id', $salonId)->select('id','urun_adi')->get() as $row) {
                if ($this->saTrKey($row->urun_adi) === $needle) {
                    $urun = \App\Urunler::find($row->id);
                    break;
                }
            }
        }
        if (!$urun) {
            try {
                $urun = new \App\Urunler();
                $urun->urun_adi = $ad;
                $urun->salon_id = $salonId;
                if (\Schema::hasColumn('urunler', 'aktif')) $urun->aktif = 0;
                if (\Schema::hasColumn('urunler', 'fiyat') && $fiyat > 0) $urun->fiyat = $fiyat;
                if (\Schema::hasColumn('urunler', 'satis_fiyati') && $fiyat > 0) $urun->satis_fiyati = $fiyat;
                $urun->save();
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy] urun eklenemedi', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        $cache[$cacheKey] = $urun->id;
        $canonCache[$cacheKey] = $urun->urun_adi;
        $canonicalAd = $urun->urun_adi;
        return $urun->id;
    }

    /**
     * Salonappy package_usage: müşterinin AÇIK paketinden (kullanılan < seans_sayisi)
     * AdisyonPaketSeanslar (geldi=1) yaz. Drklinik'teki seansiTuket'in benzeri.
     */
    private function salonappySeansiTuket($userId, $salonId, $hizmetAd, $tarih, $saat, $kac)
    {
        $kac = max(1, (int) $kac);
        $saat = $saat ?: '00:00:00';
        if (strlen($saat) === 5) $saat .= ':00';

        // Hizmet id (varsa)
        $hizmetId = \App\Hizmetler::where('hizmet_adi', $hizmetAd)->value('id');

        // Açık AdisyonHizmetler bul: same user/salon, seans_sayisi NOT NULL, kullanılan < seans_sayisi
        $rows = \DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
            ->where('a.user_id', $userId)
            ->where('a.salon_id', $salonId)
            ->whereNotNull('ah.seans_sayisi')
            ->select('ah.id', 'ah.hizmet_id', 'ah.seans_sayisi')
            ->orderBy('a.tarih')->get();

        // Önce hizmet_id eşleşenleri sırala
        $sira = [];
        if ($hizmetId) {
            foreach ($rows as $r) if ((int) $r->hizmet_id === (int) $hizmetId) $sira[] = $r;
            foreach ($rows as $r) if ((int) $r->hizmet_id !== (int) $hizmetId) $sira[] = $r;
        } else {
            $sira = $rows->all();
        }

        foreach ($sira as $r) {
            if ($kac <= 0) break;
            $kullanilan = (int) \DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->count();
            $bos = (int) $r->seans_sayisi - $kullanilan;
            if ($bos <= 0) continue;

            // Idempotent: bu paketten aynı (tarih, saat) için zaten var mı?
            $exists = \DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)
                ->where('seans_tarih', $tarih)
                ->where('seans_saat', $saat)->exists();
            if ($exists) {
                $kac--;
                continue;
            }

            $sonNo = (int) (\DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->max('seans_no') ?? 0);
            $eksik = min($kac, $bos);
            for ($i = 0; $i < $eksik; $i++) {
                $sonNo++;
                \DB::table('adisyon_paket_seanslar')->insert([
                    'adisyon_hizmet_id' => $r->id,
                    'hizmet_id' => $r->hizmet_id,
                    'seans_no' => $sonNo,
                    'seans_tarih' => $tarih,
                    'seans_saat' => $saat,
                    'geldi' => 1,
                ]);
            }
            $kac -= $eksik;
        }
    }

    private function ensurePersonel($salonId, $ad, &$canonicalAd = null)
    {
        $canonicalAd = $ad;
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        static $canonCache = [];
        $cacheKey = $salonId . '|' . mb_strtolower($ad, 'UTF-8');
        if (isset($cache[$cacheKey])) { $canonicalAd = $canonCache[$cacheKey] ?? $ad; return $cache[$cacheKey]; }

        // Exact match
        $p = \App\Personeller::where('salon_id', $salonId)->where('personel_adi', $ad)->first();
        // trKey match (case/diacritic-insensitive)
        if (!$p) {
            $needle = $this->saTrKey($ad);
            foreach (\App\Personeller::where('salon_id', $salonId)->select('id','personel_adi')->get() as $row) {
                if ($this->saTrKey($row->personel_adi) === $needle) {
                    $p = \App\Personeller::find($row->id);
                    break;
                }
            }
        }
        if (!$p) {
            try {
                // Canonical pattern: yeniPersonelKaydi (ApiController)
                $yetkili = new \App\IsletmeYetkilileri();
                $yetkili->name = $ad;
                $yetkili->save();
                $p = new \App\Personeller();
                $p->personel_adi = $ad;
                $p->salon_id = $salonId;
                $p->aktif = false;
                $p->yetkili_id = $yetkili->id;
                $p->save();
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy] personel eklenemedi', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        $cache[$cacheKey] = $p->id;
        $canonCache[$cacheKey] = $p->personel_adi;
        $canonicalAd = $p->personel_adi;
        return $p->id;
    }

    private function pickFirst($obj, $keys)
    {
        if (!is_array($obj)) return null;
        foreach ($keys as $k) {
            if (isset($obj[$k]) && $obj[$k] !== '' && $obj[$k] !== null) return $obj[$k];
        }
        return null;
    }

    /**
     * booking/detail veya booking/session response'unda hizmet listesi.
     * Salonappy yapisi tam bilinmiyor, yaygin alan adlari deneniyor.
     */
    private function extractServicesFromDetail($detail, $session)
    {
        $candidates = [];
        foreach ([$detail, $session] as $src) {
            if (!is_array($src)) continue;
            foreach (['services', 'service_list', 'items', 'service_items', 'lines', 'service_staff'] as $k) {
                if (isset($src[$k]) && is_array($src[$k]) && !empty($src[$k])) {
                    $candidates = $src[$k];
                    break 2;
                }
            }
        }
        $out = [];
        foreach ($candidates as $s) {
            if (!is_array($s)) continue;
            $hizmetAd = $this->pickFirst($s, ['service_name','name','service_title','title','hizmet_adi']) ?: '';
            $personelAd = $this->pickFirst($s, ['staff_name','employee_name','staff','personel','employee','personel_adi']) ?: '';
            $fiyat = $this->pickFirst($s, ['price','amount','total','fiyat','total_price']) ?: 0;
            $sure = $this->pickFirst($s, ['duration','duration_min','sure','sure_dk','duration_minutes']) ?: 30;
            $hizmetNotu = $this->pickFirst($s, ['note','notes','staff_note']) ?: '';
            if ($hizmetAd) {
                $out[] = [
                    'hizmet'   => $hizmetAd,
                    'personel' => $personelAd,
                    'fiyat'    => (float) $fiyat,
                    'sureDk'   => (int) $sure,
                    'notlar'   => $hizmetNotu,
                ];
            }
        }
        return $out;
    }

    private function extractProductsFromDetail($detail, $session)
    {
        $candidates = [];
        foreach ([$detail, $session] as $src) {
            if (!is_array($src)) continue;
            foreach (['products', 'product_list', 'product_items'] as $k) {
                if (isset($src[$k]) && is_array($src[$k]) && !empty($src[$k])) {
                    $candidates = $src[$k];
                    break 2;
                }
            }
        }
        $out = [];
        foreach ($candidates as $p) {
            if (!is_array($p)) continue;
            $urunAd = $this->pickFirst($p, ['product_name','name','title','urun_adi']) ?: '';
            $personelAd = $this->pickFirst($p, ['staff_name','employee_name','staff','personel']) ?: '';
            $fiyat = $this->pickFirst($p, ['price','amount','total','fiyat']) ?: 0;
            $adet = $this->pickFirst($p, ['quantity','qty','adet','count']) ?: 1;
            if ($urunAd) {
                $out[] = [
                    'urun'     => $urunAd,
                    'personel' => $personelAd,
                    'fiyat'    => (float) $fiyat,
                    'adet'     => (int) $adet,
                ];
            }
        }
        return $out;
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
