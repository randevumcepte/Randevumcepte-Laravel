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
        {--only= : Sadece bu tip(ler)i al (virgulle: musteri,hizmet,randevu)}';

    protected $description = 'Planla.co hesabindan musteri/hizmet/randevu verisini cekip randevumcepte DB sine aktarir.';

    public function handle()
    {
        $email    = $this->option('email');
        $password = $this->option('password');
        $salonId  = $this->option('salon');
        $probe    = (bool) $this->option('probe');
        $probeApi = (bool) $this->option('probe-api');
        $dupes    = (bool) $this->option('dupes');
        $diagnose = (bool) $this->option('diagnose');
        $fixOlusturan = (bool) $this->option('fix-olusturan');
        $analyze  = (bool) $this->option('analyze');
        $only     = $this->option('only');

        if (!$analyze && !$diagnose && !$fixOlusturan && (!$email || !$password)) {
            $this->error('--email ve --password zorunlu.');
            return 1;
        }
        if (!$probe && !$probeApi && !$dupes && !$diagnose && !$fixOlusturan && !$analyze && !$salonId) {
            $this->error('Import icin --salon zorunlu.');
            return 1;
        }
        if (($diagnose || $fixOlusturan) && !$salonId) {
            $this->error('--diagnose / --fix-olusturan icin --salon zorunlu.');
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

        if ($fixOlusturan) {
            $this->info("Salon {$salonId} randevularinda bozuk olusturan_personel_id taranir + duzeltilir...");
            $default = \App\Personeller::where('salon_id', $salonId)->where('aktif', 1)->orderBy('id')->value('id');
            if (!$default) $default = \App\Personeller::where('salon_id', $salonId)->orderBy('id')->value('id');
            if (!$default) { $this->error('Salonda personel yok'); return 1; }
            $this->line("Default personel_id: {$default}");

            // olusturan_personel_id gecerli personel degil
            $bozuk = \App\Randevular::where('randevular.salon_id', $salonId)
                ->whereNotNull('randevular.olusturan_personel_id')
                ->where('randevular.olusturan_personel_id', '!=', 0)
                ->leftJoin('personeller', 'personeller.id', '=', 'randevular.olusturan_personel_id')
                ->whereNull('personeller.id')
                ->pluck('randevular.id');
            $this->line("Bozuk referansli randevu sayisi: " . $bozuk->count());

            // Sifir veya null olanlar da default'a donsun (view 'salon=1 && !== null' kontrolu var)
            $nullOrZero = \App\Randevular::where('salon_id', $salonId)
                ->where(function ($q) {
                    $q->whereNull('olusturan_personel_id')->orWhere('olusturan_personel_id', 0);
                })->pluck('id');
            $this->line("NULL/0 olanlar: " . $nullOrZero->count());

            $hepsi = $bozuk->merge($nullOrZero)->unique();
            $this->line("Toplam guncellenecek: " . $hepsi->count());
            if ($hepsi->count() > 0) {
                \App\Randevular::whereIn('id', $hepsi)->update(['olusturan_personel_id' => $default]);
                $this->info("Guncellendi.");
            }
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

        $types = $only ? array_map('trim', explode(',', $only)) : ['personel', 'hizmet', 'musteri', 'randevu'];
        $importer = new PlanlaImporter($client, $salonId, $this->output);

        // Sirayla: personel -> hizmet -> musteri -> randevu (map bagimliliklari)
        if (in_array('personel', $types)) $importer->importPersoneller();
        if (in_array('hizmet', $types))   $importer->importHizmetler();
        if (in_array('musteri', $types))  $importer->importMusteriler();
        if (in_array('randevu', $types))  $importer->importRandevular();

        $this->info('Tamam. Ozet: ' . json_encode($importer->summary()));
        return 0;
    }
}
