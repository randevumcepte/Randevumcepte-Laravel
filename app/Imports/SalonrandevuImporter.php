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
            $this->log("  sayfa {$page} islendi (toplam islenen={$i} eklenen=" . $this->counts['randevu'] . " dedup=" . $this->counts['randevu_dedup'] . " skip=" . $this->counts['skip'] . ')');

            $cur  = $d['page'] ?? $page;
            $next = $d['next_page'] ?? null;
            if ($next === null || (int) $next <= (int) $cur) break;
            $page = (int) $next;
        }
        $this->log('Randevu: eklenen=' . $this->counts['randevu'] . ' dedup=' . $this->counts['randevu_dedup'] . ' skip=' . $this->counts['skip']);
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
            if (Randevular::where('salon_id', $this->salonId)
                ->where('personel_notu', 'LIKE', '%' . $marker . '%')->exists()) {
                $this->counts['randevu_dedup']++;
                return;
            }

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

            // customer_state -> durum / geldi
            $state = $appt['customer_state'] ?? null;
            $durum = 1; $geldi = null;
            if ($state === 0) $durum = 0;
            elseif ($state === 2) { $durum = 1; $geldi = 1; }
            elseif ($state === 3) { $durum = 1; $geldi = 0; }
            elseif (in_array($state, [4, 5], true)) $durum = 2;

            try {
                $r = new Randevular();
                $r->tarih = $tarih;
                $r->saat = $saat;
                $r->user_id = $userId;
                $r->salon_id = $this->salonId;
                $r->durum = $durum;
                $r->salon = 0;
                $r->olusturan_personel_id = null;
                if ($geldi !== null) $r->randevuya_geldi = $geldi;
                $not = trim((string) ($appt['note'] ?? ''));
                $r->personel_notu = trim(($not ? $not . ' ' : '') . $marker);
                if (!empty($appt['created_at'])) {
                    $ct = strtotime($appt['created_at']);
                    if ($ct) $r->created_at = date('Y-m-d H:i:s', $ct);
                }
                $r->save();

                if ($hizmetId) {
                    $rh = new RandevuHizmetler();
                    $rh->randevu_id = $r->id;
                    $rh->hizmet_id = $hizmetId;
                    $rh->saat = $saat;
                    $rh->saat_bitis = $saatBitis ?: date('H:i:s', strtotime('+' . $sure . ' minutes', strtotime($saat)));
                    $rh->sure_dk = $sure;
                    $rh->fiyat = $fiyat;
                    if ($personelId) $rh->personel_id = $personelId;
                    $rh->save();
                }
                $this->counts['randevu']++;
            } catch (\Throwable $e) {
                $this->counts['hata']++;
                \Log::warning('[Salonrandevu] randevu', ['appt' => $apptId, 'err' => $e->getMessage()]);
            }
        }
    }

    // ======================= RECEIPT (ADISYON + TAHSILAT) =======================

    public function importReceipts()
    {
        $this->log('Fisler cekiliyor (/company/receipt/index) - sayfa sayfa...');
        $i = 0;
        $page = 1;
        $guard = 0;
        $bosSayfa = false;
        while ($guard++ < 100000) {
            $j = $this->client->get('/company/receipt/index?page=' . $page);
            if (!$j) { $this->log("  sayfa {$page} alinamadi, durdu."); break; }
            $d = $j['data'] ?? [];
            $rows = isset($d['records']) && is_array($d['records']) ? $d['records']
                  : (isset($d['receipts']['records']) ? $d['receipts']['records']
                  : (isset($d[0]) ? $d : []));
            if (empty($rows)) { if ($page === 1) $bosSayfa = true; break; }

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

            $meta = isset($d['records']) ? $d : ($d['receipts'] ?? $d);
            $cur  = $meta['page'] ?? $page;
            $next = $meta['next_page'] ?? null;
            if ($next === null || (int) $next <= (int) $cur) break;
            $page = (int) $next;
        }

        // receipt/index hic veri vermediyse acik fislere dus
        if ($bosSayfa) {
            $this->log('  receipt/index bos -> /company/receipts/opened deneniyor...');
            $j = $this->client->get('/company/receipts/opened');
            $rows = $j['data']['receipts']['records'] ?? [];
            foreach ($rows as $rcRow) {
                $rid = $rcRow['id'] ?? null;
                if (!$rid) continue;
                try { $this->importOneReceipt($rid); }
                catch (\Throwable $e) { $this->counts['hata']++; }
            }
        }
        $this->log('Fis: adisyon=' . $this->counts['adisyon'] . ' tahsilat=' . $this->counts['tahsilat']);
    }

    private function importOneReceipt($rid)
    {
        $marker = '[salonrandevu:' . $rid . ']';
        // Dedup
        $adisyonTable = (new Adisyonlar)->getTable();
        $markerCol = null;
        foreach (['aciklama', 'adisyon_notu', 'genel_aciklama', 'notlar', 'not'] as $col) {
            if (\Schema::hasColumn($adisyonTable, $col)) { $markerCol = $col; break; }
        }
        if ($markerCol && DB::table($adisyonTable)->where('salon_id', $this->salonId)
            ->where($markerCol, 'LIKE', '%' . $marker . '%')->exists()) {
            return; // zaten var
        }

        $j = $this->client->get('/company/receipt/' . $rid);
        $rc = $j['data']['receipt'] ?? null;
        if (!$rc) return;

        $userId = $this->resolveUser($rc['customer'] ?? []);
        if (!$userId) { $this->counts['skip']++; return; }

        list($tarih,) = $this->isoBol($rc['created_at'] ?? null);
        if (!$tarih) $tarih = date('Y-m-d');

        // Adisyon
        $ad = new Adisyonlar();
        $ad->user_id = $userId;
        $ad->salon_id = $this->salonId;
        $ad->tarih = $tarih;
        $ad->save();
        if ($markerCol) {
            DB::table($adisyonTable)->where('id', $ad->id)->update([$markerCol => $marker]);
        }
        $this->counts['adisyon']++;

        // Hizmet kalemleri (receipt_transactions)
        foreach (($rc['receipt_transactions'] ?? []) as $tx) {
            $svc = $tx['Service'] ?? [];
            $hizmetId = null;
            if (!empty($tx['service_id']) && isset($this->hizmetMap[$tx['service_id']])) {
                $hizmetId = $this->hizmetMap[$tx['service_id']];
            }
            if (!$hizmetId && !empty($svc['name'])) {
                $hizmetId = $this->ensureHizmet($svc['name'], (int) ($svc['process_time'] ?? 30), (float) ($svc['amount'] ?? 0), $svc['category_name'] ?? null);
                if ($hizmetId && !empty($tx['service_id'])) $this->hizmetMap[$tx['service_id']] = $hizmetId;
            }
            if (!$hizmetId) continue;
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
            $ah->geldi = !empty($tx['is_paid']) ? 1 : 0;
            $ah->islem_tarihi = $pTarih ?: $tarih;
            $ah->fiyat = (float) ($tx['amount'] ?? 0);
            $ah->save();
        }

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
