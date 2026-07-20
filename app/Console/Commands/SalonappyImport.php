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
        {--services-master= : Salonappy /setup/services endpointinden cekilen master JSON (id->title TR)}
        {--reset-salonappy : [salonappy:session] markerli randevu+adisyon+kalemleri sil (musteriler kalir)}
        {--only-package-sales : Sadece paket satislarini isle (dump packageSales) — adisyon+AH+APS olusturur, tahsilat/alacak DOKUNMAZ.}
        {--only-package-payments : Daha onceden --only-package-sales ile yazilmis paket adisyonlarina dump payments[] eslestir; source_text="Paket satisi" olanlar tahsilat olarak baglanir + kalan alacak (TaksitliTahsilatlar+Vadeler+Alacaklar).}
        {--only-product-sales : Sadece urun satislarini isle (dump productSales) — adisyon+adisyon_urunler+alacak (receivable_amount > 0 ise). Tahsilat DOKUNMAZ.}
        {--only-product-payments : Daha onceden --only-product-sales ile yazilmis urun adisyonlarina dump payments[] eslestir; source_text="Urun satisi" olanlar tahsilat olarak baglanir.}
        {--only-visits : Dump visit/bookingDetails -> randevu+adisyon+AH+tahsilat+TH/TU+alacak+paket usage isaretle. --from/--to araligi sart. Marker [salonappy-visit:<session>].}
        {--only-expenses : Dump expenses[] -> masraflar tablosuna idempotent insert. UPSERT marker [salonappy-expense:id]. masraf_kategorisi auto-create.}
        {--only-setup : Kurulum dump (salonappy_setup_*.json) — tum aşamalari sirayla yaz (musteri+personel+urun+hizmet+pivot). Visit/paket/tahsilat YOK.}
        {--only-setup-musteri : Adim 1 — Musterileri yaz (aktarimMusteriKontrol; telefon dedup).}
        {--only-setup-personel : Adim 2 — Personel+Cihazlari yaz (staff.type ayrimi; aktif=1, role_id, calisma saatleri).}
        {--only-setup-urun : Adim 3 — Urunleri yaz (ensureUrun; aktif=1).}
        {--only-setup-hizmet : Adim 4 — Hizmetleri yaz (sure + kategori + providing_staff pivot). Fiyat 0 kalir (visit aktariminda zenginlesir).}
        {--with-products : --only-visits filtresine ek: sadece product_sales[] dolu visitleri isle (urun tasima testi icin).}
        {--reset-visits : Tarih araligindaki [salonappy-visit:%] markerli randevu+adisyon+tahsilat+taksit+alacak sil. --from/--to sart.}
        {--from= : Visit aktarim/reset baslangic tarihi YYYY-MM-DD}
        {--to= : Visit aktarim/reset bitis tarihi YYYY-MM-DD}
        {--fix-rh-saat : Salonun [salonappy:%] veya [salonappy-visit:%] markerli randevularinda RandevuHizmetler.saat/saat_bitis bos olanlari Randevular.saat tabaninda cumulative sure_dk ile yeniden hesapla. --salon zorunlu.}
        {--inspect-musteri : Belirli bir musteri icin DB tani: --tel=X veya --ad=Y veya --musteri-id=Z + --salon=N. Salonappy markerli kayit sayilarini doker. Login gerekmez.}
        {--tel= : --inspect-musteri icin telefon (sadece rakam)}
        {--ad= : --inspect-musteri icin ad (LIKE match)}
        {--musteri-id= : --inspect-musteri icin DB user_id direkt}
        {--reconcile-tahsilat : Salonappy tahsilat xlsx export (Musteri/Tarih/Odeme/Tutar/Kaynak/Urun) ile DB tahsilatlari karsilastir. Eksik olanlar CSV\'ye yazilir. --file --salon zorunlu, --from/--to opsiyonel.}
        {--file= : --reconcile-tahsilat icin xlsx dosya yolu}
        {--inspect-tahsilat-detay : Belli tarih+tutar aralığindaki tum DB tahsilatlarini listele. --tarih --tutar --salon zorunlu.}
        {--inspect-dupe-musteri : Salon icin duplicate name+cep_telefon ciftlerini listele (aktarim sonrasi dedup dogrulama). --salon zorunlu.}
        {--merge-dupe-musteri : Salon icin duplicate name+cep_telefon ciftlerini merge. Keeper=min(user_id); digerlerin randevu/adisyon/tahsilat/portfoy vs keeper\'a taşinir, sonra silinir. --salon zorunlu, --dry-run destekli.}
        {--tarih= : --inspect-tahsilat-detay icin merkez tarih YYYY-MM-DD}
        {--tutar= : --inspect-tahsilat-detay icin merkez tutar}
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
        $fixRhSaat = (bool) $this->option('fix-rh-saat');
        $inspectMus = (bool) $this->option('inspect-musteri');
        $reconcileT = (bool) $this->option('reconcile-tahsilat');
        $inspectTD = (bool) $this->option('inspect-tahsilat-detay');
        $inspectDupe = (bool) $this->option('inspect-dupe-musteri');
        $mergeDupe = (bool) $this->option('merge-dupe-musteri');
        if (!$analyze && !$token && !$dumpFile && !$fromFile && !$resetMode && !$fixRhSaat && !$inspectMus && !$reconcileT && !$inspectTD && !$inspectDupe && !$mergeDupe && (!$username || !$password)) {
            $this->error('--username ve --password zorunlu (veya --token / --dump-file / --from-file / --reset-salonappy / --fix-rh-saat / --inspect-musteri / --reconcile-tahsilat / --inspect-tahsilat-detay / --inspect-dupe-musteri verin).');
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
        if ((bool) $this->option('fix-rh-saat')) {
            if (!$salonId) { $this->error('--fix-rh-saat icin --salon zorunlu.'); return 1; }
            return $this->fixRandevuHizmetSaat((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ((bool) $this->option('inspect-musteri')) {
            if (!$salonId) { $this->error('--inspect-musteri icin --salon zorunlu.'); return 1; }
            return $this->inspectMusteri(
                (int) $salonId,
                $this->option('tel'),
                $this->option('ad'),
                $this->option('musteri-id')
            );
        }
        if ((bool) $this->option('reconcile-tahsilat')) {
            if (!$salonId) { $this->error('--reconcile-tahsilat icin --salon zorunlu.'); return 1; }
            $file = $this->option('file');
            if (!$file) { $this->error('--reconcile-tahsilat icin --file zorunlu.'); return 1; }
            return $this->reconcileTahsilat(
                (int) $salonId, $file,
                $this->option('from'), $this->option('to')
            );
        }
        if ((bool) $this->option('inspect-tahsilat-detay')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            $t = $this->option('tarih'); $tu = $this->option('tutar');
            if (!$t || !$tu) { $this->error('--tarih ve --tutar zorunlu.'); return 1; }
            return $this->inspectTahsilatDetay((int) $salonId, $t, (float) $tu);
        }
        if ((bool) $this->option('inspect-dupe-musteri')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            return $this->inspectDupeMusteri((int) $salonId);
        }
        if ((bool) $this->option('merge-dupe-musteri')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            return $this->mergeDupeMusteri((int) $salonId, (bool) $this->option('dry-run'));
        }
        if ($dumpFile = $this->option('dump-file')) {
            if (!$salonId) { $this->error('--salon zorunlu.'); return 1; }
            // --only-package-sales: dump'tan sadece packageSales[] isle, digerlerini atla
            if ((bool) $this->option('only-package-sales')) {
                return $this->importPackageSalesOnly($dumpFile, (int) $salonId);
            }
            // --only-package-payments: paket adisyonlarina payments[] eslestir + kalan alacak
            if ((bool) $this->option('only-package-payments')) {
                return $this->importPackagePaymentsOnly($dumpFile, (int) $salonId);
            }
            // --only-product-sales: urun satislari adisyon+adisyon_urunler+alacak
            if ((bool) $this->option('only-product-sales')) {
                return $this->importProductSalesOnly($dumpFile, (int) $salonId);
            }
            // --only-product-payments: urun adisyonlarina payments[] eslestir
            if ((bool) $this->option('only-product-payments')) {
                return $this->importProductPaymentsOnly($dumpFile, (int) $salonId);
            }
            // --only-expenses: dump expenses[] -> masraflar UPSERT
            if ((bool) $this->option('only-expenses')) {
                return $this->importExpensesOnly($dumpFile, (int) $salonId);
            }
            // --only-setup: tum kurulum aşamalari sirayla
            if ((bool) $this->option('only-setup')) {
                return $this->importSetupOnly($dumpFile, (int) $salonId);
            }
            // Adim adim kurulum: sirali cagirim
            if ((bool) $this->option('only-setup-musteri')) {
                return $this->importSetupMusteriler($dumpFile, (int) $salonId);
            }
            if ((bool) $this->option('only-setup-personel')) {
                return $this->importSetupPersoneller($dumpFile, (int) $salonId);
            }
            if ((bool) $this->option('only-setup-urun')) {
                return $this->importSetupUrunler($dumpFile, (int) $salonId);
            }
            if ((bool) $this->option('only-setup-hizmet')) {
                return $this->importSetupHizmetler($dumpFile, (int) $salonId);
            }
            // --only-visits: tarih araliginda visit detail -> randevu+adisyon+...
            if ((bool) $this->option('only-visits')) {
                $from = $this->option('from'); $to = $this->option('to');
                if (!$from || !$to) { $this->error('--only-visits icin --from ve --to zorunlu.'); return 1; }
                return $this->importVisitsOnly($dumpFile, (int) $salonId, $from, $to, (bool) $this->option('with-products'));
            }
            // --reset-visits: tarih araligindaki visit markerli kayitlari sil
            if ((bool) $this->option('reset-visits')) {
                $from = $this->option('from'); $to = $this->option('to');
                if (!$from || !$to) { $this->error('--reset-visits icin --from ve --to zorunlu.'); return 1; }
                return $this->resetVisits((int) $salonId, $from, $to, (bool) $this->option('dry-run'));
            }
            // v5 yapısını otomatik dedect et: visits + bookingDetails
            $peek = json_decode(file_get_contents($dumpFile), true);
            $servicesMasterFile = $this->option('services-master');
            if (isset($peek['visits']) && isset($peek['bookingDetails'])) {
                return $this->importFromDumpV5($dumpFile, (int) $salonId, $servicesMasterFile);
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
    private function importFromDumpV5($file, $salonId, $servicesMasterFile = null)
    {
        $j = json_decode(file_get_contents($file), true);
        $clients = $j['clients'] ?? [];
        $clientDetails = $j['clientDetails'] ?? [];
        $visits = $j['visits'] ?? [];
        $bookingDetails = $j['bookingDetails'] ?? [];

        // Services master (salonappy /setup/services): id -> TR title.
        // Dump'taki bos service_text'leri bu master ile dolduralim.
        // v6 dump icin master dump'in icinde 'servicesMaster' anahtari altinda gelir.
        $svcMaster = [];
        $masterSource = null;
        if (!empty($j['servicesMaster'])) {
            $this->collectServicesMaster($j['servicesMaster'], $svcMaster);
            $masterSource = 'dump.servicesMaster';
        } elseif ($servicesMasterFile && file_exists($servicesMasterFile)) {
            $mj = json_decode(file_get_contents($servicesMasterFile), true);
            $this->collectServicesMaster($mj, $svcMaster);
            $masterSource = $servicesMasterFile;
        }
        if (!empty($svcMaster)) {
            $this->line("Services master yuklendi ({$masterSource}): " . count($svcMaster) . " hizmet (id -> TR title)");
            // BookingDetails icindeki bos service_text'leri doldur
            $filled = 0;
            foreach ($bookingDetails as $sess => &$bd) {
                foreach (['services','package_sales','package_usages'] as $arrKey) {
                    if (!isset($bd[$arrKey]) || !is_array($bd[$arrKey])) continue;
                    foreach ($bd[$arrKey] as &$item) {
                        $txt = trim((string) ($item['service_text'] ?? ''));
                        $sid = (string) ($item['service_id'] ?? '');
                        if ($txt === '' && $sid !== '' && isset($svcMaster[$sid])) {
                            $item['service_text'] = $svcMaster[$sid];
                            $filled++;
                        }
                    }
                    unset($item);
                }
            }
            unset($bd);
            $this->line("Master ile doldurulan service_text: $filled kalem");
        }
        // Visits descending date order'da geliyor; paket satislari kullanimlarindan ONCE islensin diye ASC sirala
        usort($visits, function ($a, $b) {
            $ka = ($a['date'] ?? '') . ' ' . ($a['time_text'] ?? '');
            $kb = ($b['date'] ?? '') . ' ' . ($b['time_text'] ?? '');
            return strcmp($ka, $kb);
        });
        $this->line("v5 dump: clients={" . count($clients) . "}, visits={" . count($visits) . "}, bookingDetails={" . count($bookingDetails) . "}, clientDetails={" . count($clientDetails) . "}");

        $apiController = app(\App\Http\Controllers\ApiController::class);

        // Paket satislarini dedup etmek icin global set (aynı paket farkli visit'lerde
        // tekrarlaniyor cunku salonappy musteriinin aktif paketlerini her visit detayinda gosteriyor).
        $seenPkgIds = [];

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

            // Dedup: marker. Marker'li randevuda DELTA SYNC yap — visite sonradan eklenen
            // payments[] ve product_sales[]'i tarayip eksikleri ekle. continue YOK.
            $markerExistsRandevu = \DB::table('randevular')->where('salon_id', $salonId)
                ->where('user_id', $userId)
                ->where('personel_notu', 'LIKE', '%' . $marker . '%')->exists();
            if ($markerExistsRandevu) {
                $rDedup++;
                // Mevcut adisyon_id'yi bul (marker LIKE)
                $existAdId = \DB::table('adisyonlar')->where('salon_id', $salonId)
                    ->where('user_id', $userId)
                    ->where('notlar', 'LIKE', '%' . $marker . '%')
                    ->value('id');
                if ($existAdId && !empty($bd['payments'])) {
                    foreach ($bd['payments'] as $pIdx => $p) {
                        $tutar = (float) ($p['amount'] ?? 0);
                        if ($tutar <= 0) continue;
                        $odemeYontem = $p['payment_method_text'] ?? $p['payment_method'] ?? 'Nakit';
                        $odemeTarih = $p['date'] ?? $tarih;
                        $payMarker = '[salonappy-pay:' . $session . ':' . $pIdx . ']';
                        // 1) Onceden bu payment marker'i ile yazilmis mi?
                        $existPay = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                            ->where('user_id', $userId)
                            ->where('notlar', 'LIKE', '%' . $payMarker . '%')->exists();
                        if ($existPay) continue;
                        // 2) Marker yok ama ayni user+adisyon+tarih+tutar+yontem var mi
                        $existSame = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                            ->where('user_id', $userId)->where('adisyon_id', $existAdId)
                            ->where('odeme_tarihi', $odemeTarih)
                            ->where('tutar', $tutar)->exists();
                        if ($existSame) continue;
                        try {
                            $tReq = new \Illuminate\Http\Request([
                                'userId' => $userId, 'adisyonId' => $existAdId,
                                'odemeTarihi' => $odemeTarih, 'tahsilatTutari' => $tutar,
                                'odemeYontemi' => $odemeYontem, 'salonId' => $salonId,
                            ]);
                            $apiController->salonAppyTahsilatEkle($tReq);
                            $tEklenen++;
                            $newT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('user_id', $userId)->where('odeme_tarihi', $odemeTarih)
                                ->where('tutar', $tutar)->orderByDesc('id')->first();
                            if ($newT && \Schema::hasColumn('tahsilatlar', 'notlar')) {
                                \DB::table('tahsilatlar')->where('id', $newT->id)->update(['notlar' => $payMarker]);
                            }
                        } catch (\Throwable $e) {}
                    }
                }
                // Eksik ürün satışlarını da ekle (mevcut AU ile karşılaştır)
                if ($existAdId && !empty($bd['product_sales'])) {
                    foreach ($bd['product_sales'] as $uIdx => $p) {
                        $urunAdi = trim((string) ($p['product_text'] ?? $p['product_name'] ?? $p['name'] ?? ''));
                        if ($urunAdi === '') continue;
                        $fiyat = (float) ($p['amount'] ?? $p['price'] ?? 0);
                        $adet = max(1, (int) ($p['quantity'] ?? $p['qty'] ?? 1));
                        $urunMarker = '[salonappy-urun:' . $session . ':' . $uIdx . ']';
                        // Aynı adisyon altında ayni urun+adet+fiyat var mı?
                        $urunId = $this->ensureUrun($salonId, $urunAdi, $fiyat, $urunAdi);
                        if (!$urunId) continue;
                        $existAu = \DB::table('adisyon_urunler')
                            ->where('adisyon_id', $existAdId)->where('urun_id', $urunId)
                            ->where('fiyat', $fiyat)->where('adet', $adet)->exists();
                        if ($existAu) continue;
                        try {
                            \DB::table('adisyon_urunler')->insert([
                                'adisyon_id' => $existAdId, 'urun_id' => $urunId,
                                'fiyat' => $fiyat, 'adet' => $adet,
                                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        } catch (\Throwable $e) {}
                    }
                }
                continue;
            }

            // Hizmetler itemized. service_text bos olanlar (salonappy'de silinmis hizmet
            // referanslari) atlanir; placeholder uretilmez.
            $hizmetler = [];
            foreach (($bd['services'] ?? []) as $s) {
                $ad = trim((string) ($s['service_text'] ?? ''));
                if ($ad === '') continue;
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
            // Paket satislari: dedup et (aynı paket id farkli visit'lerde tekrar gorunur).
            // Sadece ilk gordugumuz visit'e ekle. package_sales'ta personel bilgisi YOK.
            $paketSales = $bd['package_sales'] ?? [];
            $paketHizmetAdlari = [];
            foreach ($paketSales as $pkg) {
                $pid = $pkg['id'] ?? null;
                if (!$pid || isset($seenPkgIds[$pid])) continue;
                $seenPkgIds[$pid] = true;
                $ad = trim((string) ($pkg['service_text'] ?? ''));
                if ($ad === '') continue;
                $quantity = (int) ($pkg['quantity'] ?? 1);
                $amount = (float) ($pkg['amount'] ?? 0);
                $hizmetler[] = [
                    'hizmet'   => $ad,
                    'personel' => '', // paket satisinda personel yok
                    'fiyat'    => $amount,
                    'sureDk'   => 30,
                ];
                $paketHizmetAdlari[] = ['ad' => $ad, 'quantity' => $quantity, 'amount' => $amount];
            }

            // Ürünler itemized. product_sales'ta staff_name yok; staff_id + staff[] lookup ile resolve.
            $urunler = [];
            foreach (($bd['product_sales'] ?? []) as $p) {
                $personelAdi = '';
                $sid = (string) ($p['staff_id'] ?? '');
                if ($sid !== '' && !empty($p['staff']) && is_array($p['staff'])) {
                    foreach ($p['staff'] as $st) {
                        if ((string) ($st['value'] ?? '') === $sid) {
                            $personelAdi = $st['label'] ?? '';
                            break;
                        }
                    }
                }
                if ($personelAdi === '') $personelAdi = $p['staff_name'] ?? '';
                $urunler[] = [
                    'urun'     => $p['product_text'] ?? $p['product_name'] ?? $p['name'] ?? '',
                    'personel' => $personelAdi,
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

                // Paket kullanimlari: package_usages'den seans dusumu (varsa)
                // Salonappy bu alani sik sik bos birakiyor — eger doluysa kullan,
                // degilse asagidaki "visit services'a gore otomatik tuketim" devreye girer.
                if (!empty($bd['package_usages'])) {
                    foreach ($bd['package_usages'] as $use) {
                        $hizmetAd = trim((string) ($use['service_text'] ?? ''));
                        if ($hizmetAd === '') continue;
                        $canonAd = $hizmetAd;
                        $this->ensureSalonHizmet($salonId, $hizmetAd, 30, 0, $canonAd);
                        $kullanimSayisi = (int) ($use['quantity'] ?? 1);
                        $kullanimTarih = $use['date'] ?? $tarih;
                        $this->salonappySeansiTuket($userId, $salonId, $canonAd, $kullanimTarih, $saat, $kullanimSayisi);
                    }
                } else {
                    // Drklinik mantigi: visit'in her hizmeti icin musterinin acik paketinde
                    // ayni hizmet varsa OTOMATIK paket seansi tuket. status/showup kontrol:
                    //  - Iptal/gelmedi randevu ise tuketme (paket placeholder geldi=0 kalir).
                    //  - Approved + Showed up (geldi) ise tuket.
                    $statusKel = $this->normalizeStatus($v['status_text'] ?? '', $detail['status'] ?? null);
                    $geldiKel = $this->normalizeShowup($v['showup_text'] ?? '', $detail['showup'] ?? null);
                    $iptalMi = (mb_stripos($statusKel, 'iptal', 0, 'UTF-8') !== false);
                    $gelmediMi = (mb_stripos($geldiKel, 'gelmedi', 0, 'UTF-8') !== false);
                    if (!$iptalMi && !$gelmediMi) {
                        foreach ($hizmetler as $h) {
                            $hAd = trim((string) ($h['hizmet'] ?? ''));
                            if ($hAd === '') continue;
                            $this->salonappySeansiTuket($userId, $salonId, $hAd, $tarih, $saat, 1);
                        }
                    }
                }

                // Tahsilatlar itemized (payments[] dolu ise her birini ayrı ekle)
                // Dedup: ayni user+tarih+tutar+yontem (payment_method) → skip.
                // Onceki kod sadece (user+tarih+tutar) bakiyordu → ayni adisyonda ayni
                // tutarda farkli yontem (orn: 750 Nakit + 750 KK) varsa 2. atlanip
                // payment parser tarafindan adisyon_id NULL ile ayri ekleniyordu.
                // payment_method_text karsilastirmasiyla artik ikisi de visit adisyonuna baglanir.
                if ($adisyonId && ctype_digit($adisyonId) && !empty($bd['payments'])) {
                    foreach ($bd['payments'] as $p) {
                        $tutar = (float) ($p['amount'] ?? 0);
                        if ($tutar <= 0) continue;
                        $odemeYontem = $p['payment_method_text'] ?? $p['payment_method'] ?? 'Nakit';
                        $odemeTarih = $p['date'] ?? $tarih;
                        // Marker bazli dedup: bu payment_id zaten yazilmis mi?
                        $payId = (string) ($p['id'] ?? '');
                        $payIdMarker = $payId ? '[salonappy-pay:' . $session . ':' . $payId . ']' : '';
                        if ($payIdMarker) {
                            $exists = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('notlar', 'LIKE', '%' . $payIdMarker . '%')->exists();
                            if ($exists) continue;
                        }
                        try {
                            $tReq = new \Illuminate\Http\Request([
                                'userId' => $userId, 'adisyonId' => $adisyonId,
                                'odemeTarihi' => $odemeTarih, 'tahsilatTutari' => $tutar,
                                'odemeYontemi' => $odemeYontem, 'salonId' => $salonId,
                            ]);
                            $apiController->salonAppyTahsilatEkle($tReq);
                            $tEklenen++;
                            // Tahsilata marker yaz (reset + payment dedup icin)
                            $newT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('user_id', $userId)->where('odeme_tarihi', $odemeTarih)
                                ->where('tutar', $tutar)->orderByDesc('id')->first();
                            if ($newT && \Schema::hasColumn('tahsilatlar', 'notlar')) {
                                $not = trim($marker . ($payIdMarker ? ' ' . $payIdMarker : ''));
                                \DB::table('tahsilatlar')->where('id', $newT->id)->update(['notlar' => $not]);
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

        // 3) STANDALONE PRODUCT_SALES (visit'ten bagimsiz urun satislari)
        // is_session=false olanlar manuel kasa girisi. Visit-bagli (is_session=true)
        // olanlar zaten yukaridaki product_sales pipeline'i ile islendi, atla.
        $ps = $j['productSales'] ?? [];
        if (!empty($ps)) {
            $this->line('Standalone urun satislari isleniyor: ' . count($ps) . ' kayit');
            $psEklenen = 0; $psDedup = 0; $psHata = 0; $psTahsilat = 0;
            foreach ($ps as $sale) {
                // Visit-bagli ise visit pipeline zaten isledi, atla
                if (!empty($sale['is_session'])) { $psDedup++; continue; }
                $saleId = (string) ($sale['id'] ?? '');
                if (!$saleId) { $psHata++; continue; }
                $saleMarker = '[salonappy-prodsale:' . $saleId . ']';
                // Dedup: marker ile mevcut mu
                $existAd = \DB::table('adisyonlar')->where('salon_id', $salonId)
                    ->where('notlar', 'LIKE', '%' . $saleMarker . '%')->value('id');
                if ($existAd) { $psDedup++; continue; }
                // Musteri eslestir (idMap'ten veya telefondan)
                $clientId = (string) ($sale['client_id'] ?? '');
                $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
                if (!$userId) {
                    $phone = preg_replace('~\D~', '', $sale['client_full_phone_number'] ?? $sale['client_phone_number'] ?? '');
                    if ($phone) $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                }
                if (!$userId) { $psHata++; continue; }
                $tarih = $sale['date'] ?? date('Y-m-d');
                $urunAdi = trim((string) ($sale['product_text'] ?? ''));
                $fiyat = (float) ($sale['product_price'] ?? 0);
                $adet = max(1, (int) ($sale['quantity'] ?? 1));
                $toplam = (float) ($sale['total_amount'] ?? ($fiyat * $adet));
                $odenen = (float) ($sale['paid_amount'] ?? 0);
                $odemeYontem = $sale['payment_method_text'] ?? $sale['payment_method'] ?? 'Nakit';
                $sellerAdi = $sale['seller_name'] ?? '';
                $notlar = trim(($sale['notes'] ?? '') . ' ' . $saleMarker);
                $urunId = $this->ensureUrun($salonId, $urunAdi, $fiyat, $urunAdi);
                if (!$urunId) { $psHata++; continue; }
                if ($sellerAdi) $this->ensurePersonel($salonId, $sellerAdi);
                try {
                    // Adisyon olustur (urun satisi adisyonu)
                    $defaultPersId = $sellerAdi
                        ? \DB::table('salon_personelleri')->where('salon_id', $salonId)
                            ->where('personel_adi', $sellerAdi)->value('id')
                        : null;
                    $adId = \DB::table('adisyonlar')->insertGetId([
                        'salon_id' => $salonId, 'user_id' => $userId, 'tarih' => $tarih,
                        'olusturan_id' => $defaultPersId, 'notlar' => $notlar,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    \DB::table('adisyon_urunler')->insert([
                        'adisyon_id' => $adId, 'urun_id' => $urunId,
                        'fiyat' => $fiyat, 'adet' => $adet,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $psEklenen++;
                    // Tahsilat (paid_amount > 0 ise)
                    if ($odenen > 0.01) {
                        $tReq = new \Illuminate\Http\Request([
                            'userId' => $userId, 'adisyonId' => $adId,
                            'odemeTarihi' => $tarih, 'tahsilatTutari' => $odenen,
                            'odemeYontemi' => $odemeYontem, 'salonId' => $salonId,
                        ]);
                        $apiController->salonAppyTahsilatEkle($tReq);
                        $newT = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                            ->where('user_id', $userId)->where('odeme_tarihi', $tarih)
                            ->where('tutar', $odenen)->orderByDesc('id')->first();
                        if ($newT && \Schema::hasColumn('tahsilatlar', 'notlar')) {
                            \DB::table('tahsilatlar')->where('id', $newT->id)->update(['notlar' => $saleMarker]);
                        }
                        $psTahsilat++;
                    }
                } catch (\Throwable $e) {
                    $psHata++;
                    \Log::warning('[Salonappy prodsale] hata', ['id' => $saleId, 'err' => $e->getMessage()]);
                }
            }
            $this->info("Standalone urun: eklenen={$psEklenen}, dedup={$psDedup}, hata={$psHata}, tahsilat={$psTahsilat}");
        }

        // 4) GIDERLER / MASRAFLAR (/api/expense/list)
        // Marker [salonappy-expense:id], masraflar tablosuna idempotent insert.
        $exps = $j['expenses'] ?? [];
        if (!empty($exps)) {
            $this->line('Giderler isleniyor: ' . count($exps) . ' kayit');
            $eEklenen = 0; $eDedup = 0; $eHata = 0;
            $masTable = (new \App\Masraflar)->getTable();
            $hasNotlar = \Schema::hasColumn($masTable, 'notlar');
            foreach ($exps as $ex) {
                $exId = (string) ($ex['id'] ?? '');
                if (!$exId) { $eHata++; continue; }
                $exMarker = '[salonappy-expense:' . $exId . ']';
                if ($hasNotlar) {
                    $exists = \DB::table($masTable)->where('salon_id', $salonId)
                        ->where('notlar', 'LIKE', '%' . $exMarker . '%')->exists();
                    if ($exists) { $eDedup++; continue; }
                }
                $tarih = $ex['date'] ?? date('Y-m-d');
                $tutar = (float) ($ex['amount'] ?? 0);
                $aciklama = trim((string) ($ex['description_raw'] ?? $ex['description'] ?? ''));
                $kategoriAd = trim((string) ($ex['category_text'] ?? ''));
                $odemeYontem = $ex['payment_method_text'] ?? $ex['payment_method'] ?? 'Nakit';
                $harcayanAd = (string) ($ex['created_by_name'] ?? '');
                try {
                    $kategoriId = null;
                    if ($kategoriAd && \Schema::hasColumn($masTable, 'masraf_kategori_id')) {
                        $kategoriId = \DB::table('masraf_kategorisi')->where('salon_id', $salonId)
                            ->where('masraf_kategorisi_adi', $kategoriAd)->value('id');
                        if (!$kategoriId) {
                            $kategoriId = \DB::table('masraf_kategorisi')->insertGetId([
                                'salon_id' => $salonId, 'kategori' => $kategoriAd,
                                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
                    $harcayanId = null;
                    if ($harcayanAd && \Schema::hasColumn($masTable, 'harcayan_id')) {
                        $harcayanId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                            ->where('personel_adi', 'LIKE', '%' . $harcayanAd . '%')->value('id');
                    }
                    $row = [
                        'salon_id' => $salonId, 'tarih' => $tarih, 'tutar' => $tutar,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if (\Schema::hasColumn($masTable, 'aciklama')) $row['aciklama'] = $aciklama;
                    if ($kategoriId) $row['masraf_kategori_id'] = $kategoriId;
                    if ($harcayanId) $row['harcayan_id'] = $harcayanId;
                    if ($hasNotlar) $row['notlar'] = $exMarker;
                    \DB::table($masTable)->insert($row);
                    $eEklenen++;
                } catch (\Throwable $e) {
                    $eHata++;
                    \Log::warning('[Salonappy expense] hata', ['id' => $exId, 'err' => $e->getMessage()]);
                }
            }
            $this->info("Giderler: eklenen={$eEklenen}, dedup={$eDedup}, hata={$eHata}");
        }

        // 5) PACKAGE SALES — /api/package_sale/list_v2 zengin alanlar:
        //  usage_date doluysa AH.geldi=1 + APS.geldi=1 (seans kullanildi)
        //  paid_amount > 0 ise tahsilat ekle (marker [salonappy-pkgsale-pay:id])
        //  total_usage / quantity bazli AH.seans_sayisi
        //  client_phone_number_local ile telefon eslestirme
        $pkgSales = $j['packageSales'] ?? [];
        if (!empty($pkgSales)) {
            $groups = [];
            foreach ($pkgSales as $row) {
                $gid = (string) ($row['group_id'] ?? $row['id'] ?? '');
                if (!$gid) continue;
                $groups[$gid][] = $row;
            }
            $this->line('Package sales gruplari: ' . count($groups) . ' (toplam satir: ' . count($pkgSales) . ')');
            $gEklenen = 0; $gDedup = 0; $gHata = 0; $gTah = 0;
            foreach ($groups as $gid => $rows) {
                $marker = '[salonappy-pkgsale:' . $gid . ']';
                $exists = \DB::table('adisyonlar')->where('salon_id', $salonId)
                    ->where('notlar', 'LIKE', '%' . $marker . '%')->exists();
                if ($exists) { $gDedup++; continue; }
                $first = $rows[0];
                $clientId = (string) ($first['client_id'] ?? '');
                $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
                if (!$userId) {
                    // Telefon match (v2 zengin alan)
                    $phone = preg_replace('~\D~', '', (string) ($first['client_phone_number_local'] ?? ''));
                    if ($phone) $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                }
                if (!$userId) {
                    $clientName = trim((string) ($first['client_name'] ?? ''));
                    if ($clientName) $userId = \DB::table('users')->where('name', 'LIKE', $clientName)->orderByDesc('id')->value('id');
                }
                if (!$userId) { $gHata++; continue; }
                $tarih = $first['date'] ?? date('Y-m-d');
                try {
                    $adId = \DB::table('adisyonlar')->insertGetId([
                        'salon_id' => $salonId, 'user_id' => $userId, 'tarih' => $tarih,
                        'notlar' => $marker,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    foreach ($rows as $r) {
                        $svcAd = trim((string) ($r['service_text'] ?? ''));
                        if ($svcAd === '') continue;
                        $period = max(1, (int) ($r['quantity'] ?? 1));
                        $kullanilan = max(0, (int) ($r['total_usage'] ?? 0));
                        $tutar = (float) ($r['total_amount'] ?? 0);
                        $fiyat = $period > 0 ? round($tutar / $period, 2) : $tutar;
                        $usageDate = trim((string) ($r['usage_date'] ?? '')) ?: null;
                        $hid = $this->ensureSalonHizmet($salonId, $svcAd, 30, $fiyat, $svcAd);
                        if (!$hid) continue;
                        // AH.geldi: tum quantity kullanildiysa 1, kismi/hic ise 0
                        $ahGeldi = ($kullanilan >= $period) ? 1 : 0;
                        $ahId = \DB::table('adisyon_hizmetler')->insertGetId([
                            'adisyon_id' => $adId, 'hizmet_id' => $hid,
                            'geldi' => $ahGeldi, 'islem_tarihi' => ($usageDate ?: $tarih), 'islem_saati' => '00:00:00',
                            'sure' => 30, 'fiyat' => $tutar, 'seans_sayisi' => $period,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        // N adet APS: ilk $kullanilan tanesi geldi=1 (usage_date), kalani geldi=0 placeholder
                        for ($i = 1; $i <= $period; $i++) {
                            $apsGeldi = ($i <= $kullanilan) ? 1 : 0;
                            $row = [
                                'adisyon_hizmet_id' => $ahId, 'hizmet_id' => $hid,
                                'seans_no' => $i, 'geldi' => $apsGeldi,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];
                            if ($apsGeldi && $usageDate) {
                                $row['seans_tarih'] = $usageDate;
                                $row['seans_saat'] = '00:00:00';
                                if (\Schema::hasColumn('adisyon_paket_seanslar', 'dusulen_miktar')) $row['dusulen_miktar'] = 1;
                            }
                            \DB::table('adisyon_paket_seanslar')->insert($row);
                        }
                        // Tahsilat: paid_amount > 0 ise (her satir kendi paymentini gosterir)
                        $paid = (float) ($r['paid_amount'] ?? 0);
                        if ($paid > 0) {
                            $payDate = trim((string) ($r['payment_date'] ?? '')) ?: $tarih;
                            $payMarker = '[salonappy-pkgsale-pay:' . ($r['id'] ?? $gid) . ']';
                            $payExists = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                                ->where('notlar', 'LIKE', '%' . $payMarker . '%')->exists();
                            if (!$payExists) {
                                \DB::table('tahsilatlar')->insert([
                                    'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => $adId,
                                    'odeme_tarihi' => $payDate, 'tutar' => $paid,
                                    'odeme_yontemi_id' => 1, 'notlar' => $payMarker,
                                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                                ]);
                                $gTah++;
                            }
                        }
                    }
                    $gEklenen++;
                } catch (\Throwable $e) {
                    $gHata++;
                    \Log::warning('[Salonappy pkgsale] hata', ['gid' => $gid, 'err' => $e->getMessage()]);
                }
            }
            $this->info("Package sales: eklenen={$gEklenen}, dedup={$gDedup}, hata={$gHata}, tahsilat={$gTah}");
        }

        // 6) PAYMENTS — comprehensive tahsilat listesi
        // /api/payment/list tum kaynaklarin tahsilatlarini tek listede veriyor (Adisyon /
        // Paket satisi / Urun satisi / Borc odemesi vb). Her payment id ile dedup yapilir.
        // Visit/urun pipeline kendi tahsilatlarini ekledi ama marker'lari yok — burada
        // [salonappy-payment:id] marker'i ile bizdeki tahsilat'a check yapip eksikleri ekle.
        $pays = $j['payments'] ?? [];
        if (!empty($pays)) {
            $this->line('Payments isleniyor: ' . count($pays) . ' kayit');
            $pEklenen = 0; $pDedup = 0; $pHata = 0;
            foreach ($pays as $pay) {
                $payId = (string) ($pay['id'] ?? '');
                if (!$payId) { $pHata++; continue; }
                $payMarker = '[salonappy-payment:' . $payId . ']';
                $exists = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                    ->where('notlar', 'LIKE', '%' . $payMarker . '%')->exists();
                if ($exists) { $pDedup++; continue; }
                $tutar = (float) ($pay['amount'] ?? 0);
                if ($tutar <= 0) continue;
                $odemeTarih = $pay['date'] ?? date('Y-m-d');
                $odemeYontem = $pay['payment_method_text'] ?? 'Nakit';
                // client_id YOK — sadece client_name var. Telefon mevcut olmadigi icin
                // name match (mecburen) — multi-match riski var, en yeni user'i secelim.
                $clientName = trim((string) ($pay['client_name'] ?? ''));
                $userId = null;
                if ($clientName) {
                    $userId = \DB::table('users')
                        ->where('name', 'LIKE', $clientName)
                        ->orderByDesc('id')->value('id');
                }
                if (!$userId) { $pHata++; continue; }
                // Cakisma engeli: ayni user+tarih+tutar'da BASKA bir salonappy marker'li
                // tahsilat (visit/urun/pkgsale pipeline ekledi: [salonappy:sess],
                // [salonappy-prodsale:id], [salonappy-pkgsale:gid]) varsa o tahsilata
                // payment marker'i CONCAT'le, yeni tahsilat INSERT etme. Boylece duplicate olmaz.
                // Onceki kod sadece whereNull('notlar') aramasiyla yapiyordu —
                // visit pipeline marker yazdigi icin (NULL degil) match olmuyor,
                // duplicate insert ediyordu.
                $eslesen = \DB::table('tahsilatlar')->where('salon_id', $salonId)
                    ->where('user_id', $userId)
                    ->where('odeme_tarihi', $odemeTarih)
                    ->where('tutar', $tutar)
                    ->where('notlar', 'NOT LIKE', '%[salonappy-payment:%')
                    ->orderBy('id')->first();
                if ($eslesen) {
                    $yeniNot = trim(($eslesen->notlar ?? '') . ' ' . $payMarker);
                    \DB::table('tahsilatlar')->where('id', $eslesen->id)->update(['notlar' => $yeniNot]);
                    $pDedup++;
                    continue;
                }
                // Tamamen yeni tahsilat (ozellikle "Paket satisi" source — paymentslar
                // visit pipeline'a girmiyor). Adisyon_id NULL (bagimsiz kasa kaydi).
                try {
                    \DB::table('tahsilatlar')->insert([
                        'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => null,
                        'odeme_tarihi' => $odemeTarih, 'tutar' => $tutar,
                        'odeme_yontemi_id' => 1, 'notlar' => $payMarker,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $pEklenen++;
                } catch (\Throwable $e) {
                    $pHata++;
                    \Log::warning('[Salonappy payment] hata', ['id' => $payId, 'err' => $e->getMessage()]);
                }
            }
            $this->info("Payments: eklenen={$pEklenen}, dedup={$pDedup}, hata={$pHata}");
        }

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
    /**
     * Dump'tan SADECE paket satislarini parse et — visit/payment/expense'i atla.
     * usage_date doluysa APS.geldi=1 + seans_tarih, paid_amount > 0 ise tahsilat.
     */
    private function importPackageSalesOnly($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $pkgSales = $j['packageSales'] ?? [];
        if (empty($pkgSales)) { $this->warn('packageSales[] bos veya yok'); return 0; }

        // Musteri eslestirme icin clients map (idMap)
        $idMap = [];
        foreach (($j['clients'] ?? []) as $c) {
            $sid = (string) ($c['id'] ?? '');
            if (!$sid) continue;
            $phone = preg_replace('~\D~', '', (string) ($c['phone_number_local'] ?? $c['phone_number'] ?? ''));
            if ($phone) {
                $uid = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                if ($uid) $idMap[$sid] = $uid;
            }
        }
        $this->line('idMap kuruldu: ' . count($idMap) . ' musteri eslesti');

        $groups = [];
        foreach ($pkgSales as $row) {
            $gid = (string) ($row['group_id'] ?? $row['id'] ?? '');
            if (!$gid) continue;
            $groups[$gid][] = $row;
        }
        $this->line('Package sales gruplari: ' . count($groups) . ' (toplam satir: ' . count($pkgSales) . ')');

        // Yontem text -> id mapping (sistem standardı: 1 Nakit, 2 Kart, 3 Havale, 4 Diger)
        $yontemMap = function ($txt) {
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if ($t === '') return 1;
            if (strpos($t, 'kredi') !== false || strpos($t, 'kart') !== false || strpos($t, 'pos') !== false) return 2;
            if (strpos($t, 'havale') !== false || strpos($t, 'eft') !== false || strpos($t, 'banka') !== false) return 3;
            if (strpos($t, 'nakit') !== false) return 1;
            return 4;
        };
        // Isim normalize: lowercase + trim + collapse-space + Turkce karakter sade
        $normName = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $s = preg_replace('~\s+~', ' ', $s);
            return $s;
        };
        // Servis adi normalize (icerik karsilastirmasi icin)
        $normSvc = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $s = str_replace(['(', ')', '.', ','], ' ', $s);
            $s = preg_replace('~\s+~', ' ', $s);
            return trim($s);
        };

        // pkgIndex: gid => paket adisyon meta (Pass2/Pass3 icin)
        $pkgIndex = [];

        $gEklenen = 0; $gDedup = 0; $gHata = 0; $gTah = 0; $gTaksit = 0;
        foreach ($groups as $gid => $rows) {
            $marker = '[salonappy-pkgsale:' . $gid . ']';
            // UPSERT MOD: mevcut markerli adisyon varsa once SIL (+ bagli kayitlar),
            // sonra yeniden yaz. Boylece her import tek seferde tamamlanir, eski TH/AH/APS
            // kalintilari temizlenir (kullanicinin istegi: "tek seferde eksik tamamlama").
            $eskiAdIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%' . $marker . '%')->pluck('id')->all();
            if (!empty($eskiAdIds)) {
                $eskiAhIds = \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                $eskiTahIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                // Bagli tahsilat marker'larini bul (pkgsale-pay)
                if (!empty($eskiTahIds)) {
                    \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
                    \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
                    \DB::table('tahsilatlar')->whereIn('id', $eskiTahIds)->delete();
                }
                if (!empty($eskiAhIds)) {
                    \DB::table('adisyon_paket_seanslar')->whereIn('adisyon_hizmet_id', $eskiAhIds)->delete();
                }
                // Taksitli tahsilatlar + vadeler + alacaklar
                $eskiTtIds = \DB::table('taksitli_tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                if (!empty($eskiTtIds)) {
                    \DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $eskiTtIds)->delete();
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    }
                    \DB::table('taksitli_tahsilatlar')->whereIn('id', $eskiTtIds)->delete();
                }
                \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                \DB::table('adisyon_urunler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                \DB::table('adisyonlar')->whereIn('id', $eskiAdIds)->delete();
                $gDedup++; // sayim: yeniden yazildi
            }
            $first = $rows[0];
            $clientId = (string) ($first['client_id'] ?? '');
            $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
            if (!$userId) {
                $phone = preg_replace('~\D~', '', (string) ($first['client_phone_number_local'] ?? ''));
                if ($phone) $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
            }
            if (!$userId) {
                $clientName = trim((string) ($first['client_name'] ?? ''));
                if ($clientName) $userId = \DB::table('users')->where('name', 'LIKE', $clientName)->orderByDesc('id')->value('id');
            }
            if (!$userId) { $gHata++; continue; }
            $tarih = $first['date'] ?? date('Y-m-d');
            try {
                $adId = \DB::table('adisyonlar')->insertGetId([
                    'salon_id' => $salonId, 'user_id' => $userId, 'tarih' => $tarih,
                    'notlar' => $marker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $grpReceivable = 0.0;
                $grpVadeTarih = null;
                foreach ($rows as $r) {
                    // Salonappy iki yapi:
                    //  (a) is_group=true + group_items[] → her hizmet KENDI quantity+total_usage+total_amount
                    //      ile detay (Ayse Gurbuz: 4800 Heykeltras 4 seans, 3500 G5 10 seans, 4000 Yag Yakma 4 seans).
                    //      Ust seviye service_text "A, B, C" virguluyle birlestirilmis ozet — KULLANMA.
                    //  (b) is_group=false → tek satir, kendi alanlari kullanilir.
                    $items = (!empty($r['is_group']) && !empty($r['group_items']) && is_array($r['group_items']))
                        ? $r['group_items']
                        : [$r];
                    // Alacak iki sinyalden biri:
                    //   (a) receivable_amount > 0 — Salonappy'nin "alacak" alani
                    //   (b) remaining_payment > 0 AND paid_amount > 0 — kismi odenmis paket
                    //       (Salonappy bazen receivable_amount'i guncellemiyor; orn. Gulcan Lazer Bacak
                    //       paid=2250 rem=2250 rec=0 ama UI'da alacak.)
                    // Hic odenmemis paketler (paid=0, rec=0) alacak DEGIL (Hayal Kiran vb).
                    $rec  = (float) ($r['receivable_amount'] ?? 0);
                    $rem  = (float) ($r['remaining_payment'] ?? 0);
                    $paid = (float) ($r['paid_amount'] ?? 0);
                    if ($rec > 0.01) {
                        $grpReceivable += $rec;
                    } elseif ($rem > 0.01 && $paid > 0.01) {
                        $grpReceivable += $rem;
                    }
                    // Vade tarihi = paket created_at'inin gun kismi. Salonappy UI'sinde alacak
                    // tarihi bu degerdir (date veya payment_date degil). Onder Yilmaz date=03-20
                    // pay_date=04-21 ama UI 25.03 -> created_at=2025-03-25 20:35 dogru sinyal.
                    $cAt = trim((string) ($r['created_at'] ?? ''));
                    if ($grpVadeTarih === null && $cAt !== '') {
                        $grpVadeTarih = substr($cAt, 0, 10);
                    }
                    foreach ($items as $it) {
                        $svcAd = trim((string) ($it['service_text'] ?? ''));
                        if ($svcAd === '') continue;
                        $qtyRaw = $it['quantity'] ?? null;
                        $period = ($qtyRaw === null || $qtyRaw === '') ? null : max(1, (int) $qtyRaw);
                        $tutar = (float) ($it['total_amount'] ?? 0);
                        $fiyatBirim = ($period && $period > 0) ? round($tutar / $period, 2) : $tutar;
                        $hid = $this->ensureSalonHizmet($salonId, $svcAd, 30, $fiyatBirim, $svcAd);
                        if (!$hid) continue;
                        // Paket import sirasinda HIC isaretleme YAPMA — kullanicinin istegi:
                        // "ilk paketleri aktarirken isaretleme yapmayacagiz". total_usage / usage_date
                        // YOK SAYILIR; AH.geldi=0, APS hepsi geldi=0 (bekleyen). Randevular geldikce
                        // visit pipeline + drklinik mantigi seanslari tuketir.
                        $ahRow = [
                            'adisyon_id' => $adId, 'hizmet_id' => $hid,
                            'geldi' => 0, 'islem_tarihi' => $tarih, 'islem_saati' => '00:00:00',
                            'sure' => 30, 'fiyat' => $tutar,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        if ($period !== null) $ahRow['seans_sayisi'] = $period;
                        $ahId = \DB::table('adisyon_hizmetler')->insertGetId($ahRow);
                        // total_usage: Salonappy'nin kayitli kullanim sayisi. Bazi seanslar
                        // visit-detail disinda manuel girilmis olabilir (Duru Datca total_usage=7,
                        // package_usages=5 — 2 manuel kullanim). Visit Pass package_usages icin
                        // tarihsiz APS'lerin seans_tarih'ini update eder; geri kalanlar NULL kalir.
                        // Boylece "kalan seans" hesabi (seans_sayisi - aps_sayisi) Salonappy ile esit.
                        $totalUsage = (int) ($it['total_usage'] ?? 0);
                        if ($totalUsage > 0) {
                            for ($i = 1; $i <= $totalUsage; $i++) {
                                \DB::table('adisyon_paket_seanslar')->insert([
                                    'adisyon_hizmet_id' => $ahId, 'hizmet_id' => $hid,
                                    'seans_no' => $i, 'geldi' => 1,
                                    'seans_tarih' => null,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ]);
                            }
                        }
                    }
                }
                // Planlanan alacak: receivable_amount > 0 ise UI "Satis Takibi" sayfasi icin
                // TaksitliTahsilatlar + TaksitVadeleri (taksit_vade_id ile baglanir) + Alacaklar
                // uclusunu birlikte yaz; aksi halde satis takibi tablosunda gozukmez.
                // Vade = payment_date varsa onu, yoksa paket satis tarihi.
                if ($grpReceivable > 0.01) {
                    $vadeTarih = $grpVadeTarih ?: $tarih;
                    $tutar = round($grpReceivable, 2);
                    $tt = \DB::table('taksitli_tahsilatlar')->insertGetId([
                        'user_id' => $userId, 'adisyon_id' => $adId,
                        'salon_id' => $salonId, 'vade_sayisi' => 1,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $vadeId = \DB::table('taksit_vadeleri')->insertGetId([
                        'taksitli_tahsilat_id' => $tt, 'odendi' => 0,
                        'vade_tarih' => $vadeTarih, 'tutar' => $tutar,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->insert([
                            'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => $adId,
                            'tutar' => $tutar, 'taksit_vade_id' => $vadeId,
                            'planlanan_odeme_tarihi' => $vadeTarih,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    \DB::table('adisyon_hizmetler')->where('adisyon_id', $adId)
                        ->whereNull('taksitli_tahsilat_id')
                        ->update(['taksitli_tahsilat_id' => $tt]);
                    $gTaksit++;
                }
                $gEklenen++;
            } catch (\Throwable $e) {
                $gHata++;
                \Log::warning('[Salonappy pkgsale-only] hata', ['gid' => $gid, 'err' => $e->getMessage()]);
            }
        }

        $this->info("Package sales (PASS1 - adisyon+AH+APS+alacak, tahsilat YOK): eklenen=$gEklenen, dedup=$gDedup, hata=$gHata, alacak=$gTaksit");
        $this->line('Bir sonraki adim: --only-package-payments ile ayni dump dosyasiyla tahsilatlari yaz.');
        return 0;
    }

    /**
     * PASS2 + PASS3: Daha onceden --only-package-sales ile yazilmis [salonappy-pkgsale:gid] markerli
     * paket adisyonlari icin dump payments[] tarayip source_text="Paket satisi" olanlari eslestirip
     * tahsilat insert, kalan tutari TaksitliTahsilatlar+Vadeler+Alacaklar olarak yazar.
     */
    private function importPackagePaymentsOnly($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $payments = $j['payments'] ?? [];
        if (empty($payments)) { $this->warn('payments[] bos.'); return 0; }

        $yontemMap = function ($txt) {
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if ($t === '') return 1;
            if (strpos($t, 'kredi') !== false || strpos($t, 'kart') !== false || strpos($t, 'pos') !== false) return 2;
            if (strpos($t, 'havale') !== false || strpos($t, 'eft') !== false || strpos($t, 'banka') !== false) return 3;
            if (strpos($t, 'nakit') !== false) return 1;
            return 4;
        };
        $normName = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            return preg_replace('~\s+~', ' ', $s);
        };
        $normSvc = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $s = str_replace(['(', ')', '.', ','], ' ', $s);
            return trim(preg_replace('~\s+~', ' ', $s));
        };

        // 1) UPSERT: salon paket adisyonlarina ait mevcut tahsilatlari + taksit/vade/alacak SIL
        // Boylece payments dump'i tekrar import edilince hep ayni sonuc verir.
        $pkgRows = \DB::table('adisyonlar')->where('salon_id', $salonId)
            ->where('notlar', 'LIKE', '%[salonappy-pkgsale:%')
            ->get(['id', 'user_id', 'tarih', 'notlar']);
        $this->line('Paket adisyonlari bulundu: ' . count($pkgRows));
        if ($pkgRows->isEmpty()) {
            $this->error('Paket adisyon yok — once --only-package-sales calistir.');
            return 1;
        }

        $pkgIds = $pkgRows->pluck('id')->all();
        $eskiTahIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $pkgIds)->pluck('id')->all();
        if (!empty($eskiTahIds)) {
            \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
            \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
            \DB::table('tahsilatlar')->whereIn('id', $eskiTahIds)->delete();
        }
        // NOT: taksitli_tahsilatlar + taksit_vadeleri + alacaklar Pass1'de remaining_payment'a gore
        // zaten yazildi. Pass2 yalniz tahsilat eklemekle ilgilenir; alacak'a dokunmaz.
        $this->line("Temizlendi: tahsilat=" . count($eskiTahIds));

        // 2) pkgIndex re-build — client_name + service_text dump'tan, adId+userId DB'den
        // (DB users.name'i Turkce karakter/format farkliligi nedeniyle dump'taki ile birebir
        // eslesmeyebilir; dump otoriter kaynak.)
        $dumpPkgByGid = [];
        foreach ($j['packageSales'] ?? [] as $r) {
            $gid = (string) ($r['group_id'] ?? $r['id'] ?? '');
            if ($gid === '') continue;
            $dumpPkgByGid[$gid][] = $r;
        }

        $pkgIndex = [];
        foreach ($pkgRows as $row) {
            if (!preg_match('~\[salonappy-pkgsale:([^\]]+)\]~', (string) $row->notlar, $mm)) continue;
            $gid = $mm[1];
            $svcNorms = [];
            $cnFromDump = '';
            foreach ($dumpPkgByGid[$gid] ?? [] as $r) {
                if ($cnFromDump === '') $cnFromDump = (string) ($r['client_name'] ?? '');
                $items = (!empty($r['is_group']) && !empty($r['group_items']) && is_array($r['group_items']))
                    ? $r['group_items'] : [$r];
                foreach ($items as $it) {
                    $sv = trim((string) ($it['service_text'] ?? ''));
                    if ($sv !== '') $svcNorms[] = $normSvc($sv);
                }
            }
            $totalTutar = 0.0;
            foreach (\DB::table('adisyon_hizmetler')->where('adisyon_id', $row->id)->get(['fiyat']) as $ah) {
                $totalTutar += (float) $ah->fiyat;
            }
            $pkgIndex[$gid] = [
                'adId' => (int) $row->id,
                'userId' => (int) $row->user_id,
                'date' => (string) $row->tarih,
                'cnLower' => $normName($cnFromDump),
                'svcNorms' => array_values(array_unique($svcNorms)),
                'total' => round($totalTutar, 2),
                'paid' => 0.0,
            ];
        }
        $this->line('pkgIndex re-build: ' . count($pkgIndex));

        // 3) PASS2: payments tara, source="Paket satisi" olanlari ata
        $pkgByClient = [];
        foreach ($pkgIndex as $gid => $m) {
            $pkgByClient[$m['cnLower']][] = $gid;
        }
        $pAtanan = 0; $pAtlanan = 0; $pHata = 0;
        $gTah = 0; $gTaksit = 0;
        foreach ($payments as $p) {
            $src = trim((string) ($p['source_text'] ?? ''));
            if (mb_strtolower($src, 'UTF-8') !== mb_strtolower('Paket satışı', 'UTF-8')) continue;
            $pid = (string) ($p['id'] ?? '');
            if (!$pid) { $pAtlanan++; continue; }
            $cnL = $normName($p['client_name'] ?? '');
            $pDate = trim((string) ($p['date'] ?? ''));
            $amount = (float) ($p['amount'] ?? 0);
            if ($amount <= 0) { $pAtlanan++; continue; }
            $svcList = array_filter(array_map(function ($s) use ($normSvc) {
                return $normSvc($s);
            }, explode(',', (string)($p['services'] ?? ''))));
            if (empty($pkgByClient[$cnL])) { $pAtlanan++; continue; }

            // Match onceligi (paket secimi):
            //   1) EXACT match: payment.svc paket.svcNorms icinde in_array (tam isim)
            //   2) SUBSTRING match: birinin digerini icermesi
            //   3) FALLBACK: musterinin tum paketleri (Salonappy services tutarsizligi)
            // NOT: 'kalan > 0' veya 'pDate >= paket.date' filtreleri YOK
            //  - tamamen odenmis paketler de gecmis tahsilat alabilir (Gulcan Bikini)
            //  - kaparo / ileri tarihli kayit (Yildiz Acar payment=2025-05-01 paket date=2025-05-02)
            // Cakismada en eski paket secilir.
            // Dilzerin ornek: payment 'kas alma' -> exact 'Kas Alma' paketi 'Olculu Kas Alma' (substring) yerine secilir.
            // 3 sinif aday: exact / substring / fallback (musterinin tum paketleri).
            $exactMatch = [];
            $substrMatch = [];
            foreach ($pkgByClient[$cnL] as $gid) {
                $m = $pkgIndex[$gid];
                $exact = false; $substr = false;
                foreach ($svcList as $sv) {
                    if (in_array($sv, $m['svcNorms'], true)) { $exact = true; break; }
                    foreach ($m['svcNorms'] as $ms) {
                        if ($sv && $ms && (strpos($ms, $sv) !== false || strpos($sv, $ms) !== false)) {
                            $substr = true;
                        }
                    }
                }
                if ($exact)      $exactMatch[$gid]  = $m['date'];
                elseif ($substr) $substrMatch[$gid] = $m['date'];
            }
            $fallbackMatch = [];
            foreach ($pkgByClient[$cnL] as $gid) {
                $fallbackMatch[$gid] = $pkgIndex[$gid]['date'];
            }
            // FIT secimi: paket.kalan (total - paid_so_far) >= amount -> UI'da eksi gozukmez.
            // Sinif onceligi: exactFit -> substrFit -> fallbackFit. Her sinifta payment.date'e en YAKIN.
            // Hicbir sinifta FIT yoksa ATLA (kullanici: adisyonda kalan alacak eksi gozukmemeli).
            $payTs = $pDate ? strtotime($pDate) : 0;
            $pickFit = function (array $cand) use ($pkgIndex, $amount, $payTs) {
                $fit = [];
                foreach ($cand as $gid => $pkgDate) {
                    $kalan = $pkgIndex[$gid]['total'] - $pkgIndex[$gid]['paid'];
                    if ($kalan + 0.01 < $amount) continue;
                    $diff = $payTs && $pkgDate ? abs($payTs - strtotime($pkgDate)) : PHP_INT_MAX;
                    $fit[$gid] = $diff;
                }
                if (empty($fit)) return null;
                asort($fit);
                return array_key_first($fit);
            };
            $gid = $pickFit($exactMatch) ?: $pickFit($substrMatch) ?: $pickFit($fallbackMatch);
            if (!$gid) {
                // Salonappy tarafinda overpay (Gulcan Bikini 2000+1000 = 3000 > 2000 total).
                // Atla — paket adisyon eksi gozukmesin. Toplam tahsilat -1000 kaybeder.
                $pAtlanan++;
                \Log::info('[Salonappy pay-skip-overfit]', ['pid' => $pid, 'amount' => $amount, 'client' => $cnL]);
                continue;
            }
            $m = $pkgIndex[$gid];

            $payMarker = '[salonappy-pay:' . $pid . ']';
            try {
                $yontemId = $yontemMap($p['payment_method_text'] ?? '');
                $tahId = \DB::table('tahsilatlar')->insertGetId([
                    'salon_id' => $salonId, 'user_id' => $m['userId'], 'adisyon_id' => $m['adId'],
                    'odeme_tarihi' => $pDate ?: $m['date'], 'tutar' => $amount,
                    'odeme_yontemi_id' => $yontemId, 'notlar' => $payMarker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $ahsBu = \DB::table('adisyon_hizmetler')->where('adisyon_id', $m['adId'])->get(['id', 'fiyat']);
                $tFiyat = 0.0;
                foreach ($ahsBu as $a) $tFiyat += (float) $a->fiyat;
                $paylar = []; $payToplam = 0;
                $n = $ahsBu->count();
                if ($tFiyat > 0) {
                    // Fiyat orantili dagit
                    $oran = $amount / $tFiyat;
                    foreach ($ahsBu as $a) {
                        $py = round((float)$a->fiyat * $oran, 2);
                        $paylar[(int)$a->id] = $py;
                        $payToplam += $py;
                    }
                } elseif ($n > 0) {
                    // Salonappy paket fiyat=0 ise (87 paket) esit dagit; UI 'Satis Takibi'
                    // odenenTutar = SUM(tahsilat_hizmetler.tutar), TH bos kalirsa eksik gosterir.
                    $per = round($amount / $n, 2);
                    foreach ($ahsBu as $a) {
                        $paylar[(int)$a->id] = $per;
                        $payToplam += $per;
                    }
                }
                $fark = round($amount - $payToplam, 2);
                if (abs($fark) > 0.001 && !empty($paylar)) {
                    end($paylar); $sk = key($paylar);
                    $paylar[$sk] = round($paylar[$sk] + $fark, 2);
                }
                foreach ($paylar as $ahKey => $py) {
                    if ($py <= 0) continue;
                    \DB::table('tahsilat_hizmetler')->insert([
                        'tahsilat_id' => $tahId, 'adisyon_hizmet_id' => $ahKey, 'tutar' => $py,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $pkgIndex[$gid]['paid'] = round($m['paid'] + $amount, 2);
                $gTah++; $pAtanan++;
            } catch (\Throwable $e) {
                $pHata++;
                \Log::warning('[Salonappy pay-match] hata', ['pid' => $pid, 'err' => $e->getMessage()]);
            }
        }
        $this->info("Package payments: tahsilat=$gTah, atanan=$pAtanan, atlanan=$pAtlanan, hata=$pHata");
        return 0;
    }

    /**
     * --only-product-sales: dump productSales[]'i adisyon+adisyon_urunler+alacak olarak yaz.
     * Tahsilat YOK — Pass2 (--only-product-payments) ile payments[] eslestirilir.
     * Marker: [salonappy-prodsale:<id>]. UPSERT: ayni markerli adisyon varsa silinip yeniden yazilir.
     */
    private function importProductSalesOnly($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $sales = $j['productSales'] ?? [];
        if (empty($sales)) { $this->warn('productSales[] bos veya yok'); return 0; }

        // idMap (clients phone -> users.id)
        $idMap = [];
        foreach (($j['clients'] ?? []) as $c) {
            $sid = (string) ($c['id'] ?? '');
            if (!$sid) continue;
            $phone = preg_replace('~\D~', '', (string) ($c['phone_number_local'] ?? $c['phone_number'] ?? ''));
            if ($phone) {
                $uid = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                if ($uid) $idMap[$sid] = $uid;
            }
        }
        $this->line('idMap kuruldu: ' . count($idMap) . ' musteri eslesti');
        $this->line('Urun satislari: ' . count($sales));

        $gEklenen = 0; $gDedup = 0; $gHata = 0; $gAlacak = 0;
        foreach ($sales as $r) {
            $saleId = (string) ($r['id'] ?? '');
            if (!$saleId) { $gHata++; continue; }
            $marker = '[salonappy-prodsale:' . $saleId . ']';

            // UPSERT: mevcut markerli adisyon -> sil
            $eskiAdIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%' . $marker . '%')->pluck('id')->all();
            if (!empty($eskiAdIds)) {
                $eskiTahIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                if (!empty($eskiTahIds)) {
                    \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
                    \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
                    \DB::table('tahsilatlar')->whereIn('id', $eskiTahIds)->delete();
                }
                $eskiTtIds = \DB::table('taksitli_tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                if (!empty($eskiTtIds)) {
                    \DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $eskiTtIds)->delete();
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    }
                    \DB::table('taksitli_tahsilatlar')->whereIn('id', $eskiTtIds)->delete();
                }
                \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                \DB::table('adisyon_urunler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                \DB::table('adisyonlar')->whereIn('id', $eskiAdIds)->delete();
                $gDedup++;
            }

            // Musteri eslestir
            $clientId = (string) ($r['client_id'] ?? '');
            $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
            if (!$userId) {
                $phone = preg_replace('~\D~', '', (string) ($r['client_phone_number_local'] ?? $r['client_full_phone_number'] ?? ''));
                if ($phone) $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
            }
            if (!$userId) {
                $clientName = trim((string) ($r['client_name'] ?? ''));
                if ($clientName) $userId = \DB::table('users')->where('name', 'LIKE', $clientName)->orderByDesc('id')->value('id');
            }
            if (!$userId) { $gHata++; continue; }

            $tarih = $r['date'] ?? date('Y-m-d');
            $urunAdi = trim((string) ($r['product_text'] ?? ''));
            if ($urunAdi === '') { $gHata++; continue; }
            $adet = max(1, (int) ($r['quantity'] ?? 1));
            $toplam = (float) ($r['total_amount'] ?? 0);
            $ppRaw = (float) ($r['product_price'] ?? 0);
            // AU.fiyat = SATIS TOPLAMI (paket akisiyla simetrik). UI SUM(fiyat) hesabi
            // birim*adet round farkindan kurus kaybi yaratiyordu (toplamda kusurat).
            $fiyat = $toplam > 0 ? $toplam : ($ppRaw * $adet);

            $urunId = $this->ensureUrun($salonId, $urunAdi, $fiyat, $urunAdi);
            if (!$urunId) { $gHata++; continue; }

            $sellerAdi = trim((string) ($r['seller_text'] ?? $r['created_by'] ?? ''));
            $persId = null;
            if ($sellerAdi !== '') {
                $this->ensurePersonel($salonId, $sellerAdi);
                $persId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                    ->where('personel_adi', $sellerAdi)->value('id');
            }

            try {
                $adId = \DB::table('adisyonlar')->insertGetId([
                    'salon_id' => $salonId, 'user_id' => $userId, 'tarih' => $tarih,
                    'olusturan_id' => $persId, 'notlar' => $marker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
                \DB::table('adisyon_urunler')->insert([
                    'adisyon_id' => $adId, 'urun_id' => $urunId,
                    'fiyat' => $fiyat,
                    'adet' => $adet,
                    'personel_id' => $persId,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Alacak: paket akisindaki ile ayni mantik.
                $rec  = (float) ($r['receivable_amount'] ?? 0);
                $rem  = (float) ($r['remaining_payment'] ?? 0);
                $paid = (float) ($r['paid_amount'] ?? 0);
                $grpAlacak = 0.0;
                if ($rec > 0.01) {
                    $grpAlacak = $rec;
                } elseif ($rem > 0.01 && $paid > 0.01) {
                    $grpAlacak = $rem;
                }
                if ($grpAlacak > 0.01) {
                    // Vade = paket akisindaki gibi created_at gun kismi
                    $cAt = trim((string) ($r['created_at'] ?? ''));
                    $vadeTarih = $cAt !== '' ? substr($cAt, 0, 10) : $tarih;
                    $tutar = round($grpAlacak, 2);
                    $tt = \DB::table('taksitli_tahsilatlar')->insertGetId([
                        'user_id' => $userId, 'adisyon_id' => $adId,
                        'salon_id' => $salonId, 'vade_sayisi' => 1,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $vadeId = \DB::table('taksit_vadeleri')->insertGetId([
                        'taksitli_tahsilat_id' => $tt, 'odendi' => 0,
                        'vade_tarih' => $vadeTarih, 'tutar' => $tutar,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->insert([
                            'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => $adId,
                            'tutar' => $tutar, 'taksit_vade_id' => $vadeId,
                            'planlanan_odeme_tarihi' => $vadeTarih,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $gAlacak++;
                }
                $gEklenen++;
            } catch (\Throwable $e) {
                $gHata++;
                \Log::warning('[Salonappy prodsale-only] hata', ['id' => $saleId, 'err' => $e->getMessage()]);
            }
        }

        $this->info("Product sales (PASS1 - adisyon+urunler+alacak, tahsilat YOK): eklenen=$gEklenen, dedup=$gDedup, hata=$gHata, alacak=$gAlacak");
        $this->line('Bir sonraki adim: --only-product-payments ile tahsilatlar.');
        return 0;
    }

    /**
     * PASS2: dump payments[] -> source_text="Urun satisi" olanlari [salonappy-prodsale:%] markerli
     * adisyonlara eslestir. Match: client_name + product overlap + en yakin tarih + FIT (eksi gozukmesin).
     * Tahsilat insert + tahsilat_urunler dagit.
     */
    private function importProductPaymentsOnly($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $payments = $j['payments'] ?? [];
        if (empty($payments)) { $this->warn('payments[] bos.'); return 0; }

        $yontemMap = function ($txt) {
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if ($t === '') return 1;
            if (strpos($t, 'kredi') !== false || strpos($t, 'kart') !== false || strpos($t, 'pos') !== false) return 2;
            if (strpos($t, 'havale') !== false || strpos($t, 'eft') !== false || strpos($t, 'banka') !== false) return 3;
            if (strpos($t, 'nakit') !== false) return 1;
            return 4;
        };
        $normName = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            return preg_replace('~\s+~', ' ', $s);
        };
        $normSvc = function ($s) {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $s = str_replace(['(', ')', '.', ','], ' ', $s);
            return trim(preg_replace('~\s+~', ' ', $s));
        };

        // 1) Mevcut urun adisyonlarina bagli tahsilatlari sil (UPSERT)
        $prodRows = \DB::table('adisyonlar')->where('salon_id', $salonId)
            ->where('notlar', 'LIKE', '%[salonappy-prodsale:%')
            ->get(['id', 'user_id', 'tarih', 'notlar']);
        $this->line('Urun adisyonlari bulundu: ' . count($prodRows));
        if ($prodRows->isEmpty()) {
            $this->error('Urun adisyon yok — once --only-product-sales calistir.');
            return 1;
        }
        $prodIds = $prodRows->pluck('id')->all();
        $eskiTahIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $prodIds)->pluck('id')->all();
        if (!empty($eskiTahIds)) {
            \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
            \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $eskiTahIds)->delete();
            \DB::table('tahsilatlar')->whereIn('id', $eskiTahIds)->delete();
        }
        $this->line("Temizlendi: tahsilat=" . count($eskiTahIds));

        // 2) prodIndex re-build — client_name + product_text dump'tan otoriter
        $dumpSalesById = [];
        foreach ($j['productSales'] ?? [] as $r) {
            $sid = (string) ($r['id'] ?? '');
            if ($sid === '') continue;
            $dumpSalesById[$sid] = $r;
        }
        $prodIndex = [];
        foreach ($prodRows as $row) {
            if (!preg_match('~\[salonappy-prodsale:([^\]]+)\]~', (string) $row->notlar, $mm)) continue;
            $sid = $mm[1];
            $dump = $dumpSalesById[$sid] ?? null;
            $svcNorms = [];
            $cnFromDump = '';
            if ($dump) {
                $cnFromDump = (string) ($dump['client_name'] ?? '');
                $pn = trim((string) ($dump['product_text'] ?? ''));
                if ($pn !== '') $svcNorms[] = $normSvc($pn);
            }
            $totalTutar = 0.0;
            foreach (\DB::table('adisyon_urunler')->where('adisyon_id', $row->id)->get(['fiyat', 'adet']) as $au) {
                $totalTutar += (float) $au->fiyat * max(1, (int) $au->adet);
            }
            $prodIndex[$sid] = [
                'adId' => (int) $row->id,
                'userId' => (int) $row->user_id,
                'date' => (string) $row->tarih,
                'cnLower' => $normName($cnFromDump),
                'svcNorms' => array_values(array_unique($svcNorms)),
                'total' => round($totalTutar, 2),
                'paid' => 0.0,
            ];
        }
        $this->line('prodIndex re-build: ' . count($prodIndex));

        // 3) Musteri bazli prodByClient
        $prodByClient = [];
        foreach ($prodIndex as $sid => $m) {
            $prodByClient[$m['cnLower']][] = $sid;
        }

        // 4) Payments tara (source="Urun satisi")
        $pAtanan = 0; $pAtlanan = 0; $pHata = 0; $gTah = 0;
        foreach ($payments as $p) {
            $src = trim((string) ($p['source_text'] ?? ''));
            if (mb_strtolower($src, 'UTF-8') !== mb_strtolower('Ürün satışı', 'UTF-8')) continue;
            $pid = (string) ($p['id'] ?? '');
            if (!$pid) { $pAtlanan++; continue; }
            $cnL = $normName($p['client_name'] ?? '');
            $pDate = trim((string) ($p['date'] ?? ''));
            $amount = (float) ($p['amount'] ?? 0);
            if ($amount <= 0) { $pAtlanan++; continue; }
            $svcList = array_filter(array_map(function ($s) use ($normSvc) { return $normSvc($s); },
                explode(',', (string)($p['services'] ?? ''))));
            if (empty($prodByClient[$cnL])) { $pAtlanan++; continue; }

            // 3 sinif aday: exact / substring / fallback
            $exactMatch = [];
            $substrMatch = [];
            foreach ($prodByClient[$cnL] as $sid) {
                $m = $prodIndex[$sid];
                $exact = false; $substr = false;
                foreach ($svcList as $sv) {
                    if (in_array($sv, $m['svcNorms'], true)) { $exact = true; break; }
                    foreach ($m['svcNorms'] as $ms) {
                        if ($sv && $ms && (strpos($ms, $sv) !== false || strpos($sv, $ms) !== false)) {
                            $substr = true;
                        }
                    }
                }
                if ($exact)      $exactMatch[$sid]  = $m['date'];
                elseif ($substr) $substrMatch[$sid] = $m['date'];
            }
            $fallbackMatch = [];
            foreach ($prodByClient[$cnL] as $sid) {
                $fallbackMatch[$sid] = $prodIndex[$sid]['date'];
            }

            // FIT (paket akisi ile ayni)
            $payTs = $pDate ? strtotime($pDate) : 0;
            $pickFit = function (array $cand) use ($prodIndex, $amount, $payTs) {
                $fit = [];
                foreach ($cand as $sid => $pDt) {
                    $kalan = $prodIndex[$sid]['total'] - $prodIndex[$sid]['paid'];
                    if ($kalan + 0.01 < $amount) continue;
                    $diff = $payTs && $pDt ? abs($payTs - strtotime($pDt)) : PHP_INT_MAX;
                    $fit[$sid] = $diff;
                }
                if (empty($fit)) return null;
                asort($fit);
                return array_key_first($fit);
            };
            $sid = $pickFit($exactMatch) ?: $pickFit($substrMatch) ?: $pickFit($fallbackMatch);
            if (!$sid) {
                $pAtlanan++;
                \Log::info('[Salonappy prod-pay-skip-overfit]', ['pid' => $pid, 'amount' => $amount, 'client' => $cnL]);
                continue;
            }
            $m = $prodIndex[$sid];

            $payMarker = '[salonappy-prod-pay:' . $pid . ']';
            try {
                $yontemId = $yontemMap($p['payment_method_text'] ?? '');
                $tahId = \DB::table('tahsilatlar')->insertGetId([
                    'salon_id' => $salonId, 'user_id' => $m['userId'], 'adisyon_id' => $m['adId'],
                    'odeme_tarihi' => $pDate ?: $m['date'], 'tutar' => $amount,
                    'odeme_yontemi_id' => $yontemId, 'notlar' => $payMarker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
                // tahsilat_urunler dagit (fiyat*adet orantili; tFiyat=0 ise esit)
                $aus = \DB::table('adisyon_urunler')->where('adisyon_id', $m['adId'])->get(['id', 'fiyat', 'adet']);
                $tFiyat = 0.0;
                foreach ($aus as $a) $tFiyat += (float) $a->fiyat * max(1, (int) $a->adet);
                $paylar = []; $payToplam = 0;
                $n = $aus->count();
                if ($tFiyat > 0) {
                    $oran = $amount / $tFiyat;
                    foreach ($aus as $a) {
                        $py = round((float) $a->fiyat * max(1, (int) $a->adet) * $oran, 2);
                        $paylar[(int) $a->id] = $py;
                        $payToplam += $py;
                    }
                } elseif ($n > 0) {
                    $per = round($amount / $n, 2);
                    foreach ($aus as $a) {
                        $paylar[(int) $a->id] = $per;
                        $payToplam += $per;
                    }
                }
                $fark = round($amount - $payToplam, 2);
                if (abs($fark) > 0.001 && !empty($paylar)) {
                    end($paylar); $sk = key($paylar);
                    $paylar[$sk] = round($paylar[$sk] + $fark, 2);
                }
                foreach ($paylar as $auKey => $py) {
                    if ($py <= 0) continue;
                    \DB::table('tahsilat_urunler')->insert([
                        'tahsilat_id' => $tahId, 'adisyon_urun_id' => $auKey, 'tutar' => $py,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $prodIndex[$sid]['paid'] = round($m['paid'] + $amount, 2);
                $gTah++; $pAtanan++;
            } catch (\Throwable $e) {
                $pHata++;
                \Log::warning('[Salonappy prod-pay-match] hata', ['pid' => $pid, 'err' => $e->getMessage()]);
            }
        }
        $this->info("Product payments: tahsilat=$gTah, atanan=$pAtanan, atlanan=$pAtlanan, hata=$pHata");
        return 0;
    }

    /**
     * --only-expenses: dump expenses[] -> masraflar tablosuna idempotent insert.
     * UPSERT: mevcut [salonappy-expense:id] markerli kayitlar silinir + yeniden yazilir.
     * masraf_kategorisi auto-create. harcayan personel name match.
     */
    private function importExpensesOnly($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $exps = $j['expenses'] ?? [];
        if (empty($exps)) { $this->warn('expenses[] bos.'); return 0; }

        $masTable = (new \App\Masraflar)->getTable();
        $hasNotlar    = \Schema::hasColumn($masTable, 'notlar');
        $hasAciklama  = \Schema::hasColumn($masTable, 'aciklama');
        $hasKategori  = \Schema::hasColumn($masTable, 'masraf_kategori_id');
        $hasHarcayan  = \Schema::hasColumn($masTable, 'harcayan_id');

        // UPSERT temizlik: salon icin tum salonappy-expense markerlilari sil
        $silinen = 0;
        if ($hasNotlar) {
            $silinen = \DB::table($masTable)->where('salon_id', $salonId)
                ->where('notlar', 'LIKE', '%[salonappy-expense:%')->delete();
        }
        $this->line("Mevcut salonappy gider silindi: $silinen. Dump'tan yeniden yazilacak: " . count($exps));

        // Tablo: masraf_kategorileri (MasrafKategorisi model). Kolon: kategori (sade).
        $katTable = 'masraf_kategorileri';
        $hasOdemeYontem = \Schema::hasColumn($masTable, 'odeme_yontemi_id');
        $yontemMap = function ($txt) {
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if ($t === '') return 1;
            if (strpos($t, 'kredi') !== false || strpos($t, 'kart') !== false || strpos($t, 'pos') !== false) return 2;
            if (strpos($t, 'havale') !== false || strpos($t, 'eft') !== false || strpos($t, 'banka') !== false) return 3;
            if (strpos($t, 'nakit') !== false) return 1;
            return 4;
        };

        $eEklenen = 0; $eHata = 0;
        foreach ($exps as $ex) {
            $exId = (string) ($ex['id'] ?? '');
            if (!$exId) { $eHata++; continue; }
            $exMarker = '[salonappy-expense:' . $exId . ']';
            $tarih = $ex['date'] ?? date('Y-m-d');
            $tutar = (float) ($ex['amount'] ?? 0);
            $aciklama = trim((string) ($ex['description_raw'] ?? $ex['description'] ?? ''));
            $kategoriAd = trim((string) ($ex['category_text'] ?? ''));
            $harcayanAd = trim((string) ($ex['created_by_name'] ?? $ex['created_by'] ?? ''));
            $odemeYontemAd = (string) ($ex['payment_method_text'] ?? $ex['payment_method'] ?? '');

            try {
                // MasrafKategorisi::firstOrCreate(['kategori' => $ad]) sistem mantigi —
                // tablo global (salon_id YOK). StoreAdminController line 28415 ile ayni.
                $kategoriId = null;
                if ($kategoriAd && $hasKategori) {
                    $kategoriId = \DB::table($katTable)
                        ->where('kategori', $kategoriAd)->value('id');
                    if (!$kategoriId) {
                        $kategoriId = \DB::table($katTable)->insertGetId([
                            'kategori' => $kategoriAd,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
                $harcayanId = null;
                if ($harcayanAd && $hasHarcayan) {
                    $harcayanId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                        ->where('personel_adi', 'LIKE', '%' . $harcayanAd . '%')->value('id');
                }
                $row = [
                    'salon_id' => $salonId, 'tarih' => $tarih, 'tutar' => $tutar,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($hasAciklama) $row['aciklama'] = $aciklama;
                if ($kategoriId) $row['masraf_kategori_id'] = $kategoriId;
                if ($harcayanId) $row['harcayan_id'] = $harcayanId;
                if ($hasOdemeYontem) $row['odeme_yontemi_id'] = $yontemMap($odemeYontemAd);
                if ($hasNotlar) $row['notlar'] = $exMarker;
                \DB::table($masTable)->insert($row);
                $eEklenen++;
            } catch (\Throwable $e) {
                $eHata++;
                \Log::warning('[Salonappy expense] hata', ['id' => $exId, 'err' => $e->getMessage()]);
            }
        }
        $this->info("Expenses: eklenen=$eEklenen, hata=$eHata, silinen=$silinen");
        return 0;
    }

    /**
     * --reset-visits --from --to:
     * Tarih araligindaki [salonappy-visit:<session>] markerli kayitlari sil.
     * randevu+adisyon+tahsilat+taksit+alacak. Katalog dokunulmaz.
     * Ayni aralikta ayni session UPSERT icin de kullanilir.
     */
    private function resetVisits($salonId, $from, $to, $dryRun)
    {
        $like = '%[salonappy-visit:%';
        $aIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
            ->whereBetween('tarih', [$from, $to])
            ->where('notlar', 'LIKE', $like)->pluck('id')->all();
        $rIds = \DB::table('randevular')->where('salon_id', $salonId)
            ->whereBetween('tarih', [$from, $to])
            ->where('personel_notu', 'LIKE', $like)->pluck('id')->all();
        $this->line("Visit reset {$from} .. {$to}: randevu=" . count($rIds) . " adisyon=" . count($aIds));
        if ($dryRun) { $this->warn('DRY-RUN'); return 0; }

        if (!empty($aIds)) {
            $tIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $aIds)->pluck('id')->all();
            if (!empty($tIds)) {
                \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $tIds)->delete();
                \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $tIds)->delete();
                \DB::table('tahsilatlar')->whereIn('id', $tIds)->delete();
            }
            $ttIds = \DB::table('taksitli_tahsilatlar')->whereIn('adisyon_id', $aIds)->pluck('id')->all();
            if (!empty($ttIds)) {
                \DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $ttIds)->delete();
                if (\Schema::hasTable('alacaklar')) {
                    \DB::table('alacaklar')->whereIn('adisyon_id', $aIds)->delete();
                }
                \DB::table('taksitli_tahsilatlar')->whereIn('id', $ttIds)->delete();
            }
            \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $aIds)->delete();
            \DB::table('adisyon_urunler')->whereIn('adisyon_id', $aIds)->delete();
            \DB::table('adisyonlar')->whereIn('id', $aIds)->delete();
        }
        if (!empty($rIds)) {
            \DB::table('randevu_hizmetler')->whereIn('randevu_id', $rIds)->delete();
            \DB::table('randevular')->whereIn('id', $rIds)->delete();
        }
        $this->info('Visit reset tamam.');
        return 0;
    }

    /**
     * --only-visits --from --to:
     * Dump visit + bookingDetails listesinden tarih araligindaki kayitlari isle.
     * Her visit icin:
     *   1) UPSERT (mevcut marker silinir)
     *   2) Adisyon + AH (services)
     *   3) Randevu + randevu_hizmetler (showup map)
     *   4) Urun tasima: visit.product_sales[] -> ayni tarih+product_text+adet+tutar ile mevcut
     *      [salonappy-prodsale:%] adisyonu varsa onun AU+tahsilat+alacak'larini visit adisyonuna tasi,
     *      eski adisyonu sil. Yoksa AU dogrudan visit adisyonuna eklenir.
     *   5) Paket usage: package_usages[] -> bu musterinin [salonappy-pkgsale:%] adisyonlarinda
     *      ayni service_id'li AH'in APS placeholder'larindan ilk geldi=0 olani geldi=1+tarih.
     *   6) Tahsilat: payments[] -> tahsilatlar+TH dagit, marker [salonappy-visit-pay:<pid>].
     *   7) Alacak: unpaid_amount > 0 -> taksitli_tahsilatlar+vadeler+alacaklar (vade=tarih).
     */
    private function importVisitsOnly($file, $salonId, $from, $to, $withProductsOnly = false)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $bdMap = $j['bookingDetails'] ?? [];
        if (empty($bdMap)) { $this->warn('bookingDetails[] bos.'); return 0; }

        // idMap (clients phone -> users.id)
        $idMap = [];
        foreach (($j['clients'] ?? []) as $c) {
            $sid = (string) ($c['id'] ?? '');
            if (!$sid) continue;
            $phone = preg_replace('~\D~', '', (string) ($c['phone_number_local'] ?? $c['phone_number'] ?? ''));
            if ($phone) {
                $uid = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                if ($uid) $idMap[$sid] = $uid;
            }
        }

        $yontemMap = function ($txt) {
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if ($t === '') return 1;
            if (strpos($t, 'kredi') !== false || strpos($t, 'kart') !== false || strpos($t, 'pos') !== false) return 2;
            if (strpos($t, 'havale') !== false || strpos($t, 'eft') !== false || strpos($t, 'banka') !== false) return 3;
            if (strpos($t, 'nakit') !== false) return 1;
            return 4;
        };

        // randevular sema: durum int (1=Onayli, 2=Iptal), randevuya_geldi bool
        // Showup map -> [durum, randevuya_geldi]
        $showupMap = function ($txt, $isCancelled) {
            if ($isCancelled) return [2, null];
            $t = mb_strtolower(trim((string)$txt), 'UTF-8');
            if (strpos($t, 'gelmedi') !== false || strpos($t, 'no show') !== false) return [1, 0];
            if (strpos($t, 'geldi') !== false || strpos($t, 'showed') !== false) return [1, 1];
            return [1, null]; // bekleniyor
        };

        $gVisit = 0; $gAdisyon = 0; $gRand = 0; $gAH = 0; $gAU = 0;
        $gUrunTasinan = 0; $gPaketUsage = 0; $gTah = 0; $gAlacak = 0; $gHata = 0; $gAtlanan = 0;

        // Tarih filtresi: bookingDetails uzerinden details.date kullan
        // --with-products ek filtre: sadece product_sales[] dolu olan visit'ler
        $kapsamSession = [];
        foreach ($bdMap as $sid => $bd) {
            $tarih = trim((string) ($bd['details']['date'] ?? ''));
            if ($tarih < $from || $tarih > $to) continue;
            if ($withProductsOnly && empty($bd['product_sales'])) continue;
            $kapsamSession[] = (string) $sid;
        }
        $extra = $withProductsOnly ? ' (sadece product_sales dolu)' : '';
        $this->line("Visit aktarim {$from} .. {$to}{$extra}: kapsamda " . count($kapsamSession) . ' visit');

        // Paket APS reset: bu aralikta yazilan APS'ler + eski geldi=0 placeholder'lar silinir.
        // package_usages her visit'te yeniden insert edilecek (drklinik mantigi).
        $pkgAhIds = \DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
            ->where('a.salon_id', $salonId)
            ->where('a.notlar', 'LIKE', '%[salonappy-pkgsale:%')
            ->pluck('ah.id')->all();
        if (!empty($pkgAhIds)) {
            $silinen1 = \DB::table('adisyon_paket_seanslar')
                ->whereIn('adisyon_hizmet_id', $pkgAhIds)
                ->whereBetween('seans_tarih', [$from, $to])
                ->delete();
            $silinen2 = \DB::table('adisyon_paket_seanslar')
                ->whereIn('adisyon_hizmet_id', $pkgAhIds)
                ->where('geldi', 0)
                ->delete();
            $this->line("Paket APS reset: aralik={$silinen1} eski-placeholder={$silinen2}");
        }

        foreach ($kapsamSession as $sid) {
            $bd = $bdMap[$sid];
            $d  = $bd['details'] ?? [];
            $marker = '[salonappy-visit:' . $sid . ']';

            try {
                // UPSERT — bu session'in mevcut markerli randevu+adisyon silinir (resetVisits ile ayni mantik)
                $eskiAdIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
                    ->where('notlar', 'LIKE', '%' . $marker . '%')->pluck('id')->all();
                if (!empty($eskiAdIds)) {
                    $tIds = \DB::table('tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($tIds)) {
                        \DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $tIds)->delete();
                        \DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $tIds)->delete();
                        \DB::table('tahsilatlar')->whereIn('id', $tIds)->delete();
                    }
                    $ttIds = \DB::table('taksitli_tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($ttIds)) {
                        \DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $ttIds)->delete();
                        if (\Schema::hasTable('alacaklar')) {
                            \DB::table('alacaklar')->whereIn('adisyon_id', $eskiAdIds)->delete();
                        }
                        \DB::table('taksitli_tahsilatlar')->whereIn('id', $ttIds)->delete();
                    }
                    $ahIds = \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($ahIds)) {
                        \DB::table('adisyon_paket_seanslar')->whereIn('adisyon_hizmet_id', $ahIds)->delete();
                    }
                    \DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    \DB::table('adisyon_urunler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    \DB::table('adisyonlar')->whereIn('id', $eskiAdIds)->delete();
                }
                $eskiRIds = \DB::table('randevular')->where('salon_id', $salonId)
                    ->where('personel_notu', 'LIKE', '%' . $marker . '%')->pluck('id')->all();
                if (!empty($eskiRIds)) {
                    \DB::table('randevu_hizmetler')->whereIn('randevu_id', $eskiRIds)->delete();
                    \DB::table('randevular')->whereIn('id', $eskiRIds)->delete();
                }

                // Musteri resolve
                $clientId = (string) ($d['client_id'] ?? '');
                $userId = $clientId ? ($idMap[$clientId] ?? null) : null;
                if (!$userId) {
                    $phone = preg_replace('~\D~', '', (string) ($d['client_phone_number'] ?? ''));
                    if ($phone) $userId = \DB::table('users')->where('cep_telefon', $phone)->value('id');
                }
                if (!$userId) {
                    $clientName = trim((string) ($d['client_name'] ?? ''));
                    if ($clientName) $userId = \DB::table('users')->where('name', 'LIKE', $clientName)->orderByDesc('id')->value('id');
                }
                if (!$userId) { $gHata++; continue; }

                $tarih = $d['date'] ?? date('Y-m-d');
                $saat  = trim((string) ($d['time_text_24'] ?? $d['time_text'] ?? '00:00')) . ':00';
                [$rDurum, $rGeldi] = $showupMap($d['showup_text'] ?? '', !empty($d['is_cancelled']));
                $isPast = !empty($d['is_past']);

                // Upcoming (is_past=false) -> sadece randevu yaz, adisyon ve sonrakileri atla.
                if (!$isPast) {
                    $randevuRow = [
                        'salon_id' => $salonId, 'user_id' => $userId,
                        'tarih' => $tarih, 'saat' => $saat,
                        'durum' => $rDurum,
                        'personel_notu' => $marker,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if ($rGeldi !== null) $randevuRow['randevuya_geldi'] = $rGeldi;
                    $rId = \DB::table('randevular')->insertGetId($randevuRow);
                    $gRand++;
                    $cumSaat = $saat;
                    foreach (($bd['services'] ?? []) as $svc) {
                        $svcAd = trim((string) ($svc['service_text'] ?? ''));
                        if ($svcAd === '') continue;
                        $sureDk = max(15, (int) ($svc['duration'] ?? 30));
                        $hid = $this->ensureSalonHizmet($salonId, $svcAd, $sureDk, (float) ($svc['price'] ?? 0), $svcAd);
                        if (!$hid) continue;
                        $persId = null;
                        $staffAd = trim((string) ($svc['staff_name'] ?? ''));
                        if ($staffAd !== '') {
                            $this->ensurePersonel($salonId, $staffAd);
                            $persId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                                ->where('personel_adi', $staffAd)->value('id');
                        }
                        $saatBitis = date('H:i:s', strtotime($cumSaat) + $sureDk * 60);
                        \DB::table('randevu_hizmetler')->insert([
                            'randevu_id' => $rId, 'hizmet_id' => $hid,
                            'fiyat' => (float) ($svc['price'] ?? 0),
                            'personel_id' => $persId,
                            'sure_dk' => $sureDk,
                            'saat' => $cumSaat, 'saat_bitis' => $saatBitis,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $cumSaat = $saatBitis;
                    }
                    $gVisit++;
                    continue;
                }

                // Adisyon (is_past=true)
                $adId = \DB::table('adisyonlar')->insertGetId([
                    'salon_id' => $salonId, 'user_id' => $userId, 'tarih' => $tarih,
                    'notlar' => $marker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $gAdisyon++;

                // AH (services[])
                // Salonappy bazi visit'lerde service.price=0 doner ama bd.details.total_service_price
                // visit'in toplam hizmet tutarini gosterir. Tum service.price=0 + tot_svc_price>0 ise
                // tutari hizmet sayisina esit boluştur ki adisyon toplami dogru hesaplansin.
                $svcArr = $bd['services'] ?? [];
                $totalSvcPrice = (float) ($d['total_service_price'] ?? 0);
                $rawSvcPriceSum = 0.0;
                foreach ($svcArr as $svc) $rawSvcPriceSum += (float) ($svc['price'] ?? 0);
                $svcUseFallback = ($rawSvcPriceSum <= 0.01 && $totalSvcPrice > 0 && count($svcArr) > 0);
                $svcFallbackPer = $svcUseFallback ? round($totalSvcPrice / count($svcArr), 2) : null;
                $svcCount = count($svcArr);
                $svcAcc = 0.0;
                $svcIdx = -1;

                $svcIdToAh = []; // service_id -> [ah_id, ...] (paket usage match icin)
                foreach ($svcArr as $svc) {
                    $svcIdx++;
                    $svcAd = trim((string) ($svc['service_text'] ?? ''));
                    if ($svcAd === '') continue;
                    $rawPrice = (float) ($svc['price'] ?? 0);
                    if ($svcUseFallback) {
                        // Yuvarlama farki son AH'ye yansir; toplam = total_service_price tam
                        $fiyat = ($svcIdx === $svcCount - 1)
                            ? round($totalSvcPrice - $svcAcc, 2)
                            : $svcFallbackPer;
                        $svcAcc += $fiyat;
                    } else {
                        $fiyat = $rawPrice;
                    }
                    $sure  = max(15, (int) ($svc['duration'] ?? 30));
                    $hid = $this->ensureSalonHizmet($salonId, $svcAd, $sure, $fiyat, $svcAd);
                    if (!$hid) continue;
                    $persId = null;
                    $staffAd = trim((string) ($svc['staff_name'] ?? ''));
                    if ($staffAd !== '') {
                        $this->ensurePersonel($salonId, $staffAd);
                        $persId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                            ->where('personel_adi', $staffAd)->value('id');
                    }
                    $ahId = \DB::table('adisyon_hizmetler')->insertGetId([
                        'adisyon_id' => $adId, 'hizmet_id' => $hid,
                        'fiyat' => $fiyat, 'sure' => $sure,
                        'islem_tarihi' => $tarih, 'islem_saati' => '00:00:00',
                        'personel_id' => $persId,
                        'geldi' => ($rGeldi === 1) ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $gAH++;
                    $svcKey = (string) ($svc['service_id'] ?? '');
                    if ($svcKey !== '') $svcIdToAh[$svcKey][] = $ahId;
                }

                // Randevu + randevu_hizmetler
                $randevuRow = [
                    'salon_id' => $salonId, 'user_id' => $userId,
                    'tarih' => $tarih, 'saat' => $saat,
                    'durum' => $rDurum,
                    'adisyon_id' => $adId,
                    'personel_notu' => $marker,
                    'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                ];
                if ($rGeldi !== null) $randevuRow['randevuya_geldi'] = $rGeldi;
                $rId = \DB::table('randevular')->insertGetId($randevuRow);
                $gRand++;
                $cumSaat2 = $saat;
                foreach (($bd['services'] ?? []) as $svc) {
                    $svcAd = trim((string) ($svc['service_text'] ?? ''));
                    if ($svcAd === '') continue;
                    $sureDk = max(15, (int) ($svc['duration'] ?? 30));
                    $hid = $this->ensureSalonHizmet($salonId, $svcAd, $sureDk, (float) ($svc['price'] ?? 0), $svcAd);
                    if (!$hid) continue;
                    $persId = null;
                    $staffAd = trim((string) ($svc['staff_name'] ?? ''));
                    if ($staffAd !== '') {
                        $persId = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                            ->where('personel_adi', $staffAd)->value('id');
                    }
                    $saatBitis = date('H:i:s', strtotime($cumSaat2) + $sureDk * 60);
                    \DB::table('randevu_hizmetler')->insert([
                        'randevu_id' => $rId, 'hizmet_id' => $hid,
                        'fiyat' => (float) ($svc['price'] ?? 0),
                        'personel_id' => $persId,
                        'sure_dk' => $sureDk,
                        'saat' => $cumSaat2, 'saat_bitis' => $saatBitis,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $cumSaat2 = $saatBitis;
                }

                // Urun tasima: visit.product_sales[] her item icin
                // ayni tarih + product_text + adet + tutar ile [salonappy-prodsale:%] adisyonu varsa
                // o adisyonun AU + tahsilat + alacak'larini visit adisyonuna tasi.
                // Mantik: ayni tarih + ayni musteri ile [salonappy-prodsale:%] markerli
                // adisyon(lar) varsa hepsini visit adisyonuna birlestir + eski adisyon sil.
                // (Strict urun item-level match yerine adisyon-level; cunku Salonappy ayni gun ayni
                // musteriye yapilmis urun satislari visit'le iliskili kabul edilir.)
                $prodAdIds = \DB::table('adisyonlar')->where('salon_id', $salonId)
                    ->where('user_id', $userId)->where('tarih', $tarih)
                    ->where('notlar', 'LIKE', '%[salonappy-prodsale:%')->pluck('id')->all();
                foreach ($prodAdIds as $cAdId) {
                    \DB::table('adisyon_urunler')->where('adisyon_id', $cAdId)
                        ->update(['adisyon_id' => $adId]);
                    \DB::table('tahsilatlar')->where('adisyon_id', $cAdId)
                        ->update(['adisyon_id' => $adId]);
                    \DB::table('taksitli_tahsilatlar')->where('adisyon_id', $cAdId)
                        ->update(['adisyon_id' => $adId]);
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->where('adisyon_id', $cAdId)
                            ->update(['adisyon_id' => $adId]);
                    }
                    \DB::table('adisyonlar')->where('id', $cAdId)->delete();
                    $gUrunTasinan++;
                }
                // Ek olarak bd.product_sales[] icindeki ozel urunler ayrica ekle
                // (taşinan adisyondaki AU'larda zaten var olabilir; eklenirken urun_id ile dedup).
                // Salonappy product_sales bazen tum kalemler price=null doner ama bd.details.
                // total_sales_amount visit'in toplam urun tutarini tutar — esit dagit.
                $psArr = $bd['product_sales'] ?? [];
                $totalSalesAmt = (float) ($d['total_sales_amount'] ?? 0);
                $rawPsTotalSum = 0.0;
                foreach ($psArr as $ps) {
                    $rawPsTotalSum += (float) ($ps['total_amount'] ?? 0);
                }
                $psUseFallback = ($rawPsTotalSum <= 0.01 && $totalSalesAmt > 0 && count($psArr) > 0);
                $psFallbackPer = $psUseFallback ? round($totalSalesAmt / count($psArr), 2) : null;
                $psCount = count($psArr);
                $psAcc = 0.0;
                $psIdx = -1;
                foreach ($psArr as $ps) {
                    $psIdx++;
                    $pAd = trim((string) ($ps['product_text'] ?? ''));
                    if ($pAd === '') continue;
                    $pAdet = max(1, (int) ($ps['quantity'] ?? 1));
                    $pTutar = (float) ($ps['total_amount'] ?? 0);
                    $ppRaw = (float) ($ps['product_price'] ?? 0);
                    if ($psUseFallback) {
                        // Yuvarlama farki son AU'ya tasinir; toplam = total_sales_amount tam
                        $pFiyat = ($psIdx === $psCount - 1)
                            ? round($totalSalesAmt - $psAcc, 2)
                            : $psFallbackPer;
                        $psAcc += $pFiyat;
                    } else {
                        // AU.fiyat = SATIS TOPLAMI (paket akisiyla simetrik). Birim*adet round
                        // kurus kaybi yaratiyordu; UI controller SUM(fiyat) hesabini yapiyor.
                        $pFiyat = $pTutar > 0 ? $pTutar : ($ppRaw * $pAdet);
                    }
                    $urunId = $this->ensureUrun($salonId, $pAd, $pFiyat, $pAd);
                    if (!$urunId) continue;
                    // Visit adisyonunda zaten bu urun var mi (tasinmadan dolayi)? Varsa atla.
                    // Fiyat KONTROL ETMEZ — tasinan AU fiyati otoriter; bd.product_sales fiyat=0
                    // gelse bile uzerine yazmasin.
                    $varMi = \DB::table('adisyon_urunler')->where('adisyon_id', $adId)
                        ->where('urun_id', $urunId)->where('adet', $pAdet)->exists();
                    if ($varMi) continue;
                    \DB::table('adisyon_urunler')->insert([
                        'adisyon_id' => $adId, 'urun_id' => $urunId,
                        'fiyat' => $pFiyat, 'adet' => $pAdet,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $gAU++;
                }

                // Paket usage: package_usages[] her usage icin
                // bu musterinin [salonappy-pkgsale:%] adisyonlarinda ayni service_id'li AH'i bul,
                // APS placeholder'larindan ilk geldi=0 olani geldi=1 + seans_tarih = usage.date.
                foreach (($bd['package_usages'] ?? []) as $pu) {
                    $puSvcId = (string) ($pu['service_id'] ?? '');
                    $puQty   = max(1, (int) ($pu['quantity'] ?? 1));
                    $puDate  = trim((string) ($pu['date'] ?? $tarih));
                    if ($puSvcId === '') continue;
                    // Bu musterinin paket adisyonlarinda ayni Salonappy service_id ile AH(ler)i bul
                    // ensureSalonHizmet salon hizmetini olustururken Salonappy service_id'yi
                    // hatirlamadigi icin svcAd uzerinden gidiyoruz (puSvcId yerine pu.service_text varsa onu kullan).
                    $puSvcAd = trim((string) ($pu['service_text'] ?? ''));
                    if ($puSvcAd === '') continue;
                    $hid = $this->ensureSalonHizmet($salonId, $puSvcAd, 30, 0, $puSvcAd);
                    if (!$hid) continue;
                    // Musterinin paket adisyonlari (markerli) altinda bu hizmet_id'li AH(ler)
                    $ahsForPkg = \DB::table('adisyon_hizmetler as ah')
                        ->join('adisyonlar as a', 'a.id', '=', 'ah.adisyon_id')
                        ->where('a.salon_id', $salonId)->where('a.user_id', $userId)
                        ->where('a.notlar', 'LIKE', '%[salonappy-pkgsale:%')
                        ->where('ah.hizmet_id', $hid)
                        ->orderBy('a.tarih')->pluck('ah.id')->all();
                    // Mantik: Paket Pass1 total_usage kadar APS yazmis (seans_tarih=NULL).
                    // Visit Pass package_usages icin once tarihsiz APS'leri usage.date ile UPDATE et;
                    // tarihsiz APS bittiyse yeni APS insert (kapasite sinirinda). Bu sayede
                    // Salonappy'nin total_usage'i (manuel kullanim dahil) korunur + bilinen tarihler
                    // visit'lerden gelir.
                    $kalanQty = $puQty;
                    foreach ($ahsForPkg as $ahId) {
                        if ($kalanQty <= 0) break;
                        // Once tarihsiz APS'leri date ile guncelle
                        $tarihsiz = \DB::table('adisyon_paket_seanslar')
                            ->where('adisyon_hizmet_id', $ahId)
                            ->whereNull('seans_tarih')
                            ->orderBy('seans_no')->limit($kalanQty)->pluck('id')->all();
                        if (!empty($tarihsiz)) {
                            \DB::table('adisyon_paket_seanslar')->whereIn('id', $tarihsiz)
                                ->update(['seans_tarih' => $puDate, 'updated_at' => date('Y-m-d H:i:s')]);
                            $kalanQty -= count($tarihsiz);
                            $gPaketUsage += count($tarihsiz);
                        }
                        if ($kalanQty <= 0) break;
                        // Kalan kapasite varsa yeni APS insert
                        $ahMeta = \DB::table('adisyon_hizmetler')->where('id', $ahId)
                            ->value('seans_sayisi');
                        $sansSay = $ahMeta ? (int) $ahMeta : 0;
                        $mevcutApsCnt = \DB::table('adisyon_paket_seanslar')
                            ->where('adisyon_hizmet_id', $ahId)->count();
                        $kapasite = max(0, $sansSay - $mevcutApsCnt);
                        if ($kapasite <= 0) continue;
                        $eklenecek = min($kalanQty, $kapasite);
                        for ($i = 1; $i <= $eklenecek; $i++) {
                            \DB::table('adisyon_paket_seanslar')->insert([
                                'adisyon_hizmet_id' => $ahId, 'hizmet_id' => $hid,
                                'seans_no' => $mevcutApsCnt + $i,
                                'geldi' => 1, 'seans_tarih' => $puDate,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                        $kalanQty -= $eklenecek;
                        $gPaketUsage += $eklenecek;
                    }
                }

                // Tahsilat: payments[]
                $payAcc = 0.0;
                foreach (($bd['payments'] ?? []) as $p) {
                    $amount = (float) ($p['amount'] ?? 0);
                    if ($amount <= 0) continue;
                    $pid = (string) ($p['id'] ?? '');
                    $pMarker = $marker . ($pid ? '[salonappy-visit-pay:' . $pid . ']' : '');
                    $pDate = trim((string) ($p['date'] ?? $tarih));
                    $yontemId = $yontemMap($p['payment_method_text'] ?? $p['payment_method'] ?? '');
                    $tahId = \DB::table('tahsilatlar')->insertGetId([
                        'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => $adId,
                        'odeme_tarihi' => $pDate, 'tutar' => $amount,
                        'odeme_yontemi_id' => $yontemId, 'notlar' => $pMarker,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    // TH + TU dagit (fiyat orantili — UI 'odenen' = SUM(TH)+SUM(TU); hem hizmet
                    // hem urun fiyatlarini birlikte hesaba kat ki ürün adisyonlarinda eksi gozukmesin)
                    $ahsBu = \DB::table('adisyon_hizmetler')->where('adisyon_id', $adId)->get(['id', 'fiyat']);
                    $ausBu = \DB::table('adisyon_urunler')->where('adisyon_id', $adId)->get(['id', 'fiyat', 'adet']);
                    $tFiyat = 0.0;
                    foreach ($ahsBu as $a) $tFiyat += (float) $a->fiyat;
                    foreach ($ausBu as $a) $tFiyat += (float) $a->fiyat * max(1, (int) $a->adet);
                    $thPay = []; $tuPay = []; $payToplam = 0;
                    $totCnt = $ahsBu->count() + $ausBu->count();
                    if ($tFiyat > 0) {
                        $oran = $amount / $tFiyat;
                        foreach ($ahsBu as $a) {
                            $py = round((float) $a->fiyat * $oran, 2);
                            $thPay[(int) $a->id] = $py; $payToplam += $py;
                        }
                        foreach ($ausBu as $a) {
                            $py = round((float) $a->fiyat * max(1, (int) $a->adet) * $oran, 2);
                            $tuPay[(int) $a->id] = $py; $payToplam += $py;
                        }
                    } elseif ($totCnt > 0) {
                        $per = round($amount / $totCnt, 2);
                        foreach ($ahsBu as $a) { $thPay[(int) $a->id] = $per; $payToplam += $per; }
                        foreach ($ausBu as $a) { $tuPay[(int) $a->id] = $per; $payToplam += $per; }
                    }
                    $fark = round($amount - $payToplam, 2);
                    if (abs($fark) > 0.001) {
                        if (!empty($tuPay)) {
                            end($tuPay); $sk = key($tuPay);
                            $tuPay[$sk] = round($tuPay[$sk] + $fark, 2);
                        } elseif (!empty($thPay)) {
                            end($thPay); $sk = key($thPay);
                            $thPay[$sk] = round($thPay[$sk] + $fark, 2);
                        }
                    }
                    foreach ($thPay as $ahKey => $py) {
                        if ($py <= 0) continue;
                        \DB::table('tahsilat_hizmetler')->insert([
                            'tahsilat_id' => $tahId, 'adisyon_hizmet_id' => $ahKey, 'tutar' => $py,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    foreach ($tuPay as $auKey => $py) {
                        if ($py <= 0) continue;
                        \DB::table('tahsilat_urunler')->insert([
                            'tahsilat_id' => $tahId, 'adisyon_urun_id' => $auKey, 'tutar' => $py,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $gTah++;
                    $payAcc += $amount;
                }

                // Alacak: unpaid_amount (visit detaylarinda kalan tutar)
                $unpaid = (float) ($d['unpaid_amount'] ?? 0);
                if ($unpaid > 0.01) {
                    $tutar = round($unpaid, 2);
                    $tt = \DB::table('taksitli_tahsilatlar')->insertGetId([
                        'user_id' => $userId, 'adisyon_id' => $adId,
                        'salon_id' => $salonId, 'vade_sayisi' => 1,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $vadeId = \DB::table('taksit_vadeleri')->insertGetId([
                        'taksitli_tahsilat_id' => $tt, 'odendi' => 0,
                        'vade_tarih' => $tarih, 'tutar' => $tutar,
                        'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    if (\Schema::hasTable('alacaklar')) {
                        \DB::table('alacaklar')->insert([
                            'salon_id' => $salonId, 'user_id' => $userId, 'adisyon_id' => $adId,
                            'tutar' => $tutar, 'taksit_vade_id' => $vadeId,
                            'planlanan_odeme_tarihi' => $tarih,
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $gAlacak++;
                }
                $gVisit++;
            } catch (\Throwable $e) {
                $gHata++;
                \Log::warning('[Salonappy visit] hata', ['session' => $sid, 'err' => $e->getMessage()]);
            }
        }

        $this->info("Visit aktarim sonuc: visit=$gVisit adisyon=$gAdisyon randevu=$gRand AH=$gAH AU=$gAU urun-tasinan=$gUrunTasinan paket-usage=$gPaketUsage tahsilat=$gTah alacak=$gAlacak hata=$gHata");
        return 0;
    }

    /**
     * Kurulum dump (salonappy_setup_*.json) icindeki master kayitlari yaz:
     * services + staffs + products + clients + devices.
     * Visit/paket/tahsilat/randevu/adisyon YOK — sadece kurulum.
     * Mevcut helper'lar idempotent (ensureSalonHizmet, ensurePersonel, ensureUrun,
     * aktarimMusteriKontrol), tekrar calistirma guvenli.
     */
    /**
     * Setup dump JSON dosyasini oku, key'lerini normalize et.
     */
    private function loadSetupDump($file)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: {$file}"); return null; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return null; }
        return [
            'services' => $j['services'] ?? [],
            'staffs'   => $j['staffs']   ?? ($j['staff'] ?? []),
            'products' => $j['products'] ?? [],
            'clients'  => $j['clients']  ?? [],
            'devices'  => $j['devices']  ?? [],
        ];
    }

    /**
     * Dump'taki staff isimlerinden DB'deki personel_id'lere map kur.
     * (Hizmet iterasyonunda providing_staff -> personel_sunulan_hizmetler icin gerek.)
     */
    private function buildSaStaffMap($salonId, array $staffs)
    {
        $map = [];
        foreach ($staffs as $p) {
            $ad = trim((string) ($p['name'] ?? $p['full_name'] ?? $p['staff_name'] ?? ''));
            if ($ad === '') continue;
            $saStaffId = (string) ($p['id'] ?? '');
            if ($saStaffId === '') continue;
            // Once exact, sonra trKey match
            $pid = \DB::table('salon_personelleri')->where('salon_id', $salonId)
                ->where('personel_adi', $ad)->value('id');
            if (!$pid) {
                $needle = $this->saTrKey($ad);
                foreach (\DB::table('salon_personelleri')->where('salon_id', $salonId)
                    ->select('id', 'personel_adi')->get() as $row) {
                    if ($this->saTrKey($row->personel_adi) === $needle) { $pid = $row->id; break; }
                }
            }
            if ($pid) $map[$saStaffId] = $pid;
        }
        return $map;
    }

    /**
     * Adim 1 — Musteriler (aktarimMusteriKontrol; telefon dedup ApiController tarafinda).
     */
    private function importSetupMusteriler($file, $salonId)
    {
        $j = $this->loadSetupDump($file); if (!$j) return 1;
        $clients = $j['clients'];
        $this->line("=== Adim 1: MUSTERILER (" . count($clients) . " kayit) ===");
        $apiController = app(\App\Http\Controllers\ApiController::class);
        $eklenen = 0; $hata = 0; $adsizAtlanan = 0;
        $hataDetay = []; // ilk 30 hata orneği
        foreach ($clients as $c) {
            // DB'deki User::setNameAttribute basHarfBuyut uyguluyor — biz de payload'da
            // ayni donusumu yaparsak controller WHERE name=? clause'u match eder,
            // yoksa lowercase-vs-titlecase yuzunden duplicate yaratir.
            $adRaw = trim((string) ($c['name'] ?? ''));
            $ad = $adRaw !== '' ? \App\Helpers\Metin::basHarfBuyut($adRaw) : '';
            $tel = trim((string) ($c['phone_number_local'] ?? $c['phone_number'] ?? ''));
            // Adsiz + telsiz: reddet
            if ($ad === '' && $tel === '') {
                $adsizAtlanan++;
                if (count($hataDetay) < 30) $hataDetay[] = "  cid={$c['id']}: adsiz+telsiz";
                continue;
            }
            $payload = [
                'musteriAdi'   => $ad,
                'telefon'      => $tel,
                'ePosta'       => $c['email'] ?? '',
                'dogumTarihi'  => $c['birthdate'] ?? '',
                'cinsiyet'     => $c['gender_text'] ?? '',
                'notlar'       => $c['notes'] ?? '',
                'medeniDurum'  => '', 'meslek' => '', 'adres' => '',
                'kayitTarihi'  => $c['created_at'] ?? '',
                'salonId'      => $salonId,
                'salonAppyId'  => $c['id'] ?? null,
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->aktarimMusteriKontrol($req);
                $uid = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                if ($uid && ctype_digit($uid)) {
                    $eklenen++;
                } else {
                    $hata++;
                    if (count($hataDetay) < 30) $hataDetay[] = "  cid={$c['id']} name=" . mb_substr($ad, 0, 30) . " tel=$tel: yanit=" . mb_substr((string) $uid, 0, 50);
                }
            } catch (\Throwable $e) {
                $hata++;
                $msg = mb_substr($e->getMessage(), 0, 150);
                if (count($hataDetay) < 30) $hataDetay[] = "  cid={$c['id']} name=" . mb_substr($ad, 0, 30) . " tel=$tel: exception=$msg";
                \Log::warning('[Salonappy setup] musteri', ['cid' => $c['id'] ?? '?', 'name' => $ad, 'tel' => $tel, 'err' => $e->getMessage()]);
            }
        }
        $this->info("Musteri: eklendi/eslesti=$eklenen, hata=$hata, adsiz-telsiz atlanan=$adsizAtlanan / " . count($clients));
        if (!empty($hataDetay)) {
            $this->line("\n=== Sorunlu kayit ornekleri (ilk 30) ===");
            foreach ($hataDetay as $ln) $this->line($ln);
        }
        return 0;
    }

    /**
     * Adim 2 — Personel + Cihaz (staff.type ayrimi).
     */
    private function importSetupPersoneller($file, $salonId)
    {
        $j = $this->loadSetupDump($file); if (!$j) return 1;
        $staffs = $j['staffs'];
        $this->line("=== Adim 2: PERSONEL + CIHAZ (" . count($staffs) . " kayit) ===");
        // BLACKLIST mantigi: sadece 'cihaz'/'device'/'equipment' cihaz sayilir.
        // 'personel', 'yonetici', 'sekreter', 'manager', 'staff', 'owner', 'admin',
        // 'employee', ve bilinmeyen tum type'lar PERSONEL sayilir.
        $cihazTipleri = ['cihaz', 'device', 'equipment', 'machine'];
        $personelEklenen = 0; $personelGuncel = 0; $cihazStaffEklenen = 0;

        $roleResolve = function ($p) {
            $tt = trim((string) ($p['type_text'] ?? ''));
            $t  = strtolower(trim((string) ($p['type'] ?? '')));
            $candidates = [];
            if ($tt !== '') $candidates[] = $tt;
            if ($t === 'yonetici') { $candidates[] = 'Hesap Sahibi'; $candidates[] = 'Yönetici'; }
            elseif ($t === 'personel') $candidates[] = 'Personel';
            $candidates[] = 'Hesap Sahibi';
            foreach ($candidates as $name) {
                $id = \DB::table('roles')->where('name', $name)->value('id');
                if ($id) return $id;
            }
            return \DB::table('roles')->orderBy('id')->value('id');
        };

        foreach ($staffs as $p) {
            $ad = trim((string) ($p['name'] ?? $p['full_name'] ?? $p['staff_name'] ?? ''));
            if ($ad === '') continue;
            $tip = strtolower(trim((string) ($p['type'] ?? '')));

            // CIHAZ (sadece explicit cihaz/device/equipment/machine)
            if (in_array($tip, $cihazTipleri, true)) {
                $exists = \DB::table('cihazlar')->where('salon_id', $salonId)
                    ->where('cihaz_adi', $ad)->exists();
                if (!$exists) {
                    try {
                        \DB::table('cihazlar')->insert([
                            'salon_id' => $salonId, 'cihaz_adi' => $ad,
                            'aktifmi' => 1, 'durum' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $cihazStaffEklenen++;
                    } catch (\Throwable $e) {
                        \Log::warning('[Salonappy setup] cihaz', ['ad' => $ad, 'err' => $e->getMessage()]);
                    }
                }
                continue;
            }

            // PERSONEL (canonical detayli) — cihaz olmayan tum type'lar personel
            $tel = preg_replace('~\D~', '', (string) ($p['phone_number'] ?? $p['phone_number_full'] ?? ''));
            $email = trim((string) ($p['email_address'] ?? $p['email'] ?? ''));
            $unvan = trim((string) ($p['type_text'] ?? ''));
            $roleId = $roleResolve($p);

            $existPers = null;
            if ($tel) {
                $existPers = \App\Personeller::where('salon_id', $salonId)
                    ->where('cep_telefon', $tel)->first();
            }
            if (!$existPers) {
                $existPers = \App\Personeller::where('salon_id', $salonId)
                    ->where('personel_adi', $ad)->first();
            }

            try {
                $yetkili = null;
                if ($tel) {
                    $yetkili = \App\IsletmeYetkilileri::where('gsm1', $tel)->first();
                }
                $isYeniYetkili = false;
                if (!$yetkili) {
                    $yetkili = new \App\IsletmeYetkilileri();
                    $isYeniYetkili = true;
                }
                $yetkili->name = $ad;
                if ($tel) $yetkili->gsm1 = $tel;
                if ($email && \Schema::hasColumn('isletmeyetkilileri', 'email')) $yetkili->email = $email;
                if ($unvan && \Schema::hasColumn('isletmeyetkilileri', 'unvan')) $yetkili->unvan = $unvan;
                $yetkili->aktif = true;
                if ($isYeniYetkili) {
                    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
                    $yetkili->password = \Hash::make(substr($random, 0, 8));
                }
                $yetkili->save();

                if (!$existPers) {
                    $personel = new \App\Personeller();
                    $personel->salon_id = $salonId;
                    $personel->aktif = true;
                    $personel->takvimde_gorunsun = 1;
                    $sonSira = \App\Personeller::where('salon_id', $salonId)
                        ->orderBy('takvim_sirasi', 'desc')->value('takvim_sirasi');
                    $personel->takvim_sirasi = ($sonSira ? $sonSira : 0) + 1;
                    $sonRenk = \App\Personeller::where('salon_id', $salonId)
                        ->orderBy('id', 'desc')->value('renk');
                    $personel->renk = (!$sonRenk || $sonRenk == 10) ? 1 : ($sonRenk + 1);
                    $personelEklenen++;
                } else {
                    $personel = $existPers;
                    $personel->aktif = true;
                    if (!$personel->takvimde_gorunsun) $personel->takvimde_gorunsun = 1;
                    $personelGuncel++;
                }
                $personel->personel_adi = $ad;
                if ($tel) $personel->cep_telefon = $tel;
                if ($unvan) $personel->unvan = $unvan;
                $personel->yetkili_id = $yetkili->id;
                if ($roleId) $personel->role_id = $roleId;
                if (\Schema::hasColumn('salon_personelleri', 'hizmet_prim_yuzde')) $personel->hizmet_prim_yuzde = 0;
                if (\Schema::hasColumn('salon_personelleri', 'urun_prim_yuzde'))   $personel->urun_prim_yuzde = 0;
                if (\Schema::hasColumn('salon_personelleri', 'paket_prim_yuzde')) $personel->paket_prim_yuzde = 0;
                $personel->save();

                $varOlanGunSayisi = \DB::table('personel_calisma_saatleri')
                    ->where('personel_id', $personel->id)->count();
                if ($varOlanGunSayisi === 0) {
                    for ($i = 1; $i <= 7; $i++) {
                        \DB::table('personel_calisma_saatleri')->insert([
                            'personel_id'     => $personel->id,
                            'haftanin_gunu'   => $i,
                            'calisiyor'       => ($i === 7) ? 0 : 1,
                            'baslangic_saati' => '09:00:00',
                            'bitis_saati'     => '18:00:00',
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy setup] personel', [
                    'ad' => $ad, 'tel' => $tel, 'err' => $e->getMessage(),
                ]);
            }
        }
        $this->info("Personel: yeni=$personelEklenen guncel=$personelGuncel, cihaz=$cihazStaffEklenen / " . count($staffs));
        return 0;
    }

    /**
     * Adim 3 — Urunler.
     */
    private function importSetupUrunler($file, $salonId)
    {
        $j = $this->loadSetupDump($file); if (!$j) return 1;
        $products = $j['products'];
        $this->line("=== Adim 3: URUNLER (" . count($products) . " kayit) ===");
        // Salonappy'de ayni isim ama farkli case = farkli urun olabilir
        // (ornek: 'Genosis maske' 350 TL vs 'genosis maske' 200 TL — farkli stok/parti).
        // Bu yüzden case-SENSITIVE dedup yapiyoruz: sadece birebir ad esleşme.
        $eklenen = 0; $yeni = 0;
        $eklenenIds = [];
        foreach ($products as $u) {
            $ad = trim((string) ($u['name'] ?? $u['product_name'] ?? $u['title'] ?? ''));
            if ($ad === '') continue;
            $fiyat = (float) ($u['price'] ?? $u['amount'] ?? $u['sale_price'] ?? 0);
            $barkod = trim((string) ($u['barcode'] ?? ''));
            // Case-sensitive match (BINARY): 'Genosis maske' vs 'genosis maske' farkli sayilir
            $existing = \DB::table('urunler')
                ->where('salon_id', $salonId)
                ->whereRaw('BINARY urun_adi = ?', [$ad])
                ->first();
            if ($existing) {
                $eklenenIds[] = $existing->id;
                $eklenen++;
                // Fiyat/barkod update (re-import guvenli)
                $upd = [];
                if ($fiyat > 0 && \Schema::hasColumn('urunler', 'satis_fiyati')) $upd['satis_fiyati'] = $fiyat;
                if ($fiyat > 0 && \Schema::hasColumn('urunler', 'fiyat')) $upd['fiyat'] = $fiyat;
                if ($barkod !== '' && \Schema::hasColumn('urunler', 'barkod')) $upd['barkod'] = $barkod;
                if (\Schema::hasColumn('urunler', 'aktif')) $upd['aktif'] = 1;
                if (!empty($upd)) {
                    \DB::table('urunler')->where('id', $existing->id)->update($upd);
                }
                continue;
            }
            // Yeni urun
            try {
                $insert = [
                    'salon_id' => $salonId,
                    'urun_adi' => $ad,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (\Schema::hasColumn('urunler', 'aktif')) $insert['aktif'] = 1;
                if ($fiyat > 0 && \Schema::hasColumn('urunler', 'satis_fiyati')) $insert['satis_fiyati'] = $fiyat;
                if ($fiyat > 0 && \Schema::hasColumn('urunler', 'fiyat')) $insert['fiyat'] = $fiyat;
                if ($barkod !== '' && \Schema::hasColumn('urunler', 'barkod')) $insert['barkod'] = $barkod;
                $uid = \DB::table('urunler')->insertGetId($insert);
                $eklenenIds[] = $uid;
                $eklenen++; $yeni++;
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy setup] urun', ['ad' => $ad, 'err' => $e->getMessage()]);
            }
        }
        $this->info("Urun: eklendi/eslesti=$eklenen (yeni=$yeni) / " . count($products));
        return 0;
    }

    /**
     * Adim 4 — Hizmetler (sure + kategori + providing_staff pivot).
     * NOT: Salonappy /service/salon endpoint'inde FIYAT YOK — visit aktarimiyla zenginlesir.
     */
    private function importSetupHizmetler($file, $salonId)
    {
        $j = $this->loadSetupDump($file); if (!$j) return 1;
        $services = $j['services'];
        $staffs   = $j['staffs'];
        $this->line("=== Adim 4: HIZMETLER + PIVOT (" . count($services) . " kayit) ===");
        // On kosul: personel yazilmis olmali (providing_staff pivot icin)
        $personelSayisi = \DB::table('salon_personelleri')->where('salon_id', $salonId)->count();
        if ($personelSayisi === 0) {
            $this->warn("!!! Salon $salonId'de hic personel yok.");
            $this->warn("    Once --only-setup-personel calistir, yoksa providing_staff pivot BOS kalir.");
            if (!$this->confirm('Yine de devam edilsin mi? (pivot yazilmadan sadece hizmet+kategori)', false)) {
                return 1;
            }
        }
        // Personel map (providing_staff eslesirmesi icin — DB'den yeniden kur)
        $saStaffIdToPersonelId = $this->buildSaStaffMap($salonId, $staffs);
        $this->line("  saStaff -> personel_id map: " . count($saStaffIdToPersonelId) . " / " . count($staffs));
        if (count($saStaffIdToPersonelId) < count($staffs) && $personelSayisi > 0) {
            $this->warn("  Uyari: dump'ta " . count($staffs) . " staff var, DB'de sadece " . count($saStaffIdToPersonelId) . " eslesme bulundu.");
        }

        $hizmetEklenen = 0; $kategoriBaglanan = 0; $personelHizmetEklenen = 0;
        foreach ($services as $s) {
            $ad = trim((string) ($s['name'] ?? $s['service_name'] ?? $s['title'] ?? ''));
            if ($ad === '') continue;
            $sure = (int) ($s['duration'] ?? $s['duration_default'] ?? $s['process_time'] ?? 30);
            if ($sure < 15) $sure = 15;
            $fiyat = (float) ($s['price'] ?? $s['amount'] ?? $s['service_price'] ?? 0);
            $canon = $ad;
            $hid = $this->ensureSalonHizmet($salonId, $ad, $sure, $fiyat, $canon);
            if (!$hid) continue;
            $hizmetEklenen++;

            // Sure update (re-import guvenli) + aktif=1
            \DB::table('salon_sunulan_hizmetler')
                ->where('salon_id', $salonId)->where('hizmet_id', $hid)
                ->update([
                    'sure_dk' => $sure,
                    'aktif'   => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            // Kategori bagla
            $catTitle = trim((string) ($s['service_group_title'] ?? ''));
            if ($catTitle !== '') {
                $catId = \DB::table('hizmet_kategorisi')
                    ->where('hizmet_kategorisi_adi', $catTitle)
                    ->where(function ($q) use ($salonId) {
                        $q->whereNull('salon_id')->orWhere('salon_id', $salonId);
                    })
                    ->value('id');
                if (!$catId) {
                    $insert = [
                        'hizmet_kategorisi_adi' => $catTitle,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if (\Schema::hasColumn('hizmet_kategorisi', 'salon_id')) $insert['salon_id'] = $salonId;
                    if (\Schema::hasColumn('hizmet_kategorisi', 'ozel_kategori')) $insert['ozel_kategori'] = 1;
                    $catId = \DB::table('hizmet_kategorisi')->insertGetId($insert);
                }
                if ($catId) {
                    \DB::table('salon_sunulan_hizmetler')
                        ->where('salon_id', $salonId)->where('hizmet_id', $hid)
                        ->update(['hizmet_kategori_id' => $catId]);
                    $kategoriBaglanan++;
                }
            }

            // providing_staff pivot -> personel_sunulan_hizmetler
            foreach (($s['providing_staff'] ?? []) as $ps) {
                $saStaffId = (string) ($ps['id'] ?? '');
                if ($saStaffId === '') continue;
                $persId = $saStaffIdToPersonelId[$saStaffId] ?? null;
                if (!$persId) continue;
                $exists = \DB::table('personel_sunulan_hizmetler')
                    ->where('personel_id', $persId)
                    ->where('hizmet_id', $hid)->exists();
                if ($exists) continue;
                try {
                    \DB::table('personel_sunulan_hizmetler')->insert([
                        'personel_id' => $persId,
                        'hizmet_id'   => $hid,
                        'bolum'       => 2,
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                    $personelHizmetEklenen++;
                } catch (\Throwable $e) {
                    \Log::warning('[Salonappy setup] personel-hizmet', [
                        'pers' => $persId, 'hizmet' => $hid, 'err' => $e->getMessage(),
                    ]);
                }
            }
        }
        $this->info("Hizmet: eklendi/eslesti=$hizmetEklenen / " . count($services));
        $this->info("Kategori baglanan: $kategoriBaglanan, personel-hizmet pivot: $personelHizmetEklenen");
        return 0;
    }

    private function importSetupOnly($file, $salonId)
    {
        $this->info(">>> Salonappy KURULUM: musteri -> personel -> urun -> hizmet+pivot");
        $r = $this->importSetupMusteriler($file, $salonId); if ($r !== 0) return $r;
        $r = $this->importSetupPersoneller($file, $salonId); if ($r !== 0) return $r;
        $r = $this->importSetupUrunler($file, $salonId); if ($r !== 0) return $r;
        $r = $this->importSetupHizmetler($file, $salonId); if ($r !== 0) return $r;
        $this->info('>>> Kurulum aktarimi tamam.');
        return 0;
    }

    private function importSetupOnlyLegacy($file, $salonId)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: {$file}"); return 1; }
        $j = json_decode(file_get_contents($file), true);
        if (!is_array($j)) { $this->error('Gecersiz JSON.'); return 1; }
        $services = $j['services'] ?? [];
        $staffs   = $j['staffs']   ?? ($j['staff'] ?? []);
        $products = $j['products'] ?? [];
        $clients  = $j['clients']  ?? [];
        $devices  = $j['devices']  ?? [];
        $this->line(sprintf('Dump iceri: services=%d staffs=%d products=%d clients=%d devices=%d',
            count($services), count($staffs), count($products), count($clients), count($devices)));

        // 1) Personeller + Cihazlar ÖNCE (staff.type ayrimi).
        // Salonappy staff'larini canonical akista (StoreAdminController:personelekleduzenle)
        // yazariz: aktif=1, takvimde_gorunsun=1, cep_telefon, role_id, IsletmeYetkilileri,
        // PersonelCalismaSaatleri 7 gun default (Mon-Sat 09:00-18:00, Sun pasif).
        $personelTipleri = ['personel', 'yonetici', 'manager', 'employee', 'staff', 'owner', 'admin'];
        $personelEklenen = 0; $personelGuncel = 0; $cihazStaffEklenen = 0;
        $saStaffIdToPersonelId = []; // Salonappy staff_id => DB salon_personelleri.id

        // Role map: type_text/type -> roles.name -> id
        $roleResolve = function ($p) {
            $tt = trim((string) ($p['type_text'] ?? ''));
            $t  = strtolower(trim((string) ($p['type'] ?? '')));
            $candidates = [];
            if ($tt !== '') $candidates[] = $tt;
            if ($t === 'yonetici') { $candidates[] = 'Hesap Sahibi'; $candidates[] = 'Yönetici'; }
            elseif ($t === 'personel') $candidates[] = 'Personel';
            $candidates[] = 'Hesap Sahibi'; // fallback
            foreach ($candidates as $name) {
                $id = \DB::table('roles')->where('name', $name)->value('id');
                if ($id) return $id;
            }
            return \DB::table('roles')->orderBy('id')->value('id');
        };

        foreach ($staffs as $p) {
            $ad = trim((string) ($p['name'] ?? $p['full_name'] ?? $p['staff_name'] ?? ''));
            if ($ad === '') continue;
            $saStaffId = (string) ($p['id'] ?? '');
            $tip = strtolower(trim((string) ($p['type'] ?? '')));

            // CIHAZ
            if ($tip !== '' && !in_array($tip, $personelTipleri, true)) {
                $exists = \DB::table('cihazlar')->where('salon_id', $salonId)
                    ->where('cihaz_adi', $ad)->exists();
                if (!$exists) {
                    try {
                        \DB::table('cihazlar')->insert([
                            'salon_id'  => $salonId,
                            'cihaz_adi' => $ad,
                            'aktifmi'   => 1, 'durum' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $cihazStaffEklenen++;
                    } catch (\Throwable $e) {
                        \Log::warning('[Salonappy setup] cihaz', ['ad' => $ad, 'err' => $e->getMessage()]);
                    }
                }
                continue;
            }

            // PERSONEL (canonical detayli)
            $tel = preg_replace('~\D~', '', (string) ($p['phone_number'] ?? $p['phone_number_full'] ?? ''));
            $email = trim((string) ($p['email_address'] ?? $p['email'] ?? ''));
            $unvan = trim((string) ($p['type_text'] ?? ''));
            $roleId = $roleResolve($p);

            // Dedup: salon + (ad veya cep_telefon) ile mevcut personel ara
            $existPers = null;
            if ($tel) {
                $existPers = \App\Personeller::where('salon_id', $salonId)
                    ->where('cep_telefon', $tel)->first();
            }
            if (!$existPers) {
                $existPers = \App\Personeller::where('salon_id', $salonId)
                    ->where('personel_adi', $ad)->first();
            }

            try {
                // IsletmeYetkilileri firstOrCreate (gsm1 ile)
                $yetkili = null;
                if ($tel) {
                    $yetkili = \App\IsletmeYetkilileri::where('gsm1', $tel)->first();
                }
                $isYeniYetkili = false;
                if (!$yetkili) {
                    $yetkili = new \App\IsletmeYetkilileri();
                    $isYeniYetkili = true;
                }
                $yetkili->name = $ad;
                if ($tel) $yetkili->gsm1 = $tel;
                if ($email && \Schema::hasColumn('isletmeyetkilileri', 'email')) $yetkili->email = $email;
                if ($unvan && \Schema::hasColumn('isletmeyetkilileri', 'unvan')) $yetkili->unvan = $unvan;
                $yetkili->aktif = true;
                if ($isYeniYetkili) {
                    $random = str_shuffle('abcdefghjklmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ1234567890');
                    $yetkili->password = \Hash::make(substr($random, 0, 8));
                }
                $yetkili->save();

                // Personeller insert/update
                if (!$existPers) {
                    $personel = new \App\Personeller();
                    $personel->salon_id = $salonId;
                    $personel->aktif = true;
                    $personel->takvimde_gorunsun = 1;
                    $sonSira = \App\Personeller::where('salon_id', $salonId)
                        ->orderBy('takvim_sirasi', 'desc')->value('takvim_sirasi');
                    $personel->takvim_sirasi = ($sonSira ? $sonSira : 0) + 1;
                    $sonRenk = \App\Personeller::where('salon_id', $salonId)
                        ->orderBy('id', 'desc')->value('renk');
                    $personel->renk = (!$sonRenk || $sonRenk == 10) ? 1 : ($sonRenk + 1);
                    $personelEklenen++;
                } else {
                    $personel = $existPers;
                    $personel->aktif = true;
                    if (!$personel->takvimde_gorunsun) $personel->takvimde_gorunsun = 1;
                    $personelGuncel++;
                }
                $personel->personel_adi = $ad;
                if ($tel) $personel->cep_telefon = $tel;
                if ($unvan) $personel->unvan = $unvan;
                $personel->yetkili_id = $yetkili->id;
                if ($roleId) $personel->role_id = $roleId;
                if (\Schema::hasColumn('salon_personelleri', 'hizmet_prim_yuzde')) $personel->hizmet_prim_yuzde = 0;
                if (\Schema::hasColumn('salon_personelleri', 'urun_prim_yuzde'))   $personel->urun_prim_yuzde = 0;
                if (\Schema::hasColumn('salon_personelleri', 'paket_prim_yuzde')) $personel->paket_prim_yuzde = 0;
                $personel->save();

                // Calisma saatleri (yoksa 7 gun default ekle, varsa dokunma)
                $varOlanGunSayisi = \DB::table('personel_calisma_saatleri')
                    ->where('personel_id', $personel->id)->count();
                if ($varOlanGunSayisi === 0) {
                    for ($i = 1; $i <= 7; $i++) {
                        \DB::table('personel_calisma_saatleri')->insert([
                            'personel_id'     => $personel->id,
                            'haftanin_gunu'   => $i,
                            'calisiyor'       => ($i === 7) ? 0 : 1, // Pazar pasif
                            'baslangic_saati' => '09:00:00',
                            'bitis_saati'     => '18:00:00',
                            'created_at'      => date('Y-m-d H:i:s'),
                            'updated_at'      => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                // Map: Salonappy staff_id -> DB personel_id
                if ($saStaffId !== '') {
                    $saStaffIdToPersonelId[$saStaffId] = $personel->id;
                }
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy setup] personel', [
                    'ad' => $ad, 'tel' => $tel, 'err' => $e->getMessage(),
                ]);
            }
        }
        $this->line("Personel: yeni=$personelEklenen guncel=$personelGuncel, cihaz=$cihazStaffEklenen / " . count($staffs));

        // 2) Hizmetler + kategori + providing_staff pivot
        // - ensureSalonHizmet havuzda match varsa o hizmeti kullanir (ozel_hizmet=1 yaratmaz)
        // - service_group_title -> hizmet_kategorisi firstOrCreate -> SalonHizmetler.hizmet_kategori_id UPDATE
        // - providing_staff[] -> personel_sunulan_hizmetler insert (idempotent, bolum=2)
        // - sure_dk & fiyat: SalonHizmetler'e Salonappy parametreleriyle UPDATE (re-import guvenli)
        $hizmetEklenen = 0; $kategoriBaglanan = 0; $personelHizmetEklenen = 0;
        foreach ($services as $s) {
            $ad = trim((string) ($s['name'] ?? $s['service_name'] ?? $s['title'] ?? ''));
            if ($ad === '') continue;
            $sure = (int) ($s['duration'] ?? $s['duration_default'] ?? $s['process_time'] ?? 30);
            if ($sure < 15) $sure = 15;
            $fiyat = (float) ($s['price'] ?? $s['amount'] ?? $s['service_price'] ?? 0);
            $canon = $ad;
            $hid = $this->ensureSalonHizmet($salonId, $ad, $sure, $fiyat, $canon);
            if (!$hid) continue;
            $hizmetEklenen++;

            // Salonappy parametreleri ile SalonHizmetler'i UPDATE (re-import sure/fiyat yenile)
            \DB::table('salon_sunulan_hizmetler')
                ->where('salon_id', $salonId)->where('hizmet_id', $hid)
                ->update([
                    'sure_dk' => $sure,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            // Kategori bagla (service_group_title)
            $catTitle = trim((string) ($s['service_group_title'] ?? ''));
            if ($catTitle !== '') {
                // Once salon-specific veya global match ara
                $catId = \DB::table('hizmet_kategorisi')
                    ->where('hizmet_kategorisi_adi', $catTitle)
                    ->where(function ($q) use ($salonId) {
                        $q->whereNull('salon_id')->orWhere('salon_id', $salonId);
                    })
                    ->value('id');
                if (!$catId) {
                    // Salon-specific yarat
                    $insert = [
                        'hizmet_kategorisi_adi' => $catTitle,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if (\Schema::hasColumn('hizmet_kategorisi', 'salon_id')) $insert['salon_id'] = $salonId;
                    if (\Schema::hasColumn('hizmet_kategorisi', 'ozel_kategori')) $insert['ozel_kategori'] = 1;
                    $catId = \DB::table('hizmet_kategorisi')->insertGetId($insert);
                }
                if ($catId) {
                    \DB::table('salon_sunulan_hizmetler')
                        ->where('salon_id', $salonId)->where('hizmet_id', $hid)
                        ->update(['hizmet_kategori_id' => $catId]);
                    $kategoriBaglanan++;
                }
            }

            // providing_staff -> personel_sunulan_hizmetler pivot
            foreach (($s['providing_staff'] ?? []) as $ps) {
                $saStaffId = (string) ($ps['id'] ?? '');
                if ($saStaffId === '') continue;
                $persId = $saStaffIdToPersonelId[$saStaffId] ?? null;
                if (!$persId) continue;
                $exists = \DB::table('personel_sunulan_hizmetler')
                    ->where('personel_id', $persId)
                    ->where('hizmet_id', $hid)->exists();
                if ($exists) continue;
                try {
                    \DB::table('personel_sunulan_hizmetler')->insert([
                        'personel_id' => $persId,
                        'hizmet_id'   => $hid,
                        'bolum'       => 2, // 0=Bayan, 1=Bay, 2=Ortak (default)
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                    $personelHizmetEklenen++;
                } catch (\Throwable $e) {
                    \Log::warning('[Salonappy setup] personel-hizmet', [
                        'pers' => $persId, 'hizmet' => $hid, 'err' => $e->getMessage(),
                    ]);
                }
            }
        }
        $this->line("Hizmet eklendi/eslesti: $hizmetEklenen / " . count($services));
        $this->line("Kategori baglanan: $kategoriBaglanan, personel-hizmet pivot: $personelHizmetEklenen");

        // 3) Urunler
        $urunEklenen = 0;
        foreach ($products as $u) {
            $ad = trim((string) ($u['name'] ?? $u['product_name'] ?? $u['title'] ?? ''));
            if ($ad === '') continue;
            $fiyat = (float) ($u['price'] ?? $u['amount'] ?? $u['sale_price'] ?? 0);
            $canon = $ad;
            $uid = $this->ensureUrun($salonId, $ad, $fiyat, $canon);
            if ($uid) $urunEklenen++;
        }
        $this->line("Urun eklendi/eslesti: $urunEklenen / " . count($products));

        // 4) Musteriler (aktarimMusteriKontrol ile — telefon dedup ApiController tarafinda)
        $apiController = app(\App\Http\Controllers\ApiController::class);
        $musteriEklenen = 0; $musteriHata = 0;
        foreach ($clients as $c) {
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
                'salonAppyId'  => $c['id'] ?? null,
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->aktarimMusteriKontrol($req);
                $uid = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                if ($uid && ctype_digit($uid)) $musteriEklenen++;
                else $musteriHata++;
            } catch (\Throwable $e) {
                $musteriHata++;
                \Log::warning('[Salonappy setup] musteri', ['cid' => $c['id'] ?? '?', 'err' => $e->getMessage()]);
            }
        }
        $this->line("Musteri eklendi/eslesti: $musteriEklenen, hata: $musteriHata / " . count($clients));

        // 5) Cihazlar (StoreAdminController:16403 sema: salon_id, cihaz_adi, aktifmi=true, durum=true)
        $cihazEklenen = 0;
        foreach ($devices as $dv) {
            $ad = trim((string) ($dv['name'] ?? $dv['device_name'] ?? $dv['title'] ?? ''));
            if ($ad === '') continue;
            // Idempotent: ayni isimde cihaz varsa atla
            $exists = \DB::table('cihazlar')->where('salon_id', $salonId)
                ->where('cihaz_adi', $ad)->exists();
            if ($exists) continue;
            try {
                $cihazId = \DB::table('cihazlar')->insertGetId([
                    'salon_id'  => $salonId,
                    'cihaz_adi' => $ad,
                    'aktifmi'   => 1,
                    'durum'     => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                // SalonCihazRenkleri ekle (StoreAdminController:16411 mantigi)
                if (\Schema::hasTable('salon_cihaz_renkleri')) {
                    $sonRenk = \DB::table('salon_cihaz_renkleri')->where('salon_id', $salonId)
                        ->orderByDesc('id')->value('renk_id');
                    $renkId = $sonRenk ? (($sonRenk % 10) + 1) : 1;
                    \DB::table('salon_cihaz_renkleri')->insert([
                        'salon_id' => $salonId,
                        'cihaz_id' => $cihazId,
                        'renk_id'  => $renkId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $cihazEklenen++;
            } catch (\Throwable $e) {
                \Log::warning('[Salonappy setup] cihaz', ['ad' => $ad, 'err' => $e->getMessage()]);
            }
        }
        $this->line("Cihaz eklendi: $cihazEklenen / " . count($devices));

        $this->info('Kurulum aktarimi tamam.');
        return 0;
    }

    /**
     * Bir musteriyi DB'den tani: tel / ad LIKE / user_id ile bul.
     * Bulunan her user icin salondaki Salonappy markerli kayit sayilarini doker.
     */
    private function inspectMusteri($salonId, $tel = null, $ad = null, $userId = null)
    {
        $tel = $tel ? preg_replace('~\D~', '', (string) $tel) : null;
        $ad  = $ad ? trim((string) $ad) : null;
        $userId = $userId ? (int) $userId : null;

        $q = \DB::table('users');
        if ($userId) {
            $q->where('id', $userId);
        } elseif ($tel) {
            $q->where('cep_telefon', $tel);
        } elseif ($ad) {
            $q->where('name', 'LIKE', '%' . $ad . '%');
        } else {
            $this->error('--tel / --ad / --musteri-id en az biri zorunlu.');
            return 1;
        }
        $users = $q->select('id', 'name', 'cep_telefon', 'created_at')
            ->orderBy('id')->limit(20)->get();

        if ($users->isEmpty()) {
            $this->warn('Eslesen user YOK.');
            $this->line('   Kullanilan filtre: ' . json_encode(compact('tel', 'ad', 'userId'), JSON_UNESCAPED_UNICODE));
            return 0;
        }

        $this->info("Eslesen kullanici sayisi: " . $users->count());
        foreach ($users as $u) {
            $this->line('=== USER #' . $u->id . ' | ' . $u->name . ' | tel=' . ($u->cep_telefon ?? '?') . ' | kayit=' . ($u->created_at ?? '?') . ' ===');
            $rs = \DB::table('randevular')->where('user_id', $u->id)->where('salon_id', $salonId)->count();
            $ads = \DB::table('adisyonlar')->where('user_id', $u->id)->where('salon_id', $salonId)->count();
            $ts = \DB::table('tahsilatlar')->where('user_id', $u->id)->where('salon_id', $salonId)->count();
            $rsApp = \DB::table('randevular')->where('user_id', $u->id)->where('salon_id', $salonId)
                ->where(function ($q) {
                    $q->where('personel_notu', 'LIKE', '%[salonappy:%')
                      ->orWhere('personel_notu', 'LIKE', '%[salonappy-visit:%');
                })->count();
            $adsApp = \DB::table('adisyonlar')->where('user_id', $u->id)->where('salon_id', $salonId)
                ->where(function ($q) {
                    $q->where('notlar', 'LIKE', '%[salonappy%');
                    foreach (['aciklama', 'adisyon_notu', 'genel_aciklama'] as $col) {
                        if (\Schema::hasColumn('adisyonlar', $col)) {
                            $q->orWhere($col, 'LIKE', '%[salonappy%');
                        }
                    }
                })->count();
            $this->line(sprintf('  randevu: total=%d, salonappy-markerli=%d', $rs, $rsApp));
            $this->line(sprintf('  adisyon: total=%d, salonappy-markerli=%d', $ads, $adsApp));
            $this->line(sprintf('  tahsilat: total=%d', $ts));
            // Portfoy
            if (\Schema::hasTable('musteri_portfoy')) {
                $pf = \DB::table('musteri_portfoy')->where('user_id', $u->id)->where('salon_id', $salonId)->count();
                $this->line('  portfoy kaydi: ' . $pf);
            }
        }
        return 0;
    }

    /**
     * Salonun [salonappy:%] veya [salonappy-visit:%] markerli randevularinda
     * randevu_hizmetler.saat / saat_bitis NULL olanlari, randevular.saat
     * tabaninda cumulative sure_dk ile yeniden hesapla.
     *
     * Default --dump-file akisindaki controller (salonAppyAdisyonRandevuEkle)
     * RH.saat/saat_bitis yazmiyordu — bu komut tek seferlik tamir.
     */
    /**
     * Salonappy tahsilat xlsx export'unu DB Salonappy tahsilatlariyla karsilastir.
     * Excel: Musteri | Satis tarihi | Odeme yontemi | Tutar | Kaynak | Urun/Hizmet | Olusturan | Olusturulma
     * Match: user_id (users.name trKey LIKE) + tarih + tutar.
     * Eksik olanlar CSV'ye yazilir.
     */
    private function reconcileTahsilat($salonId, $file, $from = null, $to = null)
    {
        if (!file_exists($file)) { $this->error("Dosya yok: $file"); return 1; }

        // 1) Excel oku
        $this->info("Excel okunuyor: $file");
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);
            $ws = $spreadsheet->getActiveSheet();
        } catch (\Throwable $e) {
            $this->error('Excel okunamadi: ' . $e->getMessage());
            return 1;
        }

        // TR ay isimleri -> ay no
        $trAy = [
            'ocak' => 1, 'şubat' => 2, 'subat' => 2, 'mart' => 3, 'nisan' => 4,
            'mayıs' => 5, 'mayis' => 5, 'haziran' => 6, 'temmuz' => 7,
            'ağustos' => 8, 'agustos' => 8, 'eylül' => 9, 'eylul' => 9,
            'ekim' => 10, 'kasım' => 11, 'kasim' => 11, 'aralık' => 12, 'aralik' => 12,
        ];
        $parseDate = function ($s) use ($trAy) {
            $s = mb_strtolower(trim((string) $s), 'UTF-8');
            if (preg_match('/^(\d{1,2})\s+([a-zçğıöşü]+)\s+(\d{4})/u', $s, $m)) {
                $ay = $trAy[$m[2]] ?? null;
                if ($ay) return sprintf('%04d-%02d-%02d', (int) $m[3], $ay, (int) $m[1]);
            }
            return null;
        };
        $parseTutar = function ($s) {
            $s = str_replace(['.', ' ', 'TL', 'tl', ','], ['', '', '', '', '.'], (string) $s);
            return (float) preg_replace('/[^0-9.]/', '', $s);
        };
        $trKey = function ($s) {
            $s = mb_strtolower((string) $s, 'UTF-8');
            $s = strtr($s, [
                'ı' => 'i', 'İ' => 'i', 'ş' => 's', 'ğ' => 'g',
                'ü' => 'u', 'ö' => 'o', 'ç' => 'c',
            ]);
            $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
            return trim($s);
        };
        $ymMap = ['ocak' => 1, 'subat' => 2, 'mart' => 3, 'nisan' => 4];

        // Odeme yontemi normalize: Excel "Nakit "/"Kredi karti "/"Havale " -> odeme_yontemi_id
        $yontemMap = function ($s) use ($trKey) {
            $k = $trKey($s);
            if (strpos($k, 'nakit') !== false) return 1;
            if (strpos($k, 'kredi') !== false || strpos($k, 'kart') !== false) return 2;
            if (strpos($k, 'havale') !== false || strpos($k, 'eft') !== false) return 3;
            return 4; // Diger
        };

        $excelRows = [];
        $header = null;
        $totalRow = 0;
        foreach ($ws->getRowIterator() as $rowIt) {
            $vals = [];
            foreach ($rowIt->getCellIterator() as $cell) {
                $vals[] = $cell->getValue();
            }
            if ($header === null) { $header = $vals; continue; }
            $totalRow++;
            if (empty($vals[0])) continue;
            $tarih = $parseDate($vals[1] ?? '');
            $tutar = $parseTutar($vals[3] ?? '');
            if (!$tarih || $tutar <= 0) continue;
            if ($from && $tarih < $from) continue;
            if ($to && $tarih > $to) continue;
            $excelRows[] = [
                'user_key' => $trKey((string) $vals[0]),
                'user_ad'  => trim((string) $vals[0]),
                'tarih'    => $tarih,
                'tutar'    => round($tutar, 2),
                'yontem'   => trim((string) ($vals[2] ?? '')),
                'yontem_id' => $yontemMap($vals[2] ?? ''),
                'kaynak'   => trim((string) ($vals[4] ?? '')),
                'hizmet'   => trim((string) ($vals[5] ?? '')),
            ];
        }
        $this->line("Excel toplam satir: $totalRow, filtreli tahsilat: " . count($excelRows));

        if (empty($excelRows)) { $this->warn('Excel\'de karsilastirilacak satir yok.'); return 0; }

        // 2) DB'de salonun TUM tahsilatlarini cek (Salonappy markerli + manuel eklenmis dahil)
        $dbQuery = \DB::table('tahsilatlar as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('t.salon_id', $salonId);
        if ($from) $dbQuery->where('t.odeme_tarihi', '>=', $from);
        if ($to)   $dbQuery->where('t.odeme_tarihi', '<=', $to);
        $dbRows = $dbQuery->select('t.id', 't.odeme_tarihi', 't.tutar', 't.odeme_yontemi_id', 'u.name', 't.notlar')->get();
        $this->line("DB salon $salonId tahsilat sayisi (tum kaynaklar): " . $dbRows->count());

        // 3) Karsilastirma: 4-lu key = trKey(isim) + tarih + tutar + odeme_yontemi_id
        $dbIndex = []; // key => [tahsilat_id, ...]
        foreach ($dbRows as $r) {
            $k = $trKey($r->name)
                . '|' . substr((string) $r->odeme_tarihi, 0, 10)
                . '|' . number_format((float) $r->tutar, 2, '.', '')
                . '|' . (int) ($r->odeme_yontemi_id ?? 0);
            if (!isset($dbIndex[$k])) $dbIndex[$k] = [];
            $dbIndex[$k][] = $r->id;
        }

        $eslesen = 0; $eksik = [];
        $dbKullanildi = []; // ayni DB satirini iki kez saymamak icin
        // 2-PASS matching: once EXACT tarih match'i tuket, sonra kalan Excel satirlari
        // icin ±1 gun tolerance. Bu sayede shift'e giden Excel satirlari, tam eslesecek
        // olan bir DB kaydini erken tuketmez.
        // Key = user_key|tarih|tutar|yontem_id
        $tryMatch = function ($userKey, $tarih, $tutar, $yontemId) use ($dbIndex, &$dbKullanildi) {
            $tt = number_format($tutar, 2, '.', '');
            $k = $userKey . '|' . $tarih . '|' . $tt . '|' . (int) $yontemId;
            if (isset($dbIndex[$k])) {
                foreach ($dbIndex[$k] as $dbId) {
                    if (!isset($dbKullanildi[$dbId])) {
                        $dbKullanildi[$dbId] = true;
                        return true;
                    }
                }
            }
            return false;
        };
        $tryMatchShift = function ($userKey, $tarih, $tutar, $yontemId) use ($dbIndex, &$dbKullanildi) {
            $tt = number_format($tutar, 2, '.', '');
            foreach ([date('Y-m-d', strtotime($tarih . ' -1 day')), date('Y-m-d', strtotime($tarih . ' +1 day'))] as $t) {
                $k = $userKey . '|' . $t . '|' . $tt . '|' . (int) $yontemId;
                if (isset($dbIndex[$k])) {
                    foreach ($dbIndex[$k] as $dbId) {
                        if (!isset($dbKullanildi[$dbId])) {
                            $dbKullanildi[$dbId] = true;
                            return true;
                        }
                    }
                }
            }
            return false;
        };
        // Pass 1: exact tarih + isim + tutar + yontem
        $kalan = [];
        foreach ($excelRows as $ex) {
            if ($tryMatch($ex['user_key'], $ex['tarih'], $ex['tutar'], $ex['yontem_id'])) { $eslesen++; }
            else { $kalan[] = $ex; }
        }
        // Pass 2: ±1 gun shift (isim + tutar + yontem korunur)
        foreach ($kalan as $ex) {
            if ($tryMatchShift($ex['user_key'], $ex['tarih'], $ex['tutar'], $ex['yontem_id'])) { $eslesen++; }
            else { $eksik[] = $ex; }
        }

        $this->info("Eslesen: $eslesen, EKSIK (Excel'de var, DB'de yok): " . count($eksik));

        // 4) CSV'ye yaz
        $csv = storage_path("app/reconcile_tahsilat_{$salonId}_" . date('Ymd_His') . '.csv');
        $fp = fopen($csv, 'w');
        fputcsv($fp, ['musteri', 'tarih', 'tutar', 'yontem', 'kaynak', 'hizmet']);
        foreach ($eksik as $ex) {
            fputcsv($fp, [$ex['user_ad'], $ex['tarih'], $ex['tutar'], $ex['yontem'], $ex['kaynak'], $ex['hizmet']]);
        }
        fclose($fp);
        $this->info("Eksik CSV: $csv");

        // 5) Ozet: kaynak bazli eksik dagilim + aylik
        $kaynakEksik = []; $aylikEksik = [];
        foreach ($eksik as $ex) {
            $kaynakEksik[$ex['kaynak']] = ($kaynakEksik[$ex['kaynak']] ?? 0) + 1;
            $ay = substr($ex['tarih'], 0, 7);
            $aylikEksik[$ay] = ($aylikEksik[$ay] ?? 0) + 1;
        }
        $this->line("\n=== Kaynak bazli eksik ===");
        foreach ($kaynakEksik as $k => $n) $this->line(sprintf('  %-30s %d', $k ?: '(bos)', $n));
        $this->line("\n=== Aylik eksik ===");
        ksort($aylikEksik);
        foreach ($aylikEksik as $ay => $n) $this->line(sprintf('  %s  %d', $ay, $n));

        // Eksik full liste (tarih azalan)
        if (!empty($eksik)) {
            usort($eksik, function ($a, $b) { return strcmp($b['tarih'], $a['tarih']); });
            $this->line("\n=== EKSIK FULL LISTE (" . count($eksik) . ") ===");
            foreach ($eksik as $ex) {
                $this->line(sprintf('  %s | %s | %s TL | %s | %s',
                    $ex['tarih'], $ex['user_ad'], $ex['tutar'], $ex['kaynak'], $ex['hizmet']));
            }
        }
        return 0;
    }

    /**
     * Belli tarih (±3 gun) + tutar (±100) araligindaki DB tahsilatlarini listele.
     * Reconcile'da "sistemde var ama eksik" gozuken tahsilati bulmak icin.
     */
    private function inspectTahsilatDetay($salonId, $tarih, $tutar)
    {
        $tFrom = date('Y-m-d', strtotime($tarih . ' -3 day'));
        $tTo   = date('Y-m-d', strtotime($tarih . ' +3 day'));
        $tuMin = max(0, $tutar - 100);
        $tuMax = $tutar + 100;
        $this->info("Salon $salonId | tarih $tFrom..$tTo | tutar $tuMin..$tuMax");
        $rows = \DB::table('tahsilatlar as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('t.salon_id', $salonId)
            ->whereBetween('t.odeme_tarihi', [$tFrom, $tTo])
            ->whereBetween('t.tutar', [$tuMin, $tuMax])
            ->select('t.id', 't.odeme_tarihi', 't.tutar', 't.odeme_yontemi_id', 't.notlar', 'u.id as uid', 'u.name', 'u.cep_telefon')
            ->orderBy('t.odeme_tarihi')->orderBy('t.id')
            ->get();
        $this->line("Bulunan tahsilat sayisi: " . $rows->count());
        foreach ($rows as $r) {
            $marker = '';
            if (preg_match('~\[salonappy[^\]]*\]~', (string) $r->notlar, $mm)) $marker = $mm[0];
            $this->line(sprintf('  t.id=%d | %s | %.2f TL | oy=%d | user#%d %s (tel:%s) | %s',
                $r->id, $r->odeme_tarihi, (float) $r->tutar, (int) $r->odeme_yontemi_id,
                $r->uid, $r->name, $r->cep_telefon, $marker));
        }
        return 0;
    }

    /**
     * Salon icin duplicate name+cep_telefon ciftlerini listele.
     * Musteri aktarim sonrasi dedup dogrulama.
     */
    private function inspectDupeMusteri($salonId)
    {
        $this->info("Salon $salonId — duplicate name+cep_telefon aranıyor...");
        // Salon portfoyundaki tum kullanicilar
        $userIds = \DB::table('musteri_portfoy')->where('salon_id', $salonId)->pluck('user_id')->all();
        $this->line("Salon portfoy user sayisi: " . count($userIds));
        if (empty($userIds)) return 0;

        $dupes = \DB::table('users')
            ->whereIn('id', $userIds)
            ->select('name', 'cep_telefon', \DB::raw('COUNT(*) as cnt'), \DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('name', 'cep_telefon')
            ->having('cnt', '>', 1)
            ->orderByDesc('cnt')
            ->get();

        $this->line("Duplicate (ayni name+cep_telefon) sayisi: " . $dupes->count());
        if ($dupes->isEmpty()) {
            $this->info('Duplicate yok.');
            return 0;
        }
        $toplam = 0;
        foreach ($dupes as $d) {
            $toplam += $d->cnt - 1;
            $this->line(sprintf('  %s | tel=%s | x%d | user_ids=%s',
                mb_substr((string) $d->name, 0, 40), $d->cep_telefon, $d->cnt, $d->ids));
        }
        $this->warn("Toplam duplicate satir (unique cifte fazla): $toplam");
        $this->line('Duplicate temizligi icin: --merge-dupe-musteri --salon=' . $salonId);
        return 0;
    }

    /**
     * Salon icin duplicate name+cep_telefon ciftlerini merge et.
     * Keeper = min(user_id) kalanlarin randevu/adisyon/tahsilat/portfoy vs keeper'a taşinir.
     */
    private function mergeDupeMusteri($salonId, $dryRun)
    {
        $this->info("Salon $salonId — duplicate merge" . ($dryRun ? ' (DRY-RUN)' : ''));
        $userIds = \DB::table('musteri_portfoy')->where('salon_id', $salonId)->pluck('user_id')->all();
        if (empty($userIds)) { $this->info('Portfoyde user yok.'); return 0; }

        $dupes = \DB::table('users')
            ->whereIn('id', $userIds)
            ->select('name', 'cep_telefon', \DB::raw('GROUP_CONCAT(id ORDER BY id ASC) as ids'), \DB::raw('COUNT(*) as cnt'))
            ->groupBy('name', 'cep_telefon')
            ->having('cnt', '>', 1)
            ->get();

        $this->line("Merge edilecek duplicate cift: " . $dupes->count());
        $toplamMerge = 0; $toplamSil = 0;
        foreach ($dupes as $d) {
            $ids = array_map('intval', explode(',', $d->ids));
            sort($ids);
            $keeper = $ids[0];
            $sil = array_slice($ids, 1);
            $this->line("  {$d->name} | tel={$d->cep_telefon} | keeper=$keeper | sil=" . implode(',', $sil));
            if ($dryRun) { $toplamSil += count($sil); continue; }

            try {
                // Bagli tablolarda user_id degistir (varsa)
                $tabloKolonlari = [
                    'randevular'         => 'user_id',
                    'adisyonlar'         => 'user_id',
                    'tahsilatlar'        => 'user_id',
                    'musteri_portfoy'    => 'user_id',
                    'musteri_notlari'    => 'user_id',
                    'kampanya_musterileri' => 'user_id',
                    'sms_gonderim_listesi' => 'user_id',
                ];
                foreach ($tabloKolonlari as $tbl => $col) {
                    if (!\Schema::hasTable($tbl) || !\Schema::hasColumn($tbl, $col)) continue;
                    \DB::table($tbl)->whereIn($col, $sil)->update([$col => $keeper]);
                }
                // musteri_portfoy'da keeper icin duplicate olabilir (silinen kayitlarin portfoyleri)
                // salon bazli tekilesir: keeper icin ayni salonda birden cok portfoy varsa fazlasini sil
                $portfoyIds = \DB::table('musteri_portfoy')->where('user_id', $keeper)
                    ->where('salon_id', $salonId)->orderBy('id')->pluck('id')->all();
                if (count($portfoyIds) > 1) {
                    $keepPortfoyId = array_shift($portfoyIds);
                    \DB::table('musteri_portfoy')->whereIn('id', $portfoyIds)->delete();
                }
                // Sil eski user'lari
                \DB::table('users')->whereIn('id', $sil)->delete();
                $toplamMerge++;
                $toplamSil += count($sil);
            } catch (\Throwable $e) {
                $this->warn("  HATA: " . $e->getMessage());
            }
        }
        $this->info(($dryRun ? 'DRY-RUN' : 'Merge tamam') . ": $toplamMerge cift, $toplamSil user silindi.");
        return 0;
    }

    private function fixRandevuHizmetSaat($salonId, $dryRun)
    {
        $this->info("Salon {$salonId}: RH.saat NULL kayitlari tamir ediliyor" . ($dryRun ? ' (DRY-RUN)' : '') . '...');
        $rRows = \DB::table('randevular')->where('salon_id', $salonId)
            ->where(function ($q) {
                $q->where('personel_notu', 'LIKE', '%[salonappy:%')
                  ->orWhere('personel_notu', 'LIKE', '%[salonappy-visit:%');
            })
            ->select('id', 'saat')
            ->get();
        $this->line("Markerli randevu sayisi: " . $rRows->count());

        $totalRh = 0; $guncellenen = 0; $atlananRandevu = 0;
        foreach (array_chunk($rRows->all(), 500) as $chunk) {
            $rIds = array_column($chunk, 'id');
            $rhRows = \DB::table('randevu_hizmetler')
                ->whereIn('randevu_id', $rIds)
                ->where(function ($q) {
                    $q->whereNull('saat')->orWhere('saat', '00:00:00')->orWhere('saat', '');
                })
                ->orderBy('randevu_id')->orderBy('id')
                ->get(['id', 'randevu_id', 'sure_dk']);
            // randevu_id => [rh, rh, ...] grupla
            $byRandevu = [];
            foreach ($rhRows as $rh) {
                $byRandevu[$rh->randevu_id][] = $rh;
            }
            // randevu.saat lookup
            $saatMap = [];
            foreach ($chunk as $r) $saatMap[$r->id] = $r->saat;

            foreach ($byRandevu as $rid => $rhList) {
                $baseSaat = $saatMap[$rid] ?? null;
                if (!$baseSaat || $baseSaat === '00:00:00' || strlen($baseSaat) < 5) {
                    $atlananRandevu++; continue;
                }
                if (strlen($baseSaat) === 5) $baseSaat .= ':00';
                $cumSaat = $baseSaat;
                foreach ($rhList as $rh) {
                    $totalRh++;
                    $sureDk = (int) ($rh->sure_dk ?: 30);
                    if ($sureDk < 1) $sureDk = 30;
                    $saatBitis = date('H:i:s', strtotime($cumSaat) + $sureDk * 60);
                    if (!$dryRun) {
                        \DB::table('randevu_hizmetler')->where('id', $rh->id)->update([
                            'saat' => $cumSaat,
                            'saat_bitis' => $saatBitis,
                            'sure_dk' => $sureDk,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                    $guncellenen++;
                    $cumSaat = $saatBitis;
                }
            }
        }
        $this->info("RH.saat NULL toplam: $totalRh, guncellenen: " . ($dryRun ? '(DRY-RUN)' : $guncellenen)
            . ", atlanan randevu (saat=NULL/00:00): $atlananRandevu");
        return 0;
    }

    private function resetSalonappy($salonId, $dryRun)
    {
        $tR = (new \App\Randevular)->getTable();
        $tA = (new \App\Adisyonlar)->getTable();
        $tRh = (new \App\RandevuHizmetler)->getTable();
        $tAh = (new \App\AdisyonHizmetler)->getTable();
        $tAu = (new \App\AdisyonUrunler)->getTable();
        $tT = (new \App\Tahsilatlar)->getTable();
        $tAps = (new \App\AdisyonPaketSeanslar)->getTable();

        // AGRESIF RESET: salon_id=X icin TUM transactional veri silinir
        // (randevu/adisyon/tahsilat/masraf + tum bagli detaylar).
        // Salon salonappy kaynakli oldugu icin marker check'siz salon_id bazli silme guvenli.
        // Korunanlar: musteri (users + musteri_portfoy), kurulum (hizmet/personel/urun/paket master).
        $randevuIds = \DB::table($tR)->where('salon_id', $salonId)->pluck('id')->all();
        $adisyonIds = \DB::table($tA)->where('salon_id', $salonId)->pluck('id')->all();
        $tahsilatIds = \DB::table($tT)->where('salon_id', $salonId)->pluck('id')->all();
        $masrafIds = \DB::table('masraflar')->where('salon_id', $salonId)->pluck('id')->all();
        $taksitIds = \DB::table('taksitli_tahsilatlar')->where('salon_id', $salonId)->pluck('id')->all();
        $alacakCnt = \Schema::hasTable('alacaklar') ? \DB::table('alacaklar')->where('salon_id', $salonId)->count() : 0;

        $this->line("Salon {$salonId} silinecek:");
        $this->line("  randevu: " . count($randevuIds));
        $this->line("  adisyon: " . count($adisyonIds));
        $this->line("  tahsilat: " . count($tahsilatIds));
        $this->line("  taksitli_tahsilat: " . count($taksitIds));
        $this->line("  alacak: " . $alacakCnt);
        $this->line("  masraf: " . count($masrafIds));
        if ($dryRun) { $this->warn('DRY-RUN'); return 0; }

        // Taksitli tahsilat + vadeler + alacaklar (adisyon silinmeden once FK-safe)
        if (\Schema::hasTable('alacaklar')) {
            \DB::table('alacaklar')->where('salon_id', $salonId)->delete();
        }
        if (!empty($taksitIds)) {
            foreach (array_chunk($taksitIds, 1000) as $ck) {
                \DB::table('taksit_vadeleri')->whereIn('taksitli_tahsilat_id', $ck)->delete();
                \DB::table('taksitli_tahsilatlar')->whereIn('id', $ck)->delete();
            }
        }

        // AdisyonHizmetler -> AdisyonPaketSeanslar -> AdisyonUrunler -> Adisyonlar
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
        // Masraflar
        if (!empty($masrafIds)) {
            foreach (array_chunk($masrafIds, 1000) as $ck) {
                \DB::table('masraflar')->whereIn('id', $ck)->delete();
            }
        }
        $this->info('Reset tamam. Musteri + kurulum (hizmet/personel/urun) korunur. Simdi --dump-file ile re-import yapabilirsiniz.');
        return 0;
    }

    /**
     * Salonappy /setup/services response'unu rekursif gez,
     * service_id -> TR title mapping'i biriktir.
     * Endpoint yanitinda hizmetler service_group altinda nested olabilir;
     * her nesnede 'id' + 'title' varsa map'e eklenir.
     */
    private function collectServicesMaster($node, array &$out)
    {
        if (is_array($node)) {
            // Bir hizmet objesi mi?
            if (isset($node['id']) && isset($node['title']) && is_scalar($node['title'])) {
                $id = (string) $node['id'];
                $title = trim((string) $node['title']);
                if ($id !== '' && $title !== '' && !isset($out[$id])) {
                    $out[$id] = $title;
                }
            }
            foreach ($node as $child) {
                if (is_array($child)) $this->collectServicesMaster($child, $out);
            }
        }
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
        static $salonTrKeyMap = null;  // salon-specific trKey -> hizmet_id (yuksek oncelik)
        static $globalTrKeyMap = null; // global trKey -> hizmet_id (fallback)
        static $defaultKategoriId = null;
        $needle = $this->saTrKey($ad);
        $cacheKey = $salonId . '|' . $needle;
        if (isset($cache[$cacheKey])) { $canonicalAd = $canonCache[$cacheKey] ?? $ad; return $cache[$cacheKey]; }

        // 1) Salon-specific match: salon_sunulan_hizmetler JOIN hizmetler (oncelikli)
        if ($salonTrKeyMap === null) {
            $salonTrKeyMap = [];
            $rows = \DB::table('salon_sunulan_hizmetler as sh')
                ->join('hizmetler as h', 'sh.hizmet_id', '=', 'h.id')
                ->where('sh.salon_id', $salonId)
                ->select('h.id', 'h.hizmet_adi')->get();
            foreach ($rows as $h) {
                $k = $this->saTrKey($h->hizmet_adi);
                if ($k && !isset($salonTrKeyMap[$k])) $salonTrKeyMap[$k] = $h->id;
            }
        }
        $hizmet = null;
        if (isset($salonTrKeyMap[$needle])) {
            $hizmet = \App\Hizmetler::find($salonTrKeyMap[$needle]);
        }

        // 2) Exact match (case-sensitive) global hizmetler
        if (!$hizmet) {
            $hizmet = \App\Hizmetler::where('hizmet_adi', $ad)->first();
        }
        // 3) Global trKey match (case/diacritic-insensitive)
        if (!$hizmet) {
            if ($globalTrKeyMap === null) {
                $globalTrKeyMap = [];
                foreach (\DB::table('hizmetler')->select('id','hizmet_adi')->get() as $h) {
                    $k = $this->saTrKey($h->hizmet_adi);
                    if ($k && !isset($globalTrKeyMap[$k])) $globalTrKeyMap[$k] = $h->id;
                }
            }
            if (isset($globalTrKeyMap[$needle])) {
                $hizmet = \App\Hizmetler::find($globalTrKeyMap[$needle]);
            }
        }
        if (!$hizmet) {
            try {
                $hizmet = new \App\Hizmetler();
                $hizmet->hizmet_adi = $ad;
                // Salon'un kendi mevcut bir kategorisini kullan (yoksa global ilk kategori).
                // 'Salonappy' isimli ozel kategori yaratilmaz; arayuzde gozukmesin.
                if ($defaultKategoriId === null) {
                    $defaultKategoriId = \DB::table('salon_sunulan_hizmetler')
                        ->where('salon_id', $salonId)
                        ->whereNotNull('hizmet_kategori_id')
                        ->value('hizmet_kategori_id');
                    if (!$defaultKategoriId) {
                        $defaultKategoriId = \DB::table('hizmet_kategorisi')->orderBy('id')->value('id');
                    }
                }
                if ($defaultKategoriId) $hizmet->hizmet_kategori_id = $defaultKategoriId;
                $hizmet->ozel_hizmet = true;
                if (\Schema::hasColumn('hizmetler', 'salon_id')) $hizmet->salon_id = $salonId;
                if (\Schema::hasColumn('hizmetler', 'aktif'))    $hizmet->aktif = 0;
                $hizmet->save();
                // Yeni eklenen hizmeti salon-specific map'e de ekle ki sonraki lookup'lar bulsun
                $salonTrKeyMap[$needle] = $hizmet->id;
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
                // Eslesme bulamayinca auto-create edilen tum hizmetler salon'da pasif (aktif=0).
                // Kullanici incelemeden onayladiktan sonra panelden aktif edebilir.
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
