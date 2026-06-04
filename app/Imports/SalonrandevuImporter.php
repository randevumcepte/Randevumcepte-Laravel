<?php

namespace App\Imports;

use App\Services\SalonrandevuClient;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\Personeller;
use App\IsletmeYetkilileri;
use App\Randevular;
use App\RandevuHizmetler;
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\AdisyonUrunler;
use App\Tahsilatlar;
use App\Urunler;
use App\Paketler;
use App\PaketHizmetler;
use App\AdisyonPaketler;
use App\AdisyonPaketSeanslar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * app.salonrandevu.com -> randevumcepte aktarici.
 *
 * Marker'lar (idempotent):
 *   randevular.personel_notu : [salonrandevu-rdv:APPT_ID]
 *   adisyonlar               : [salonrandevu:RECEIPT_ID]
 *   tahsilatlar.notlar       : [salonrandevu:RECEIPT_ID]
 */
class SalonrandevuImporter
{
    /** @var SalonrandevuClient */ private $client;
    /** @var int */ private $salonId;
    private $out;

    private $musteriMap = [];   // sr customer id -> users.id
    private $hizmetMap = [];     // sr service id -> hizmetler.id
    private $packetMaster = null; // packet_id => [service_id => period]
    private $personelMap = [];   // sr staff id -> personeller.id
    private $urunMap = [];        // sr stock id -> urunler.id

    private $counts = ['personel' => 0, 'hizmet' => 0, 'urun' => 0, 'musteri' => 0,
                       'randevu' => 0, 'randevu_dedup' => 0, 'adisyon' => 0,
                       'tahsilat' => 0, 'gider' => 0, 'skip' => 0, 'hata' => 0];

    public function __construct(SalonrandevuClient $client, $salonId, $output = null)
    {
        $this->client = $client;
        $this->salonId = (int) $salonId;
        $this->out = $output;
    }

    public function summary() { return $this->counts; }

    /**
     * --report-package-sales: Salonrandevu /receipts/packets ile bizdeki paket adisyonlarini karsilastir.
     * Hicbir DB degisikligi yapmaz; sadece sayim+eksik liste.
     */
    public function reportPackageSales()
    {
        if (!$this->client->getToken()) {
            $login = $this->client->login();
            if (!$login['ok']) { $this->log('Login fail: ' . $login['detail']); return; }
        }
        $srRecords = [];
        $page = 1;
        while (true) {
            $j = $this->client->get('/company/receipts/packets', ['page' => $page, 'order' => 0]);
            $records = $j['data']['records'] ?? [];
            if (empty($records)) break;
            foreach ($records as $r) {
                $rid = (int) ($r['id'] ?? 0);
                if (!$rid) continue;
                $srRecords[$rid] = [
                    'all_amount' => (float) ($r['all_amount'] ?? 0),
                    'paid' => (float) ($r['paid'] ?? 0),
                    'debt' => (float) ($r['debt'] ?? 0),
                    'full_name' => trim((string) ($r['full_name'] ?? '')),
                    'info' => trim((string) ($r['info'] ?? '')),
                    'created_at' => $r['created_at'] ?? '',
                ];
            }
            $next = (int) ($j['data']['next_page'] ?? 0);
            if (!$next || $next === $page) break;
            $page = $next;
        }
        $srSayi = count($srRecords);
        $srTutar = array_sum(array_column($srRecords, 'all_amount'));
        $srOdenen = array_sum(array_column($srRecords, 'paid'));
        $this->log("Salonrandevu paket fis sayisi: $srSayi");
        $this->log("Salonrandevu toplam tutar: $srTutar TL (odenen: $srOdenen TL)");

        // Bizdeki: [salonrandevu:rid] markerli adisyonlarin AdisyonPaketler'i
        $adisyonTable = (new Adisyonlar)->getTable();
        $markerCol = null;
        foreach (['aciklama', 'adisyon_notu', 'genel_aciklama', 'notlar', 'not'] as $col) {
            if (\Schema::hasColumn($adisyonTable, $col)) { $markerCol = $col; break; }
        }
        if (!$markerCol) { $this->log('Marker kolonu yok.'); return; }

        $bizdekiRids = [];
        $bizdekiTutar = 0.0;
        $rows = DB::table($adisyonTable)->where('salon_id', $this->salonId)
            ->where($markerCol, 'LIKE', '%[salonrandevu:%')
            ->get(['id', $markerCol]);
        foreach ($rows as $r) {
            if (!preg_match('~\[salonrandevu:(\d+)\]~', (string) $r->{$markerCol}, $mm)) continue;
            $rid = (int) $mm[1];
            $apFiyat = DB::table('adisyon_paketler')->where('adisyon_id', $r->id)->sum('fiyat');
            if ($apFiyat > 0) {
                $bizdekiRids[$rid] = (float) $apFiyat;
                $bizdekiTutar += (float) $apFiyat;
            }
        }
        $bizdekiSayi = count($bizdekiRids);
        $this->log("Bizdeki paket adisyon sayisi (AdisyonPaketler dolu): $bizdekiSayi");
        $this->log("Bizdeki toplam tutar: $bizdekiTutar TL");

        $eksikler = array_diff_key($srRecords, $bizdekiRids);
        $fazlalar = array_diff_key($bizdekiRids, $srRecords);
        $eksikTutar = array_sum(array_column($eksikler, 'all_amount'));
        $this->log("DB'de EKSIK olan (Salonrandevu'da var, bizde yok): " . count($eksikler) . " (toplam $eksikTutar TL)");
        $this->log("DB'de FAZLA olan (bizde var, Salonrandevu'da yok): " . count($fazlalar));

        if (!empty($eksikler)) {
            $this->log("\n=== EKSIK paket fisleri (ilk 30) ===");
            $i = 0;
            foreach ($eksikler as $rid => $r) {
                if ($i++ >= 30) break;
                $this->log("  #$rid {$r['created_at']} | {$r['full_name']} | {$r['all_amount']} TL | {$r['info']}");
            }
        }
        $this->log("\nOzet: SR=$srSayi DB=$bizdekiSayi eksik=" . count($eksikler) . " fazla=" . count($fazlalar));
    }

    /**
     * --only-package-sales: /company/receipts/packets paginated tara, her receipt id icin
     * /company/receipt/{id} detayi cek, paket akisini UPSERT'le yaz. Hizmet/urun/tahsilat YAZILMAZ.
     * (Adim 1: paket satislari ayri akis — sonraki adimlar: tahsilat, hizmet, randevu, gider.)
     */
    public function importPackageSalesOnly()
    {
        if (!$this->client->getToken()) {
            $login = $this->client->login();
            if (!$login['ok']) { $this->log('Login fail: ' . $login['detail']); return; }
        }
        // Master ön yükleme yok — receipt detayindaki customer'dan müşteri inline yaratilir,
        // hizmet ve personel ensureHizmet/ensurePersonel ile inline.

        $page = 1; $toplam = 0;
        while (true) {
            $j = $this->client->get('/company/receipts/packets', ['page' => $page, 'order' => 0]);
            $records = $j['data']['records'] ?? [];
            if (empty($records)) break;
            $this->log("Paket fis sayfasi $page: " . count($records) . ' kayit');
            foreach ($records as $rec) {
                $rid = (int) ($rec['id'] ?? 0);
                if (!$rid) continue;
                $this->importOneReceipt($rid, ['package_only' => true, 'rec' => $rec]);
                $toplam++;
            }
            $next = (int) ($j['data']['next_page'] ?? 0);
            if (!$next || $next === $page) break;
            $page = $next;
        }
        $this->log("Paket satislari tamamlandi: $toplam fis islendi.");
    }
    private function log($m) { if ($this->out) $this->out->writeln($m); }

    // ======================= YARDIMCILAR =======================

    private function trKey($s)
    {
        $s = mb_strtolower((string) $s, 'UTF-8');
        $s = preg_replace('/\p{M}+/u', '', $s);
        $s = strtr($s, ['ı'=>'i','İ'=>'i','ş'=>'s','Ş'=>'s','ğ'=>'g','Ğ'=>'g','ü'=>'u','Ü'=>'u','ö'=>'o','Ö'=>'o','ç'=>'c','Ç'=>'c']);
        $s = preg_replace('~[^a-z0-9]+~', ' ', $s);
        return trim($s);
    }

    private function telNormalize($tel)
    {
        if (!$tel) return null;
        $tel = preg_replace('/[^0-9]/', '', (string) $tel);
        $tel = preg_replace('/^90/', '', $tel);
        $tel = preg_replace('/^0/', '', $tel);
        return $tel ?: null;
    }

    /** ISO "2027-01-13T09:30:00+03:00" -> ['2027-01-13','09:30:00'] */
    private function isoBol($iso)
    {
        if (!$iso) return [null, null];
        $t = strtotime($iso);
        if ($t === false) return [null, null];
        return [date('Y-m-d', $t), date('H:i:s', $t)];
    }

    // ======================= PERSONEL =======================

    public function importPersoneller()
    {
        $this->log('Personel cekiliyor (/company/staffs/unsafe)...');
        $j = $this->client->get('/company/staffs/unsafe');
        $list = $j['data'] ?? [];
        foreach ($list as $row) {
            $srId = $row['id'] ?? null;
            $ad = trim(($row['name'] ?? '') . ' ' . ($row['surname'] ?? ''));
            if ($ad === '') $ad = $row['full_name'] ?? '';
            if ($ad === '' || !$srId) continue;
            $pid = $this->ensurePersonel($ad, $row['detail']['phone'] ?? null);
            if ($pid) { $this->personelMap[$srId] = $pid; $this->counts['personel']++; }
        }
        $this->log('Personel: ' . $this->counts['personel']);
    }

    private function ensurePersonel($ad, $tel = null)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        $key = $this->trKey($ad);
        if (isset($cache[$key])) return $cache[$key];

        $p = Personeller::where('salon_id', $this->salonId)->where('personel_adi', $ad)->first();
        if (!$p) {
            foreach (Personeller::where('salon_id', $this->salonId)->select('id', 'personel_adi')->get() as $row) {
                if ($this->trKey($row->personel_adi) === $key) { $p = Personeller::find($row->id); break; }
            }
        }
        if (!$p) {
            try {
                $yetkili = new IsletmeYetkilileri();
                $yetkili->name = $ad;
                if ($tel) $yetkili->gsm1 = $this->telNormalize($tel);
                $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $yetkili->password = Hash::make(Str::random(10));
                $yetkili->aktif = 1;
                $yetkili->save();

                $sonSira = Personeller::where('salon_id', $this->salonId)->max('takvim_sirasi');
                $sonRenk = Personeller::where('salon_id', $this->salonId)->orderBy('id', 'desc')->value('renk');
                $renk = (!$sonRenk || $sonRenk >= 10) ? 1 : $sonRenk + 1;

                $p = new Personeller();
                $p->personel_adi = $ad;
                if ($tel) $p->cep_telefon = $this->telNormalize($tel);
                $p->salon_id = $this->salonId;
                $p->yetkili_id = $yetkili->id;
                $p->role_id = 5;
                $p->aktif = 1;
                $p->takvimde_gorunsun = 1;
                $p->takvim_sirasi = ($sonSira ?: 0) + 1;
                $p->renk = $renk;
                $p->save();
                DB::insert('INSERT INTO model_has_roles (role_id, model_type, model_id, salon_id) VALUES (?, ?, ?, ?)',
                    [5, 'App\\IsletmeYetkilileri', $yetkili->id, $this->salonId]);
            } catch (\Throwable $e) {
                \Log::warning('[Salonrandevu] personel', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        $cache[$key] = $p->id;
        return $p->id;
    }

    // ======================= HIZMET =======================

    public function importHizmetler()
    {
        $this->log('Hizmetler cekiliyor (/company/services/filter?key=&paginate=1)...');
        $j = $this->client->get('/company/services/filter?key=&paginate=1');
        $list = $j['data'] ?? [];
        foreach ($list as $row) {
            $srId = $row['id'] ?? null;
            $ad = trim((string) ($row['name'] ?? ''));
            if (!$srId || $ad === '') continue;
            $hid = $this->ensureHizmet($ad, (int) ($row['process_time'] ?? 30), (float) ($row['amount'] ?? 0), $row['category_name'] ?? null);
            if ($hid) { $this->hizmetMap[$srId] = $hid; $this->counts['hizmet']++; }
        }
        $this->log('Hizmet: ' . $this->counts['hizmet']);
    }

    private function ensureHizmet($ad, $sureDk = 30, $fiyat = 0, $kategoriAd = null)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        static $trMap = null;
        $key = $this->trKey($ad);
        $ck = $this->salonId . '|' . $key;
        if (isset($cache[$ck])) return $cache[$ck];

        // Salon-spesifik once
        if ($trMap === null) {
            $trMap = [];
            $rows = DB::table('salon_sunulan_hizmetler as sh')
                ->join('hizmetler as h', 'sh.hizmet_id', '=', 'h.id')
                ->where('sh.salon_id', $this->salonId)
                ->select('h.id', 'h.hizmet_adi')->get();
            foreach ($rows as $h) {
                $k = $this->trKey($h->hizmet_adi);
                if ($k && !isset($trMap[$k])) $trMap[$k] = $h->id;
            }
        }
        $hizmet = null;
        if (isset($trMap[$key])) $hizmet = Hizmetler::find($trMap[$key]);
        if (!$hizmet) $hizmet = Hizmetler::where('hizmet_adi', $ad)->first();
        if (!$hizmet) {
            // global trKey
            foreach (DB::table('hizmetler')->select('id', 'hizmet_adi')->get() as $h) {
                if ($this->trKey($h->hizmet_adi) === $key) { $hizmet = Hizmetler::find($h->id); break; }
            }
        }
        if (!$hizmet) {
            try {
                $hizmet = new Hizmetler();
                $hizmet->hizmet_adi = $ad;
                $kategoriId = $this->ensureKategori($kategoriAd);
                if ($kategoriId) $hizmet->hizmet_kategori_id = $kategoriId;
                $hizmet->ozel_hizmet = true;
                if (\Schema::hasColumn('hizmetler', 'salon_id')) $hizmet->salon_id = $this->salonId;
                if (\Schema::hasColumn('hizmetler', 'aktif')) $hizmet->aktif = 0;
                $hizmet->save();
                $trMap[$key] = $hizmet->id;
            } catch (\Throwable $e) {
                \Log::warning('[Salonrandevu] hizmet', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        // SalonHizmet kayit
        $sh = SalonHizmetler::where('salon_id', $this->salonId)->where('hizmet_id', $hizmet->id)->first();
        if (!$sh) {
            try {
                $sh = new SalonHizmetler();
                $sh->salon_id = $this->salonId;
                $sh->hizmet_id = $hizmet->id;
                $sh->hizmet_kategori_id = $hizmet->hizmet_kategori_id;
                $sh->aktif = 0;
                $sh->bolum = 2;
                $sh->sure_dk = $sureDk >= 15 ? $sureDk : 15;
                $sh->baslangic_fiyat = $fiyat;
                $sh->son_fiyat = $fiyat;
                $sh->save();
            } catch (\Throwable $e) {}
        }
        $cache[$ck] = $hizmet->id;
        return $hizmet->id;
    }

    private function ensureKategori($ad)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        if (isset($cache[$ad])) return $cache[$ad];
        $kat = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $ad)->first();
        if (!$kat) {
            try {
                $kat = new Hizmet_Kategorisi();
                $kat->hizmet_kategorisi_adi = $ad;
                $kat->save();
            } catch (\Throwable $e) { return null; }
        }
        $cache[$ad] = $kat->id;
        return $kat->id;
    }

    // ======================= URUN =======================

    public function importUrunler()
    {
        $this->log('Urunler cekiliyor (/company/stock/items/notpag)...');
        $j = $this->client->get('/company/stock/items/notpag');
        $list = $j['data'] ?? [];
        foreach ($list as $row) {
            $srId = $row['id'] ?? null;
            $ad = trim((string) ($row['name'] ?? ''));
            if (!$srId || $ad === '') continue;
            $uid = $this->ensureUrun($ad, (float) ($row['amount'] ?? 0), $row['barcode'] ?? null);
            if ($uid) { $this->urunMap[$srId] = $uid; $this->counts['urun']++; }
        }
        $this->log('Urun: ' . $this->counts['urun']);
    }

    private function ensureUrun($ad, $fiyat = 0, $barkod = null)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        static $cache = [];
        $key = $this->trKey($ad);
        $ck = $this->salonId . '|' . $key;
        if (isset($cache[$ck])) return $cache[$ck];

        $urun = Urunler::where('salon_id', $this->salonId)->where('urun_adi', $ad)->first();
        if (!$urun) {
            foreach (Urunler::where('salon_id', $this->salonId)->select('id', 'urun_adi')->get() as $row) {
                if ($this->trKey($row->urun_adi) === $key) { $urun = Urunler::find($row->id); break; }
            }
        }
        if (!$urun) {
            try {
                $urun = new Urunler();
                $urun->urun_adi = $ad;
                $urun->salon_id = $this->salonId;
                if (\Schema::hasColumn('urunler', 'barkod') && $barkod) $urun->barkod = $barkod;
                if (\Schema::hasColumn('urunler', 'aktif')) $urun->aktif = 0;
                if (\Schema::hasColumn('urunler', 'fiyat') && $fiyat > 0) $urun->fiyat = $fiyat;
                if (\Schema::hasColumn('urunler', 'satis_fiyati') && $fiyat > 0) $urun->satis_fiyati = $fiyat;
                $urun->save();
            } catch (\Throwable $e) {
                \Log::warning('[Salonrandevu] urun', ['ad' => $ad, 'err' => $e->getMessage()]);
                return null;
            }
        }
        $cache[$ck] = $urun->id;
        return $urun->id;
    }

    // ======================= MUSTERI =======================

    public function importMusteriler()
    {
        $this->log('Musteriler cekiliyor (/company/customers)...');
        $apiController = app(\App\Http\Controllers\ApiController::class);

        // /company/customers?extra=1&page=N : her sayfa data.records, meta'da
        // total_record var (next_page YOK). total_record'a ulasinca dur.
        $rows = [];
        $total = null;
        $page = 1;
        while ($page <= 50000) {
            $j = $this->client->get('/company/customers?extra=1&page=' . $page);
            if (!$j) break;
            $d = $j['data'] ?? [];
            $recs = isset($d['records']) && is_array($d['records']) ? $d['records']
                  : (isset($d[0]) ? $d : []);
            if (empty($recs)) break;
            $rows = array_merge($rows, $recs);
            if ($total === null) {
                $total = $d['total_record'] ?? $d['total_records'] ?? null;
                $this->log('  total_record=' . ($total ?? '?') . ' sayfa_boyutu=' . count($recs));
            }
            if ($total !== null && count($rows) >= (int) $total) break;
            if (count($recs) < 2) break; // guard
            $page++;
            if ($page % 100 === 0) $this->log('  ..musteri sayfa ' . $page . ' (' . count($rows) . ')');
        }
        $this->log('  Toplam musteri kaydi: ' . count($rows));

        $i = 0;
        foreach ($rows as $c) {
            $i++;
            $srId = $c['id'] ?? null;
            if (!$srId) continue;
            $ad = $c['name'] ?? '';
            $soyad = $c['surname'] ?? '';
            $tel = $this->telNormalize($c['phone'] ?? ($c['s_phone'] ?? null));

            $payload = [
                'musteriAdi'  => trim($ad . ' ' . $soyad),
                'telefon'     => $tel,
                'ePosta'      => $c['email'] ?? '',
                'dogumTarihi' => $c['birthday'] ?? '',
                'cinsiyet'    => isset($c['sex']) ? ($c['sex'] == 1 ? 'Erkek' : 'Kadın') : '',
                'notlar'      => $c['description'] ?? '',
                'medeniDurum' => '', 'meslek' => '', 'adres' => '',
                'kayitTarihi' => $c['created_at'] ?? '',
                'salonId'     => $this->salonId,
            ];
            try {
                $req = new \Illuminate\Http\Request($payload);
                $resp = $apiController->aktarimMusteriKontrol($req);
                $userId = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
                if ($userId && ctype_digit($userId)) {
                    $this->musteriMap[$srId] = (int) $userId;
                    $this->counts['musteri']++;
                } else { $this->counts['hata']++; }
            } catch (\Throwable $e) {
                $this->counts['hata']++;
                \Log::warning('[Salonrandevu] musteri', ['sr' => $srId, 'err' => $e->getMessage()]);
            }
            if ($i % 500 === 0) $this->log("  musteri {$i}/" . count($rows) . " eklenen=" . $this->counts['musteri']);
        }
        $this->log('Musteri: eklenen=' . $this->counts['musteri'] . ' hata=' . $this->counts['hata']);
    }

    /** sr customer id -> users.id (map'te yoksa telefon ile DB'den bul) */
    private function resolveUser($srCustomer)
    {
        if (!is_array($srCustomer)) return null;
        $srId = $srCustomer['id'] ?? null;
        if ($srId && isset($this->musteriMap[$srId])) return $this->musteriMap[$srId];
        $tel = $this->telNormalize($srCustomer['phone'] ?? null);
        if ($tel) {
            $uid = DB::table('users')->where('cep_telefon', $tel)->value('id');
            if ($uid) { if ($srId) $this->musteriMap[$srId] = $uid; return $uid; }
        }
        // Inline create: receipt detayinda customer.name/surname/phone var ise
        // ApiController.aktarimMusteriKontrol ile musteri yarat veya bul.
        $ad = trim((string) ($srCustomer['name'] ?? ''));
        $soyad = trim((string) ($srCustomer['surname'] ?? ''));
        $fullName = trim($ad . ' ' . $soyad) ?: trim((string) ($srCustomer['full_name'] ?? ''));
        if ($fullName === '' && !$tel) return null;
        try {
            $apiController = app(\App\Http\Controllers\ApiController::class);
            $req = new \Illuminate\Http\Request([
                'musteriAdi'  => $fullName ?: ($tel ?: 'İsimsiz'),
                'telefon'     => $tel,
                'ePosta'      => $srCustomer['email'] ?? '',
                'dogumTarihi' => $srCustomer['birthday'] ?? '',
                'cinsiyet'    => isset($srCustomer['sex']) ? ($srCustomer['sex'] == 1 ? 'Erkek' : 'Kadın') : '',
                'notlar'      => $srCustomer['description'] ?? '',
                'medeniDurum' => '', 'meslek' => '', 'adres' => '',
                'kayitTarihi' => $srCustomer['created_at'] ?? '',
                'salonId'     => $this->salonId,
            ]);
            $resp = $apiController->aktarimMusteriKontrol($req);
            $uid = trim(is_object($resp) && method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp);
            if ($uid && ctype_digit($uid)) {
                if ($srId) $this->musteriMap[$srId] = (int) $uid;
                return (int) $uid;
            }
        } catch (\Throwable $e) {
            \Log::warning('[Salonrandevu] inline musteri', ['err' => $e->getMessage()]);
        }
        return null;
    }

    // ======================= RANDEVU =======================

    public function importRandevular()
    {
        $this->log('Randevular cekiliyor (/company/appointment/list) - sayfa sayfa...');
        $i = 0;
        $page = 1;
        $guard = 0;
        while ($guard++ < 100000) {
            $j = $this->client->get('/company/appointment/list?page=' . $page);
            if (!$j) { $this->log("  sayfa {$page} alinamadi, durdu."); break; }
            $d = $j['data'] ?? [];
            $rows = isset($d['records']) && is_array($d['records']) ? $d['records']
                  : (isset($d[0]) ? $d : []);
            if (empty($rows)) break;

            foreach ($rows as $appt) {
                $i++;
                $this->importOneAppointment($appt);
            }
            $this->log("  sayfa {$page} islendi (toplam islenen={$i} eklenen=" . $this->counts['randevu'] . " update=" . ($this->counts['randevu_update'] ?? 0) . " skip=" . $this->counts['skip'] . ')');

            $cur  = $d['page'] ?? $page;
            $next = $d['next_page'] ?? null;
            if ($next === null || (int) $next <= (int) $cur) break;
            $page = (int) $next;
        }
        $this->log('Randevu: eklenen=' . $this->counts['randevu'] . ' update=' . ($this->counts['randevu_update'] ?? 0) . ' skip=' . $this->counts['skip']);
    }

    private function importOneAppointment($appt)
    {
        {
            $apptId = $appt['id'] ?? null;
            if (!$apptId) { $this->counts['skip']++; return; }

            $userId = $this->resolveUser($appt['customer'] ?? []);
            if (!$userId) { $this->counts['skip']++; return; }

            list($tarih, $saat) = $this->isoBol($appt['appointment_start_date'] ?? null);
            if (!$tarih) { $this->counts['skip']++; return; }
            list(, $saatBitis) = $this->isoBol($appt['appointment_end_date'] ?? null);

            $marker = '[salonrandevu-rdv:' . $apptId . ']';
            // Upsert: marker varsa UPDATE (durum/geldi degisikligi senkron), yoksa INSERT.
            // Eskiden SKIP idi, salonrandevu'da durum degisirse bizde eski halde kaliyor.
            $existRandevu = Randevular::where('salon_id', $this->salonId)
                ->where('personel_notu', 'LIKE', '%' . $marker . '%')->first();

            // Personel
            $personelId = null;
            if (!empty($appt['staff']['id'])) {
                $personelId = $this->personelMap[$appt['staff']['id']] ?? null;
                if (!$personelId) {
                    $sad = trim(($appt['staff']['name'] ?? '') . ' ' . ($appt['staff']['surname'] ?? ''));
                    $personelId = $this->ensurePersonel($sad, $appt['staff']['detail']['phone'] ?? null);
                    if ($personelId && !empty($appt['staff']['id'])) $this->personelMap[$appt['staff']['id']] = $personelId;
                }
            }

            // Hizmet
            $hizmetId = null; $sure = 30; $fiyat = 0;
            if (!empty($appt['service']['id'])) {
                $svc = $appt['service'];
                $hizmetId = $this->hizmetMap[$svc['id']] ?? null;
                if (!$hizmetId) {
                    $hizmetId = $this->ensureHizmet($svc['name'] ?? '', (int) ($svc['process_time'] ?? 30), (float) ($svc['amount'] ?? 0), $svc['category_name'] ?? null);
                    if ($hizmetId) $this->hizmetMap[$svc['id']] = $hizmetId;
                }
                $sure = (int) ($svc['process_time'] ?? 30);
                if ($sure < 15) $sure = 15;
                $fiyat = (float) ($svc['amount'] ?? 0);
            }

            // customer_state -> durum / geldi mapping
            // Salonrandevu state degerleri (sample inspect ile dogrulandi):
            //   0 = Beklemede (onaysiz)
            //   1 = Onaylı (rezerve edilmis, henuz olmamis)
            //   2 = Geldi (musteri geldi - sadece GECMIS randevular icin anlamli)
            //   3 = Gelmedi (musteri gelmedi)
            //   4 = İptal
            //   5 = İptal (alternatif)
            $state = $appt['customer_state'] ?? null;
            $randevuTs = $tarih . ' ' . $saat;
            $isFuture = (strtotime($randevuTs) > time());

            $durum = 1; // default: onayli
            $geldi = null; // default: belli degil
            if ($state === 0)                         { $durum = 0; }
            elseif ($state === 1)                     { $durum = 1; }
            elseif ($state === 2 && !$isFuture)       { $durum = 1; $geldi = 1; }
            elseif ($state === 2 && $isFuture)        { $durum = 1; } // gelecekse Geldi degil
            elseif ($state === 3 && !$isFuture)       { $durum = 1; $geldi = 0; }
            elseif ($state === 3 && $isFuture)        { $durum = 1; }
            elseif (in_array($state, [4, 5], true))   { $durum = 2; $geldi = 0; }

            try {
                $r = $existRandevu ?: new Randevular();
                $r->tarih = $tarih;
                $r->saat = $saat;
                $r->user_id = $userId;
                $r->salon_id = $this->salonId;
                $r->durum = $durum;
                if (!$existRandevu) {
                    $r->salon = 0;
                    $r->olusturan_personel_id = null;
                }
                // EXPLICIT olarak set — null bile olsa eski yanlis degeri silsin
                // (eski kod "if !== null" kontrol ediyordu, gelecek randevu icin
                // null oldugundan eski geldi=1 silinmiyordu, hatali kayit kaliyordu)
                $r->randevuya_geldi = $geldi;
                $not = trim((string) ($appt['note'] ?? ''));
                $r->personel_notu = trim(($not ? $not . ' ' : '') . $marker);
                if (!$existRandevu && !empty($appt['created_at'])) {
                    $ct = strtotime($appt['created_at']);
                    if ($ct) $r->created_at = date('Y-m-d H:i:s', $ct);
                }
                $r->save();

                if ($hizmetId) {
                    // RandevuHizmetler upsert: (randevu_id, hizmet_id) bazli
                    $rh = RandevuHizmetler::where('randevu_id', $r->id)
                        ->where('hizmet_id', $hizmetId)->first();
                    if (!$rh) $rh = new RandevuHizmetler();
                    $rh->randevu_id = $r->id;
                    $rh->hizmet_id = $hizmetId;
                    $rh->saat = $saat;
                    $rh->saat_bitis = $saatBitis ?: date('H:i:s', strtotime('+' . $sure . ' minutes', strtotime($saat)));
                    $rh->sure_dk = $sure;
                    $rh->fiyat = $fiyat;
                    if ($personelId) $rh->personel_id = $personelId;
                    $rh->save();
                }
                if ($existRandevu) $this->counts['randevu_update'] = ($this->counts['randevu_update'] ?? 0) + 1;
                else $this->counts['randevu']++;
            } catch (\Throwable $e) {
                $this->counts['hata']++;
                \Log::warning('[Salonrandevu] randevu', ['appt' => $apptId, 'err' => $e->getMessage()]);
            }
        }
    }

    // ======================= RECEIPT (ADISYON + TAHSILAT) =======================

    public function importReceipts($from = null, $to = null)
    {
        // Dogru endpoint: /company/receipts/opened
        //   ispaid=2 (odenmis+odenmemis hepsi), isbetween=true (tarih araligi), start/end
        // /company/receipt/index sadece filter formu icin dropdown data donduruyor.
        $from = $from ?: '2020-01-01';
        $to   = $to   ?: date('Y-m-d', strtotime('+1 day'));
        $this->log("Fisler cekiliyor (/company/receipts/opened ispaid=2 {$from}..{$to}) - sayfa sayfa...");
        $i = 0;
        $page = 1;
        $guard = 0;
        while ($guard++ < 100000) {
            $qs = http_build_query([
                'page' => $page, 'ispaid' => 2, 'order' => 0,
                'start' => $from, 'end' => $to, 'isbetween' => 'true',
            ]);
            $j = $this->client->get('/company/receipts/opened?' . $qs);
            if (!$j) { $this->log("  sayfa {$page} alinamadi, durdu."); break; }
            $d = $j['data'] ?? [];
            // Response sema esnek: receipts.records, records, ya da data dizisi
            $rows = $d['receipts']['records'] ?? $d['records'] ?? (isset($d[0]) ? $d : []);
            if (empty($rows)) break;

            foreach ($rows as $rcRow) {
                $i++;
                $rid = $rcRow['id'] ?? null;
                if (!$rid) continue;
                try {
                    $this->importOneReceipt($rid);
                } catch (\Throwable $e) {
                    $this->counts['hata']++;
                    \Log::warning('[Salonrandevu] receipt', ['rid' => $rid, 'err' => $e->getMessage()]);
                }
            }
            $this->log("  fis sayfa {$page} islendi (toplam={$i} adisyon=" . $this->counts['adisyon'] . " tahsilat=" . $this->counts['tahsilat'] . ')');

            $meta = $d['receipts'] ?? $d;
            $cur  = $meta['page'] ?? $page;
            $next = $meta['next_page'] ?? null;
            if ($next === null || (int) $next <= (int) $cur) break;
            $page = (int) $next;
        }
        $this->log('Fis: adisyon=' . $this->counts['adisyon'] . ' tahsilat=' . $this->counts['tahsilat']);
    }

    /**
     * /company/packets master listesini bir kerelik yukle.
     * packet_id => [service_id => period (toplam seans sayisi)]
     */
    private function loadPacketMaster()
    {
        if ($this->packetMaster !== null) return;
        $this->packetMaster = [];
        $j = $this->client->get('/company/packets?name=&page=-1');
        $rows = $j['data']['packets']['records'] ?? ($j['data']['records'] ?? []);
        foreach ($rows as $pkg) {
            $pkgId = (int) ($pkg['id'] ?? 0);
            if (!$pkgId) continue;
            $details = $pkg['packet_details'] ?? [];
            foreach ($details as $det) {
                $svcId = (int) ($det['service_id'] ?? 0);
                $period = (int) ($det['period'] ?? 0);
                if ($svcId && $period > 0) {
                    $this->packetMaster[$pkgId][$svcId] = $period;
                }
            }
        }
        $this->log('Paket master yuklendi: ' . count($this->packetMaster) . ' paket.');
    }

    /**
     * Sistem mantigi: paket adi + hizmetler[hid=>['seans'=>N,'fiyat'=>F]] -> Paketler + PaketHizmetler.
     * Ayni salon+paket_adi varsa o ID donulur (paket_hizmetler eksik kalanlari da tamamlar).
     */
    private function ensurePaket($salonId, $paketAdi, array $hizmetler, $srSaleId = null)
    {
        $paketAdi = trim((string) $paketAdi);
        if ($paketAdi === '') return null;
        // Salonrandevu'da paket_id MASTER degil TEMPLATE — ayni packet_id farkli satislarda
        // farkli hizmetler iceriyor (Onur 'Lazer tüm vücut' + Ege 'Lazer kemer üstü' ikisi de
        // packet_id=11538). Bu yuzden her receipt_packages.id (satis) icin AYRI master paket
        // yaratiyoruz; suffix '[SR-sale:<saleId>]' ile UI'da gizli olarak ayirt edilir.
        $aramaAdi = $srSaleId ? $paketAdi . ' [SR-sale:' . $srSaleId . ']' : $paketAdi;
        $paket = Paketler::where('salon_id', $salonId)->where('paket_adi', $aramaAdi)->first();
        if (!$paket) {
            $paket = new Paketler();
            $paket->paket_adi = $aramaAdi;
            $paket->salon_id = $salonId;
            if (\Schema::hasColumn('paketler', 'aktif')) $paket->aktif = true;
            $paket->save();
        }
        foreach ($hizmetler as $hid => $info) {
            $ph = PaketHizmetler::where('paket_id', $paket->id)->where('hizmet_id', $hid)->first();
            if (!$ph) {
                $ph = new PaketHizmetler();
                $ph->paket_id = $paket->id;
                $ph->hizmet_id = $hid;
            }
            $ph->seans = (int) ($info['seans'] ?? 1);
            $ph->fiyat = (float) ($info['fiyat'] ?? 0);
            $ph->save();
        }
        return $paket->id;
    }

    private function importOneReceipt($rid, array $opts = [])
    {
        $packageOnly = !empty($opts['package_only']);
        $marker = '[salonrandevu:' . $rid . ']';
        // Dedup / UPSERT: mevcut markerli adisyon varsa SIL (paket-only modunda yeniden yaz)
        $adisyonTable = (new Adisyonlar)->getTable();
        $markerCol = null;
        foreach (['aciklama', 'adisyon_notu', 'genel_aciklama', 'notlar', 'not'] as $col) {
            if (\Schema::hasColumn($adisyonTable, $col)) { $markerCol = $col; break; }
        }
        if ($markerCol) {
            $eskiAdIds = DB::table($adisyonTable)->where('salon_id', $this->salonId)
                ->where($markerCol, 'LIKE', '%' . $marker . '%')->pluck('id')->all();
            if (!empty($eskiAdIds)) {
                if ($packageOnly) {
                    // UPSERT — eski adisyon ve bagli kayitlari sil
                    $apIds = DB::table('adisyon_paketler')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($apIds)) DB::table('adisyon_paket_seanslar')->whereIn('adisyon_paket_id', $apIds)->delete();
                    $ahIds = DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($ahIds)) DB::table('adisyon_paket_seanslar')->whereIn('adisyon_hizmet_id', $ahIds)->delete();
                    DB::table('adisyon_paketler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    DB::table('adisyon_hizmetler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    DB::table('adisyon_urunler')->whereIn('adisyon_id', $eskiAdIds)->delete();
                    $tIds = DB::table('tahsilatlar')->whereIn('adisyon_id', $eskiAdIds)->pluck('id')->all();
                    if (!empty($tIds)) {
                        DB::table('tahsilat_hizmetler')->whereIn('tahsilat_id', $tIds)->delete();
                        DB::table('tahsilat_urunler')->whereIn('tahsilat_id', $tIds)->delete();
                        if (\Schema::hasTable('tahsilat_paketler')) DB::table('tahsilat_paketler')->whereIn('tahsilat_id', $tIds)->delete();
                        DB::table('tahsilatlar')->whereIn('id', $tIds)->delete();
                    }
                    DB::table($adisyonTable)->whereIn('id', $eskiAdIds)->delete();
                } else {
                    return; // eski akis: zaten var ise atla
                }
            }
        }

        $j = $this->client->get('/company/receipt/' . $rid);
        $rc = $j['data']['receipt'] ?? null;
        if (!$rc) {
            if ($packageOnly) {
                $this->counts['skip']++;
                \Log::warning('[Salonrandevu] paket rc null', [
                    'rid' => $rid,
                    'http' => $j['_http'] ?? '?',
                    'raw_keys' => array_keys($j['data'] ?? []),
                ]);
            }
            return;
        }
        if ($packageOnly) {
            $pkgCnt = count($rc['receipt_packages'] ?? []);
            $txCnt = count($rc['receipt_transactions'] ?? []);
            \Log::info('[Salonrandevu] paket processing', [
                'rid' => $rid,
                'customer' => $rc['customer']['full_name'] ?? '?',
                'pkg_count' => $pkgCnt,
                'tx_count' => $txCnt,
            ]);
            // FALLBACK: eski 2023 paketleri /receipt/{id} detayinda receipt_packages=[] doner,
            // ama paginated /receipts/packets listesinde is_package=true ile gelir + tx'ler dolu.
            // Bu durumda paginated rec'ten sentetik paket master uret + tx'leri buna bagla.
            if ($pkgCnt === 0 && $txCnt > 0 && !empty($opts['rec']) && !empty($opts['rec']['is_package'])) {
                $rec = $opts['rec'];
                // Customer detay bos ise paginated rec.full_name kullan
                if (empty($rc['customer']) || empty($rc['customer']['full_name'])) {
                    $rc['customer'] = is_array($rc['customer'] ?? null) ? $rc['customer'] : [];
                    $rc['customer']['full_name'] = $rec['full_name'] ?? ($rc['customer']['full_name'] ?? '');
                }
                // info'nun ilk satirini paket adi olarak al (multi-line: "Lazer ( 3 Bölge )\nDIGER URUNLER")
                $infoStr = trim((string) ($rec['info'] ?? ''));
                $paketAdiSentez = $infoStr;
                if (strpos($infoStr, "\n") !== false) {
                    $paketAdiSentez = trim(strstr($infoStr, "\n", true));
                }
                if ($paketAdiSentez === '') $paketAdiSentez = 'Paket #' . $rid;
                $syntheticSaleId = $rid; // benzersiz fallback id
                $rc['receipt_packages'] = [[
                    'id'           => $syntheticSaleId,
                    'package_name' => $paketAdiSentez,
                    'packet_id'    => 0,
                    'amount'       => (float) ($rec['all_amount'] ?? 0),
                    'paid_amount'  => (float) ($rec['paid'] ?? 0),
                    'staff_id'     => 0,
                ]];
                // Tx'lerin hicbirinde receipt_package_id yok — hepsini sentetik paket altina sok
                $rc['receipt_transactions'] = array_map(function ($tx) use ($syntheticSaleId) {
                    $tx['receipt_package_id'] = $syntheticSaleId;
                    return $tx;
                }, $rc['receipt_transactions'] ?? []);
                $rc['is_package'] = true;
                \Log::info('[Salonrandevu] paket FALLBACK sentez', [
                    'rid' => $rid, 'paket_adi' => $paketAdiSentez,
                    'fiyat' => $rec['all_amount'] ?? 0, 'tx_count' => $txCnt,
                ]);
            }
        }

        $userId = $this->resolveUser($rc['customer'] ?? []);
        if (!$userId) {
            $this->counts['skip']++;
            \Log::warning('[Salonrandevu] skip user resolve fail', [
                'rid' => $rid,
                'customer_name' => $rc['customer']['full_name'] ?? '?',
                'customer_phone' => $rc['customer']['phone'] ?? '?',
                'customer_id' => $rc['customer']['id'] ?? 0,
            ]);
            return;
        }

        list($tarih,) = $this->isoBol($rc['created_at'] ?? null);
        if (!$tarih) $tarih = date('Y-m-d');

        // Paket fişi ise paket master'ı yukle (lazy)
        // receipt_packages[idx].packet_id (master ref) + receipt_packages[idx].id (sale id)
        // receipt_transactions[idx].receipt_package_id => receipt_packages[idx].id
        $pkgById = []; // receipt_packages.id => packet_id (master)
        $pkgAdlari = []; // paket name listesi (notlar icin)
        foreach (($rc['receipt_packages'] ?? []) as $pkg) {
            $pkgById[(int) ($pkg['id'] ?? 0)] = (int) ($pkg['packet_id'] ?? 0);
            $pn = trim((string) ($pkg['package_name'] ?? ''));
            if ($pn !== '') $pkgAdlari[] = $pn;
        }
        $isPaketFisi = !empty($rc['is_package']) || !empty($pkgById);
        if ($isPaketFisi) {
            $this->loadPacketMaster();
        }

        // Adisyon
        $ad = new Adisyonlar();
        $ad->user_id = $userId;
        $ad->salon_id = $this->salonId;
        $ad->tarih = $tarih;
        $ad->save();
        if ($markerCol) {
            // Paket fişi ise extra marker ekle: [salonrandevu-paket:rid] + paket adlari (UI'da tanima)
            $finalMarker = $marker;
            if ($isPaketFisi) {
                $finalMarker = trim('[salonrandevu-paket:' . $rid . '] ' . (count($pkgAdlari) ? implode(', ', $pkgAdlari) . ' ' : '') . $marker);
            }
            DB::table($adisyonTable)->where('id', $ad->id)->update([$markerCol => $finalMarker]);
        }
        $this->counts['adisyon']++;

        // === Paket fişlerini parse et: receipt_packages[idx] + onun altındaki transactions ===
        // Sistem mantigi (ApiController:878+): Paketler + PaketHizmetler master, AdisyonPaketler +
        // AdisyonPaketSeanslar satis. Paket transaction'lari icin AH YAZILMAZ — kullanicinin istegi
        // "sadece paket olarak kaydolsun, her seans ayri hizmet kaydi olmasın".
        $pkgInfoBySaleId = []; // receipt_packages.id => ['adi'=>..., 'fiyat'=>..., 'hizmetler'=>[svcId=>['hizmet_id'=>X,'tx_list'=>[...]]]]
        foreach (($rc['receipt_packages'] ?? []) as $pkg) {
            $saleId = (int) ($pkg['id'] ?? 0);
            if (!$saleId) continue;
            $pkgFiyat = (float) ($pkg['amount'] ?? 0);
            // pkg.amount=0 ise paginated rec.all_amount kullan (tek paketli fislerde)
            if ($pkgFiyat == 0.0 && !empty($opts['rec']['all_amount'])
                && count($rc['receipt_packages']) === 1) {
                $pkgFiyat = (float) $opts['rec']['all_amount'];
            }
            $pkgInfoBySaleId[$saleId] = [
                'adi'       => trim((string) ($pkg['package_name'] ?? '')),
                'fiyat'     => $pkgFiyat,
                'packet_id' => (int) ($pkg['packet_id'] ?? 0),
                'staff_id'  => (int) ($pkg['staff_id'] ?? 0), // satisi yapan personel
                'hizmetler' => [], // service_id -> ['hizmet_id'=>X, 'tx_list'=>[$tx,...]]
            ];
        }

        foreach (($rc['receipt_transactions'] ?? []) as $tx) {
            $recPkgId = (int) ($tx['receipt_package_id'] ?? 0);
            $svcId = (int) ($tx['service_id'] ?? 0);
            $svc = $tx['Service'] ?? [];
            $hizmetId = null;
            if ($svcId && isset($this->hizmetMap[$svcId])) {
                $hizmetId = $this->hizmetMap[$svcId];
            }
            if (!$hizmetId && !empty($svc['name'])) {
                $hizmetId = $this->ensureHizmet($svc['name'], (int) ($svc['process_time'] ?? 30), (float) ($svc['amount'] ?? 0), $svc['category_name'] ?? null);
                if ($hizmetId && $svcId) $this->hizmetMap[$svcId] = $hizmetId;
            }
            if (!$hizmetId) continue;

            if ($recPkgId && isset($pkgInfoBySaleId[$recPkgId])) {
                // Paket icine ait transaction — grupla, AH yazma
                if (!isset($pkgInfoBySaleId[$recPkgId]['hizmetler'][$svcId])) {
                    $pkgInfoBySaleId[$recPkgId]['hizmetler'][$svcId] = [
                        'hizmet_id' => $hizmetId, 'tx_list' => [],
                    ];
                }
                $pkgInfoBySaleId[$recPkgId]['hizmetler'][$svcId]['tx_list'][] = $tx;
                continue;
            }

            // FALLBACK: tx.receipt_package_id=0 ve receipt'te tek paket varsa
            // (SR'de "acik paket" — tx pakete bagli degil ama paket fisinde tek master var)
            if (!$recPkgId && $packageOnly && count($pkgInfoBySaleId) === 1) {
                $onlySaleId = array_key_first($pkgInfoBySaleId);
                if (!isset($pkgInfoBySaleId[$onlySaleId]['hizmetler'][$svcId])) {
                    $pkgInfoBySaleId[$onlySaleId]['hizmetler'][$svcId] = [
                        'hizmet_id' => $hizmetId, 'tx_list' => [],
                    ];
                }
                $pkgInfoBySaleId[$onlySaleId]['hizmetler'][$svcId]['tx_list'][] = $tx;
                continue;
            }

            // Paket disi normal hizmet satisi — eski AH mantigi (geldi=1)
            if ($packageOnly) {
                // Paket modunda paket disi tx — log: receipt_package_id'si yok ya da match etmiyor
                \Log::warning('[Salonrandevu] paket-only mod: tx receipt_package_id match yok', [
                    'rid' => $rid, 'tx_recPkgId' => $recPkgId,
                    'svc_id' => $svcId, 'svc_name' => $svc['name'] ?? '?',
                    'mevcut_sale_ids' => array_keys($pkgInfoBySaleId),
                ]);
                continue; // --only-package-sales modunda paket disi hizmet YAZMA
            }
            $personelId = null;
            if (!empty($tx['staffID'])) $personelId = $this->personelMap[$tx['staffID']] ?? null;
            if (!$personelId && !empty($tx['staff']['full_name'])) {
                $personelId = $this->ensurePersonel($tx['staff']['full_name']);
            }
            list($pTarih,) = $this->isoBol($tx['process_date'] ?? null);
            $ah = new AdisyonHizmetler();
            $ah->adisyon_id = $ad->id;
            $ah->hizmet_id = $hizmetId;
            $ah->personel_id = $personelId;
            $ah->geldi = 1;
            $ah->islem_tarihi = $pTarih ?: $tarih;
            $ah->fiyat = (float) ($tx['amount'] ?? 0);
            $ah->save();
        }

        // Paket SATIŞ akisi: her receipt_packages icin Paketler master + AdisyonPaketler + APS
        foreach ($pkgInfoBySaleId as $saleId => $info) {
            $paketAdi = $info['adi'] ?: ('Paket #' . $saleId);
            $paketFiyat = $info['fiyat'];
            if (empty($info['hizmetler'])) {
                \Log::warning('[Salonrandevu] paket bos hizmetler', [
                    'rid' => $rid, 'saleId' => $saleId,
                    'paket_adi' => $paketAdi, 'fiyat' => $paketFiyat,
                    'tx_count' => count($rc['receipt_transactions'] ?? []),
                ]);
                continue;
            }
            // Master paket: paket_hizmetler her service icin seans=tx_count, fiyat=sum(amount)
            $hzMap = [];
            foreach ($info['hizmetler'] as $svcId => $svcGroup) {
                $hid = $svcGroup['hizmet_id'];
                $seans = count($svcGroup['tx_list']);
                $fiyat = 0.0;
                foreach ($svcGroup['tx_list'] as $tx) $fiyat += (float) ($tx['amount'] ?? 0);
                $hzMap[$hid] = ['seans' => $seans, 'fiyat' => $fiyat];
            }
            $paketId = $this->ensurePaket($this->salonId, $paketAdi, $hzMap, $saleId);
            if (!$paketId) continue;
            $totalSeans = array_sum(array_column($hzMap, 'seans'));
            // Satisi yapan personel (receipt_packages.staff_id)
            $satisPersonelId = null;
            $pkgStaffId = $info['staff_id'] ?? 0;
            if ($pkgStaffId && isset($this->personelMap[$pkgStaffId])) {
                $satisPersonelId = $this->personelMap[$pkgStaffId];
            }
            // AdisyonPaketler insert (paket satisi) — StoreAdminController:17487/22006 formati
            $apkt = new AdisyonPaketler();
            $apkt->adisyon_id = $ad->id;
            $apkt->paket_id = $paketId;
            $apkt->fiyat = $paketFiyat ?: array_sum(array_column($hzMap, 'fiyat'));
            if ($satisPersonelId && \Schema::hasColumn('adisyon_paketler', 'personel_id')) $apkt->personel_id = $satisPersonelId;
            if (\Schema::hasColumn('adisyon_paketler', 'seans_sayisi')) $apkt->seans_sayisi = $totalSeans;
            if (\Schema::hasColumn('adisyon_paketler', 'baslangic_tarihi')) $apkt->baslangic_tarihi = $tarih;
            if (\Schema::hasColumn('adisyon_paketler', 'seans_araligi')) $apkt->seans_araligi = 7;
            try {
                $apkt->save();
            } catch (\Throwable $e) {
                \Log::warning('[Salonrandevu paket] AdisyonPaketler save hata', ['rid' => $rid, 'err' => $e->getMessage()]);
                continue;
            }
            if ($packageOnly) {
                \Log::info('[Salonrandevu] paket SAVE OK', [
                    'rid' => $rid, 'saleId' => $saleId,
                    'paket_id' => $paketId, 'ap_id' => $apkt->id,
                    'fiyat' => $apkt->fiyat, 'seans' => $totalSeans,
                ]);
            }
            // APS yaz: her tx -> 1 APS. geldi=NULL (Bekleniyor, Salonrandevu UI ile esit).
            // Her APS'in personel_id'sini transaction.staffID'den al (her seans icin atanan personel).
            foreach ($info['hizmetler'] as $svcId => $svcGroup) {
                $hid = $svcGroup['hizmet_id'];
                $seansNo = 0;
                foreach ($svcGroup['tx_list'] as $tx) {
                    $seansNo++;
                    list($pTarih,) = $this->isoBol($tx['process_date'] ?? null);
                    $txStaffId = (int) ($tx['staffID'] ?? 0);
                    $apsPersonelId = $txStaffId && isset($this->personelMap[$txStaffId])
                        ? $this->personelMap[$txStaffId] : null;
                    if (!$apsPersonelId && !empty($tx['staff']['full_name'])) {
                        $apsPersonelId = $this->ensurePersonel($tx['staff']['full_name']);
                        if ($apsPersonelId && $txStaffId) $this->personelMap[$txStaffId] = $apsPersonelId;
                    }
                    $aps = new AdisyonPaketSeanslar();
                    $aps->adisyon_paket_id = $apkt->id;
                    $aps->hizmet_id = $hid;
                    $aps->seans_no = $seansNo;
                    $aps->geldi = null; // Salonrandevu = Bekleniyor
                    if ($apsPersonelId && \Schema::hasColumn('adisyon_paket_seanslar', 'personel_id')) {
                        $aps->personel_id = $apsPersonelId;
                    }
                    if (\Schema::hasColumn('adisyon_paket_seanslar', 'seans_tarih')) {
                        $aps->seans_tarih = $pTarih ?: null;
                    }
                    try { $aps->save(); } catch (\Throwable $e) {
                        \Log::warning('[Salonrandevu paket APS] hata', ['rid' => $rid, 'err' => $e->getMessage()]);
                    }
                }
            }
            // Satisi yapan personel ilk denemede personelMap'ten yoksa, ilk APS'in personel'inden al
            if (!$satisPersonelId && $apkt->id && \Schema::hasColumn('adisyon_paketler', 'personel_id')) {
                $firstApsPers = DB::table('adisyon_paket_seanslar')
                    ->where('adisyon_paket_id', $apkt->id)
                    ->whereNotNull('personel_id')->value('personel_id');
                if ($firstApsPers) {
                    DB::table('adisyon_paketler')->where('id', $apkt->id)->update(['personel_id' => $firstApsPers]);
                }
            }
        }

        if ($packageOnly) { $this->counts['paket_satis'] = ($this->counts['paket_satis'] ?? 0) + 1; return; }

        // Urun satislari (receipt_sales)
        foreach (($rc['receipt_sales'] ?? []) as $sale) {
            $urunId = null;
            $srStockId = $sale['stock_item_id'] ?? ($sale['stock_id'] ?? null);
            if ($srStockId && isset($this->urunMap[$srStockId])) $urunId = $this->urunMap[$srStockId];
            $urunAd = $sale['name'] ?? ($sale['stock_item']['name'] ?? '');
            if (!$urunId && $urunAd) {
                $urunId = $this->ensureUrun($urunAd, (float) ($sale['amount'] ?? 0));
            }
            if (!$urunId) continue;
            $au = new AdisyonUrunler();
            $au->adisyon_id = $ad->id;
            $au->urun_id = $urunId;
            $au->adet = (int) ($sale['quantity'] ?? $sale['count'] ?? 1);
            $au->fiyat = (float) ($sale['amount'] ?? 0);
            if (\Schema::hasColumn('adisyon_urunler', 'islem_tarihi')) $au->islem_tarihi = $tarih;
            $au->save();
        }

        // Tahsilatlar (receipt_payments)
        foreach (($rc['receipt_payments'] ?? []) as $pay) {
            $tutar = (float) ($pay['amount'] ?? 0);
            if ($tutar <= 0) continue;
            list($odemeTarih,) = $this->isoBol($pay['payment_date'] ?? null);
            $t = new Tahsilatlar();
            $t->user_id = $userId;
            $t->adisyon_id = $ad->id;
            $t->salon_id = $this->salonId;
            $t->tutar = $tutar;
            if (\Schema::hasColumn('tahsilatlar', 'yapilan_odeme')) $t->yapilan_odeme = $tutar;
            $t->odeme_tarihi = $odemeTarih ?: $tarih;
            // payment_type: 1 nakit, 2 kart, 3 havale (tahmini)
            $pt = $pay['payment_type'] ?? 1;
            $t->odeme_yontemi_id = in_array($pt, [1, 2, 3, 4]) ? $pt : 1;
            if (\Schema::hasColumn('tahsilatlar', 'notlar')) $t->notlar = $marker;
            $t->save();
            $this->counts['tahsilat']++;
        }
    }

    // ======================= GIDER =======================

    public function importGiderler($from = null, $to = null)
    {
        $from = $from ?: '2020-01-01';
        $to   = $to ?: date('Y-m-d');
        $this->log("Giderler cekiliyor (/company/accounting/expenses {$from}..{$to})...");
        // Tarih parametre adlari belirsiz; birkac varyant dene
        $rows = [];
        foreach ([
            ['start_date' => $from, 'end_date' => $to],
            ['start' => $from, 'end' => $to],
            ['from' => $from, 'to' => $to],
            ['date_start' => $from, 'date_end' => $to],
        ] as $q) {
            $qs = http_build_query($q);
            $j = $this->client->get('/company/accounting/expenses?' . $qs);
            $d = $j['data'] ?? [];
            $cand = $d['records'] ?? (isset($d[0]) ? $d : []);
            if (!empty($cand)) { $rows = $cand; break; }
        }
        $this->log('  Toplam gider: ' . count($rows));

        $defaultPers = Personeller::where('salon_id', $this->salonId)->value('id');
        foreach ($rows as $g) {
            $gid = $g['id'] ?? null;
            if (!$gid) continue;
            $marker = '[salonrandevu-gider:' . $gid . ']';
            if (DB::table('masraflar')->where('salon_id', $this->salonId)
                ->where('notlar', 'LIKE', '%' . $marker . '%')->exists()) continue;
            list($tarih,) = $this->isoBol($g['date'] ?? ($g['created_at'] ?? null));
            try {
                DB::table('masraflar')->insert([
                    'salon_id' => $this->salonId,
                    'tarih' => $tarih ?: date('Y-m-d'),
                    'tutar' => (float) ($g['amount'] ?? 0),
                    'aciklama' => (string) ($g['description'] ?? ($g['note'] ?? '')),
                    'notlar' => $marker,
                    'harcayan_id' => $defaultPers,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $this->counts['gider']++;
            } catch (\Throwable $e) {
                \Log::warning('[Salonrandevu] gider', ['gid' => $gid, 'err' => $e->getMessage()]);
            }
        }
        $this->log('Gider: ' . $this->counts['gider']);
    }

    // ======================= ORTAK =======================

    /**
     * {data:{records:[], next_page, page}} yapisinda tum sayfalari toplar.
     * next_page == page olunca veya bos sayfada durur.
     */
    private function fetchAllPaged($path, $recordsKey = 'records', $pageParam = 'page', $startPage = 1)
    {
        $all = [];
        $page = $startPage;
        $sep = (strpos($path, '?') !== false) ? '&' : '?';
        $guard = 0;
        while ($guard++ < 5000) {
            $j = $this->client->get($path . $sep . $pageParam . '=' . $page);
            if (!$j) break;
            $d = $j['data'] ?? [];
            $records = null;
            if (isset($d[$recordsKey]) && is_array($d[$recordsKey])) $records = $d[$recordsKey];
            elseif (isset($d['receipts'][$recordsKey])) $records = $d['receipts'][$recordsKey];
            elseif (isset($d['packets'][$recordsKey])) $records = $d['packets'][$recordsKey];
            elseif (isset($d[0])) $records = $d;
            if (empty($records)) break;
            $all = array_merge($all, $records);
            // pagination meta
            $meta = isset($d[$recordsKey]) ? $d : ($d['receipts'] ?? $d['packets'] ?? $d);
            $next = $meta['next_page'] ?? null;
            $cur  = $meta['page'] ?? $page;
            if ($next === null || (int) $next <= (int) $cur) break;
            $page = (int) $next;
        }
        return $all;
    }

    private function buildHizmetMapFromDb()
    {
        // sr id <-> hizmet eslesmesi import sirasinda kuruluyor; DB'den isimle yeniden kurmak
        // mumkun degil. Bos birak, randevu/receipt sirasinda ensureHizmet ile cozulur.
    }

    private function buildPersonelMapFromDb()
    {
        // Ayni sekilde — ensurePersonel devreye girer.
    }
}
