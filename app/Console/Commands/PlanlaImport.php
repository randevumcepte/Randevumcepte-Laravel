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
        {--probe-api : Login + POST /connect-api action varyantlarini tara}
        {--analyze : Login olmadan Site.js bundle\'ini indirip icinden endpoint ve payload cikarir}
        {--dupes : Planla musterilerinde telefon mukerrer/adsiz/telefonsuz sayilarini raporla}
        {--diagnose : Salon randevularinda portfoye bagli olmayan kullanici atamalarini listele}
        {--fix-olusturan : Gecerli personele bagli olmayan olusturan_personel_id degerlerini salonun default personeline ayarla}
        {--backfill-personel-oda : Salon personellerine birebir oda olustur, mevcut randevu_hizmetler.oda_id NULL olanlari personel adi ile esleyen oda\'ya bagla}
        {--fix-sure-dk : Salon randevu_hizmetler/adisyon_hizmetler icinde 15dk altindaki sureleri 15\'e cek, saat_bitis yeniden hesapla}
        {--merge-duplicate-odalar : Salon icin trKey (case+aksan+whitespace normalize) ayni olan oda kayitlarini merge et: en eski id\'yi tut, digerlerin tum baglantilarini ona transfer + fazlalari sil}
        {--merge-prefix-odalar : Kisa+uzun ad odalari merge eder (LAZER KONTROL + lazer kontrol emine -> tek)}
        {--dry-run : fix-sure-dk vb. islemlerde sadece sayim, yazma}
        {--only= : Sadece bu tip(ler)i al (virgulle: musteri,hizmet,randevu)}';

    protected $description = 'Planla.co hesabindan musteri/hizmet/randevu verisini cekip randevumcepte DB sine aktarir.';

    public function handle()
    {
        // CLI -d max_execution_time=0 yetersiz kalabiliyor (Laravel bootstrap override edebilir)
        @set_time_limit(0);
        @ini_set('memory_limit', '2048M');
        $email    = $this->option('email');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $probe    = (bool) $this->option('probe');
        $probeApi = (bool) $this->option('probe-api');
        $dupes    = (bool) $this->option('dupes');
        $diagnose = (bool) $this->option('diagnose');
        $fixOlusturan = (bool) $this->option('fix-olusturan');
        $backfillOda  = (bool) $this->option('backfill-personel-oda');
        $fixSure      = (bool) $this->option('fix-sure-dk');
        $analyze  = (bool) $this->option('analyze');
        $only     = $this->option('only');

        $mergeDup    = (bool) $this->option('merge-duplicate-odalar');
        $mergePrefix = (bool) $this->option('merge-prefix-odalar');
        if (!$analyze && !$diagnose && !$fixOlusturan && !$backfillOda && !$fixSure && !$mergeDup && !$mergePrefix && (!$email || !$password)) {
            $this->error('--email ve --password zorunlu.');
            return 1;
        }
        if (!$probe && !$probeApi && !$dupes && !$diagnose && !$fixOlusturan && !$backfillOda && !$fixSure && !$analyze && !$salonId) {
            $this->error('Import icin --salon zorunlu.');
            return 1;
        }
        if (($diagnose || $fixOlusturan || $backfillOda || $fixSure) && !$salonId) {
            $this->error('--diagnose / --fix-olusturan / --backfill-personel-oda / --fix-sure-dk icin --salon zorunlu.');
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
            $this->line('Base URL adaylari: ' . implode(', ', $s['base_urls']));
            $this->line('Planla domain URL\'leri: ' . implode(', ', $s['planla_urls']));
            $this->line('--- Login/auth path adaylari (' . count($s['login_paths']) . ') ---');
            foreach ($s['login_paths'] as $e) $this->line('  ' . $e);
            $this->line('--- Data path adaylari (' . count($s['data_paths']) . ') ---');
            foreach ($s['data_paths'] as $e) $this->line('  ' . $e);
            $this->line('--- HTTP call (' . count($s['http_calls']) . ' adet, ilk 40) ---');
            foreach (array_slice($s['http_calls'], 0, 40) as $e) $this->line('  ' . $e);
            $this->line('--- HTTP init (axios.create, vs.) ---');
            foreach ($s['http_init'] as $e) $this->line('  ' . $e);
            $this->line('--- Protokol isaretcileri (graphql/socket/ws/v1) ---');
            foreach ($s['protocols'] as $e) $this->line('  ' . $e);
            $this->line('--- Tum path-benzeri stringler (ilk 40) ---');
            foreach (array_slice($s['all_paths'], 0, 40) as $e) $this->line('  ' . $e);
            $this->line('--- Path context\'leri (her path icin etraftaki JS kodu) ---');
            foreach ($s['path_contexts'] as $key => $ctx) {
                $this->line('### ' . $key);
                $this->line('  ' . str_replace("\n", ' ', $ctx));
            }
            $this->line('--- url:"..."+method:"..." pattern\'leri (' . count($s['url_fields']) . ') ---');
            foreach ($s['url_fields'] as $e) $this->line('  ' . $e);
            $this->line('--- operation_opts (postOptions/getOptions/mutation/query) (' . count($s['operation_opts']) . ', ilk 20) ---');
            foreach (array_slice($s['operation_opts'], 0, 20) as $e) $this->line('  ' . $e);
            $this->line('--- queryKey\'ler (' . count($s['query_keys']) . ') ---');
            foreach ($s['query_keys'] as $e) $this->line('  ' . $e);
            $this->line('--- wrapper call\'lar (useQuery/useMutation) (' . count($s['wrapper_calls']) . ', ilk 20) ---');
            foreach (array_slice($s['wrapper_calls'], 0, 20) as $e) $this->line('  ' . $e);
            $this->line('--- Keyword context\'leri (graphql/connect-api/subscription) ---');
            foreach ($s['keyword_ctx'] as $k => $ctx) {
                $this->line('### ' . $k);
                $this->line('  ' . str_replace("\n", ' ', $ctx));
            }
            $this->info('Tam analiz: ' . $client->dumpDir() . '/bundle_analysis.body');
            return 0;
        }

        if ((bool) $this->option('merge-duplicate-odalar')) {
            if (!$salonId) { $this->error('--merge-duplicate-odalar icin --salon zorunlu.'); return 1; }
            return $this->mergeDuplicateOdalar((int) $salonId, (bool) $this->option('dry-run'));
        }

        if ((bool) $this->option('merge-prefix-odalar')) {
            if (!$salonId) { $this->error('--merge-prefix-odalar icin --salon zorunlu.'); return 1; }
            return $this->mergePrefixOdalar((int) $salonId, (bool) $this->option('dry-run'));
        }

        if ($fixOlusturan) {
            $this->info("Salon {$salonId}: Planla randevularinda olusturan_personel_id'yi NULL, salon=0 yap...");
            // Planla importinin izi: ya olusturan_personel_id=0 ya da gecersiz referans
            // Guvenli kriter: salon=1 ve olusturan_personel_id salon personellerinden degil
            $salonPersIds = \App\Personeller::where('salon_id', $salonId)->pluck('id');
            $etkilenen = \App\Randevular::where('salon_id', $salonId)
                ->where(function ($q) use ($salonPersIds) {
                    $q->where('olusturan_personel_id', 0)
                      ->orWhereNull('olusturan_personel_id')
                      ->orWhereNotIn('olusturan_personel_id', $salonPersIds);
                })
                ->count();
            $this->line("Etkilenen randevu: {$etkilenen}");
            if ($etkilenen > 0) {
                \App\Randevular::where('salon_id', $salonId)
                    ->where(function ($q) use ($salonPersIds) {
                        $q->where('olusturan_personel_id', 0)
                          ->orWhereNull('olusturan_personel_id')
                          ->orWhereNotIn('olusturan_personel_id', $salonPersIds);
                    })
                    ->update(['olusturan_personel_id' => null, 'salon' => 0]);
                $this->info('Guncellendi: olusturan_personel_id=NULL, salon=0');
            }
            return 0;
        }

        if ($fixSure) {
            $dryRun = (bool) $this->option('dry-run');
            $this->info("Salon {$salonId}: 15dk altindaki randevu sureleri 15'e cekiliyor" . ($dryRun ? ' (DRY-RUN)' : '') . '...');
            $rIds = \DB::table('randevular')->where('salon_id', $salonId)->pluck('id');
            $this->line('Salon randevu sayisi: ' . $rIds->count());

            // randevu_hizmetler: 1..14 dk olanlar
            $rhEtkilenen = 0; $rhBitisGuncel = 0;
            foreach (array_chunk($rIds->all(), 1000) as $chunk) {
                $rows = \DB::table('randevu_hizmetler')
                    ->whereIn('randevu_id', $chunk)
                    ->where('sure_dk', '>', 0)->where('sure_dk', '<', 15)
                    ->select('id', 'saat', 'saat_bitis')->get();
                foreach ($rows as $rh) {
                    $rhEtkilenen++;
                    if ($dryRun) continue;
                    $upd = ['sure_dk' => 15];
                    if (!empty($rh->saat)) {
                        $upd['saat_bitis'] = date('H:i:s', strtotime('+15 minutes', strtotime($rh->saat)));
                        $rhBitisGuncel++;
                    }
                    \DB::table('randevu_hizmetler')->where('id', $rh->id)->update($upd);
                }
            }
            $this->line("randevu_hizmetler: 15dk alti = {$rhEtkilenen}" . ($dryRun ? '' : " -> guncellendi (saat_bitis: {$rhBitisGuncel})"));

            // adisyon_hizmetler: ayni randevulara bagli, sure 1..14
            $ahEtkilenen = 0;
            foreach (array_chunk($rIds->all(), 1000) as $chunk) {
                if ($dryRun) {
                    $ahEtkilenen += \DB::table('adisyon_hizmetler')
                        ->whereIn('randevu_id', $chunk)
                        ->where('sure', '>', 0)->where('sure', '<', 15)->count();
                } else {
                    $ahEtkilenen += \DB::table('adisyon_hizmetler')
                        ->whereIn('randevu_id', $chunk)
                        ->where('sure', '>', 0)->where('sure', '<', 15)
                        ->update(['sure' => 15]);
                }
            }
            $this->line("adisyon_hizmetler: 15dk alti sure = {$ahEtkilenen}" . ($dryRun ? '' : ' -> 15 yapildi'));

            // salon_sunulan_hizmetler: KOK kaynak. Bunu duzeltmezsek re-import
            // randevu_hizmetler.sure_dk'yi tekrar 1'e ceker.
            $shQuery = \DB::table('salon_sunulan_hizmetler')
                ->where('salon_id', $salonId)
                ->where('sure_dk', '>', 0)->where('sure_dk', '<', 15);
            if ($dryRun) {
                $shEtkilenen = $shQuery->count();
            } else {
                $shEtkilenen = $shQuery->update(['sure_dk' => 15]);
            }
            $this->line("salon_sunulan_hizmetler: 15dk alti sure_dk = {$shEtkilenen}" . ($dryRun ? '' : ' -> 15 yapildi (re-import guvenli)'));

            $this->info($dryRun ? 'DRY-RUN bitti, yazma yapilmadi.' : 'Sure duzeltme tamam.');
            return 0;
        }

        if ($backfillOda) {
            $this->info("Salon {$salonId}: Personeller -> Odalar eşleme + backfill...");
            $hasAktifmi = \Schema::hasColumn('odalar', 'aktifmi');
            $hasAktif = \Schema::hasColumn('odalar', 'aktif');
            $hasPersonelId = \Schema::hasColumn('odalar', 'personel_id');
            $hasTakvimSirasi = \Schema::hasColumn('odalar', 'takvim_sirasi');

            // 1) Her personel icin ayni isimde oda yoksa yarat + salon_oda_renkleri kaydi
            $personeller = \App\Personeller::where('salon_id', $salonId)->orderBy('id')->get();
            $created = 0; $existed = 0; $rengiAtanan = 0;
            $personelToOdaId = [];
            $sira = (int) (\DB::table('odalar')->where('salon_id', $salonId)
                ->max($hasTakvimSirasi ? 'takvim_sirasi' : 'id') ?? 0);
            // Salonun son oda rengi (rotate 1..10)
            $sonRenk = (int) (\DB::table('salon_oda_renkleri')->where('salon_id', $salonId)
                ->orderBy('id', 'desc')->value('renk_id') ?? 0);
            $sonrakiRenk = function () use (&$sonRenk) {
                $sonRenk = ($sonRenk <= 0 || $sonRenk >= 10) ? 1 : $sonRenk + 1;
                return $sonRenk;
            };
            foreach ($personeller as $p) {
                $ad = trim((string) $p->personel_adi);
                if ($ad === '') continue;
                $oda = \App\Odalar::where('salon_id', $salonId)->where('oda_adi', $ad)->first();
                if (!$oda) {
                    $oda = new \App\Odalar();
                    $oda->salon_id = $salonId;
                    $oda->oda_adi = $ad;
                    if ($hasPersonelId)  $oda->personel_id = $p->id;
                    if ($hasAktifmi)     $oda->aktifmi = 1;
                    if ($hasAktif)       $oda->aktif = 1;
                    if ($hasTakvimSirasi) $oda->takvim_sirasi = ++$sira;
                    $oda->save();
                    $created++;
                } else {
                    // mevcut oda var ama personel_id farkliysa eslestir (varsa)
                    if ($hasPersonelId && empty($oda->personel_id)) {
                        $oda->personel_id = $p->id;
                        $oda->save();
                    }
                    $existed++;
                }
                // Renk kaydi yoksa ekle (takvim INNER JOIN yapiyor, eksikse oda gozukmez)
                $odaRenk = \DB::table('salon_oda_renkleri')->where('oda_id', $oda->id)->first();
                if (!$odaRenk) {
                    \DB::table('salon_oda_renkleri')->insert([
                        'salon_id' => $salonId,
                        'renk_id'  => $sonrakiRenk(),
                        'oda_id'   => $oda->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $rengiAtanan++;
                }
                $personelToOdaId[$p->id] = $oda->id;
            }
            $this->line("Personel: " . count($personeller) . " | Oda yaratildi: {$created} | Mevcut: {$existed} | Renk atanan: {$rengiAtanan}");

            // 2) Mevcut randevu_hizmetler.oda_id NULL olanlara personel'den eslesen oda_id'yi yaz
            // Sadece bu salonun randevulari
            $rIds = \DB::table('randevular')->where('salon_id', $salonId)->pluck('id');
            $this->line("Salon randevu sayisi: " . $rIds->count());
            $updated = 0;
            foreach ($personelToOdaId as $persId => $odaId) {
                $n = \DB::table('randevu_hizmetler')
                    ->whereIn('randevu_id', $rIds)
                    ->where('personel_id', $persId)
                    ->whereNull('oda_id')
                    ->update(['oda_id' => $odaId]);
                $updated += $n;
            }
            $this->info("randevu_hizmetler.oda_id backfill: {$updated} satir guncellendi");
            return 0;
        }

        if ($diagnose) {
            $this->info("Salon {$salonId} randevularinda sorunlu user_id taramasi...");
            $toplam = \App\Randevular::where('salon_id', $salonId)->count();
            $this->line("Toplam randevu: {$toplam}");

            $orphanCount = \App\Randevular::where('salon_id', $salonId)
                ->leftJoin('users', 'randevular.user_id', '=', 'users.id')
                ->whereNull('users.id')->count();
            $orphan = \App\Randevular::where('salon_id', $salonId)
                ->leftJoin('users', 'randevular.user_id', '=', 'users.id')
                ->whereNull('users.id')
                ->select('randevular.id', 'randevular.user_id', 'randevular.tarih', 'randevular.saat')
                ->limit(20)->get();
            $this->line("\n[A] user_id users'ta YOK (orphan) - toplam: {$orphanCount} (ilk 20):");
            foreach ($orphan as $r) {
                $this->line("  randevu_id={$r->id} user_id={$r->user_id} tarih={$r->tarih} saat={$r->saat}");
            }

            $portfoysuzCount = \App\Randevular::where('randevular.salon_id', $salonId)
                ->join('users', 'randevular.user_id', '=', 'users.id')
                ->leftJoin('musteri_portfoy', function ($j) use ($salonId) {
                    $j->on('musteri_portfoy.user_id', '=', 'users.id')
                      ->where('musteri_portfoy.salon_id', '=', $salonId);
                })
                ->whereNull('musteri_portfoy.id')->count();
            $portfoysuz = \App\Randevular::where('randevular.salon_id', $salonId)
                ->join('users', 'randevular.user_id', '=', 'users.id')
                ->leftJoin('musteri_portfoy', function ($j) use ($salonId) {
                    $j->on('musteri_portfoy.user_id', '=', 'users.id')
                      ->where('musteri_portfoy.salon_id', '=', $salonId);
                })
                ->whereNull('musteri_portfoy.id')
                ->select('randevular.id', 'randevular.user_id', 'users.name', 'users.cep_telefon')
                ->limit(20)->get();
            $this->line("\n[B] user var ama bu salonun portfoyunde YOK - toplam: {$portfoysuzCount} (ilk 20):");
            foreach ($portfoysuz as $r) {
                $this->line("  randevu_id={$r->id} user_id={$r->user_id} name={$r->name} tel={$r->cep_telefon}");
            }
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
            $this->info('Probe tamam.');
            return 0;
        }

        if ($probeApi) {
            $this->info('POST /connect-api action varyantlari taraniyor...');
            $results = $client->probeConnectApi();
            foreach ($results as $a => $r) {
                $this->line(str_pad($a, 30) . ' -> ' . $r);
            }
            $this->info('connect-api probe tamam.');
            return 0;
        }

        if ($dupes) {
            $this->info('Musteriler cekiliyor (category=customers)...');
            $resp = $client->connectApi('customers');
            $data = isset($resp['data']) && is_array($resp['data']) ? $resp['data'] : [];
            $this->line('Toplam Planla musteri: ' . count($data));
            $telMap = []; $telsiz = 0; $adsiz = 0; $idsiz = 0;
            foreach ($data as $r) {
                if (empty($r['_id'])) { $idsiz++; continue; }
                $tel = preg_replace('/[^0-9]/', '', isset($r['phone']) ? (string) $r['phone'] : '');
                $tel = preg_replace('/^90/', '', $tel);
                $tel = preg_replace('/^0/', '', $tel);
                $ad = isset($r['fullName']) ? trim($r['fullName']) : '';
                if (!$tel) $telsiz++;
                if (!$ad) $adsiz++;
                if ($tel) $telMap[$tel] = (isset($telMap[$tel]) ? $telMap[$tel] : 0) + 1;
            }
            $this->line('_id bos: ' . $idsiz);
            $this->line('Telefonsuz: ' . $telsiz);
            $this->line('Adsiz: ' . $adsiz);
            $dupTel = array_filter($telMap, function ($c) { return $c > 1; });
            $this->line('Unique telefon sayisi: ' . count($telMap));
            $this->line('Mukerrer telefonlu kaynak kayit adedi: ' . count($dupTel));
            $this->line('Mukerrer nedeniyle uretilen fazlalik: ' . (array_sum($dupTel) - count($dupTel)));
            $ilk5 = array_slice($dupTel, 0, 10, true);
            foreach ($ilk5 as $t => $c) $this->line("  tel={$t} -> {$c} kayit");
            return 0;
        }

        $types = $only ? array_map('trim', explode(',', $only)) : ['personel', 'hizmet', 'musteri', 'randevu', 'tahsilat'];
        $importer = new PlanlaImporter($client, $salonId, $this->output);

        // Sirayla: personel -> hizmet -> musteri -> randevu -> tahsilat (map bagimliliklari)
        if (in_array('personel', $types)) $importer->importPersoneller();
        if (in_array('hizmet', $types))   $importer->importHizmetler();
        if (in_array('musteri', $types))  $importer->importMusteriler();
        if (in_array('randevu', $types))  $importer->importRandevular();
        if (in_array('tahsilat', $types)) $importer->importTahsilatlar();

        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }

    /**
     * Salon icin "kisa ad" (canonical) + "uzun ad (kisa adin prefix'i)" oda
     * kayitlarini tek odaya merge eder. Orn:
     *   "LAZER KONTROL"       (canonical, kisa)
     *   "lazer kontrol emine" (uzun, kisa olanin prefix'i)
     *   "lazer kontrol buse"  (uzun, ayni grup)
     *   -> Hepsi tek "LAZER KONTROL" odasina merge.
     *
     * Algorithm:
     *   1. Tum oda_adlarini trKey ile normalize et + kelimelerine bol
     *   2. Her oda icin "olasi parent" bul: kelime sayisi daha az olan ve
     *      bu odanin baslangic kelimeleriyle BIREBIR eslesen oda
     *   3. Parent varsa: child'in tum baglantilarini parent'a aktar + child'i sil
     */
    private function mergePrefixOdalar($salonId, $dryRun)
    {
        $odalar = \DB::table('odalar')->where('salon_id', $salonId)
            ->select('id', 'oda_adi', 'personel_id')->orderBy('id')->get();

        $trKey = function ($s) {
            $s = mb_strtolower((string) $s, 'UTF-8');
            $s = preg_replace('/\p{M}+/u', '', $s);
            $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
            return trim(preg_replace('~[^a-z0-9]+~', ' ', $s));
        };

        // Her oda icin: id, normalize kelimeler, kelime sayisi
        $items = [];
        foreach ($odalar as $o) {
            $norm = $trKey($o->oda_adi);
            $words = $norm === '' ? [] : explode(' ', $norm);
            $items[] = ['id' => $o->id, 'ad' => $o->oda_adi, 'norm' => $norm, 'words' => $words, 'wc' => count($words)];
        }

        // Parent map: child_id => parent_id
        $parentMap = []; // child_id => parent_id
        foreach ($items as $child) {
            if ($child['wc'] < 2) continue; // 1 kelimelik tek baslarina kalsin
            $bestParent = null; $bestWc = 0;
            foreach ($items as $parent) {
                if ($parent['id'] === $child['id']) continue;
                if ($parent['wc'] >= $child['wc']) continue; // child uzun, parent kisa olmali
                // parent kelimeleri child'in basinda BIREBIR eslesmeli
                $match = true;
                for ($i = 0; $i < $parent['wc']; $i++) {
                    if (($child['words'][$i] ?? '') !== $parent['words'][$i]) { $match = false; break; }
                }
                if ($match && $parent['wc'] > $bestWc) {
                    $bestParent = $parent['id'];
                    $bestWc = $parent['wc'];
                }
            }
            if ($bestParent !== null) $parentMap[$child['id']] = $bestParent;
        }

        if (empty($parentMap)) { $this->info('Prefix-match oda yok.'); return 0; }

        // Parent → child listesi (gosterim icin)
        $groupByParent = [];
        foreach ($parentMap as $childId => $parentId) $groupByParent[$parentId][] = $childId;

        $this->line('--- Prefix-match merge plani ---');
        foreach ($groupByParent as $parentId => $children) {
            $parentAd = array_values(array_filter($items, function ($x) use ($parentId) { return $x['id'] === $parentId; }))[0]['ad'];
            $this->line("  Parent: id=$parentId \"$parentAd\"");
            foreach ($children as $cid) {
                $cAd = array_values(array_filter($items, function ($x) use ($cid) { return $x['id'] === $cid; }))[0]['ad'];
                $this->line("    -> child id=$cid \"$cAd\"");
            }
        }
        $this->line('Toplam: ' . count($parentMap) . ' child oda parent\'a merge edilecek');

        if ($dryRun) { $this->warn('DRY-RUN: silme/transfer yapilmadi.'); return 0; }

        $rhHasOda  = \Schema::hasColumn('randevu_hizmetler', 'oda_id');
        $ahHasOda  = \Schema::hasColumn('adisyon_hizmetler', 'oda_id');
        $persHasOda = \Schema::hasColumn('salon_personelleri', 'oda_id');
        $renkExists = \Schema::hasTable('salon_oda_renkleri');

        $silinen = 0; $aktarilan = 0;
        \DB::beginTransaction();
        try {
            foreach ($groupByParent as $parentId => $children) {
                if ($rhHasOda)  $aktarilan += \DB::table('randevu_hizmetler')->whereIn('oda_id', $children)->update(['oda_id' => $parentId]);
                if ($ahHasOda)  $aktarilan += \DB::table('adisyon_hizmetler')->whereIn('oda_id', $children)->update(['oda_id' => $parentId]);
                if ($persHasOda) $aktarilan += \DB::table('salon_personelleri')->whereIn('oda_id', $children)->update(['oda_id' => $parentId]);
                if ($renkExists) \DB::table('salon_oda_renkleri')->whereIn('oda_id', $children)->delete();
                \DB::table('odalar')->whereIn('id', $children)->delete();
                $silinen += count($children);
            }
            \DB::commit();
            $this->info("Merge tamam: $silinen oda silindi, $aktarilan baglanti aktarildi.");
        } catch (\Throwable $e) {
            \DB::rollBack();
            $this->error('Hata: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }

    /**
     * Salon icin ayni isimli (case+whitespace normalize) oda kayitlarini merge eder.
     * En eski id'yi tutar, digerlerin bagliliklarini ona transfer eder, fazlalari siler.
     * Transfer edilen baglilik: randevu_hizmetler.oda_id, adisyon_hizmetler.oda_id (varsa),
     * salon_oda_renkleri.oda_id, personeller.oda_id (varsa).
     */
    private function mergeDuplicateOdalar($salonId, $dryRun)
    {
        $odalar = \DB::table('odalar')->where('salon_id', $salonId)
            ->select('id', 'oda_adi', 'personel_id', 'created_at')->orderBy('id')->get();
        $norm = function ($s) {
            return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $s)), 'UTF-8');
        };
        $g = [];
        foreach ($odalar as $o) $g[$norm($o->oda_adi)][] = $o;
        $dups = array_filter($g, function ($x) { return count($x) > 1; });
        $this->line('Toplam oda: ' . count($odalar) . ', duplicate grup: ' . count($dups));

        if (empty($dups)) { $this->info('Duplicate yok.'); return 0; }

        $silinecek = 0; $aktarilan = 0;
        $renkTblExists = \Schema::hasTable('salon_oda_renkleri');
        $rhHasOda = \Schema::hasColumn('randevu_hizmetler', 'oda_id');
        $ahHasOda = \Schema::hasColumn('adisyon_hizmetler', 'oda_id');
        // Tablo adi: salon_personelleri (model App\Personeller -> 'salon_personelleri')
        $persHasOda = \Schema::hasColumn('salon_personelleri', 'oda_id');

        foreach ($dups as $key => $arr) {
            // AKILLI secim: pasif (personel_id NULL) yerine personel'i olan tut.
            // Esit ise en cok RH bagli olani tut. Es ise en eski id'ye dus.
            $secim = [];
            foreach ($arr as $o) {
                $rhCnt = $rhHasOda ? \DB::table('randevu_hizmetler')->where('oda_id', $o->id)->count() : 0;
                $secim[] = ['o' => $o, 'aktif' => !empty($o->personel_id), 'rh' => $rhCnt];
            }
            usort($secim, function ($a, $b) {
                if ($a['aktif'] !== $b['aktif']) return $b['aktif'] - $a['aktif']; // aktif onde
                if ($a['rh'] !== $b['rh']) return $b['rh'] - $a['rh']; // cok RH onde
                return $a['o']->id - $b['o']->id; // en eski onde
            });
            $tut = $secim[0]['o'];
            $silIds = [];
            foreach (array_slice($secim, 1) as $s) $silIds[] = $s['o']->id;
            $this->line("  [$key] tut id={$tut->id} (pers=" . ($tut->personel_id ?? 'NULL') . ", rh={$secim[0]['rh']}), sil: " . implode(',', $silIds));
            $silinecek += count($silIds);
            if ($dryRun) continue;

            // Bagliliklari transfer
            if ($rhHasOda) {
                $aktarilan += \DB::table('randevu_hizmetler')->whereIn('oda_id', $silIds)
                    ->update(['oda_id' => $tut->id]);
            }
            if ($ahHasOda) {
                $aktarilan += \DB::table('adisyon_hizmetler')->whereIn('oda_id', $silIds)
                    ->update(['oda_id' => $tut->id]);
            }
            if ($persHasOda) {
                $aktarilan += \DB::table('salon_personelleri')->whereIn('oda_id', $silIds)
                    ->update(['oda_id' => $tut->id]);
            }
            if ($renkTblExists) {
                // Silinecek odalardaki renk kayitlari sil (tut'unki yoksa olustur)
                \DB::table('salon_oda_renkleri')->whereIn('oda_id', $silIds)->delete();
            }
            // Odalari sil
            \DB::table('odalar')->whereIn('id', $silIds)->delete();
        }

        $this->line('--- Sonuc ---');
        $this->line('  silinecek/silinen oda : ' . $silinecek);
        $this->line('  aktarilan baglanti     : ' . $aktarilan);
        if ($dryRun) $this->warn('DRY-RUN: silme/transfer yapilmadi.');
        else $this->info('Merge tamam.');
        return 0;
    }
}
