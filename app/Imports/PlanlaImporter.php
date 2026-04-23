<?php

namespace App\Imports;

use App\Services\PlanlaClient;
use App\User;
use App\MusteriPortfoy;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\SalonHizmetKategoriRenkleri;
use App\Personeller;
use App\PersonelCalismaSaatleri;
use App\IsletmeYetkilileri;
use App\Randevular;
use App\RandevuHizmetler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Planla.co connect-api verisini randevumcepte DB'sine aktarir.
 * Endpoint: POST /connect-api, meta: {version:"1", category:<cat>, event:"read"}
 *
 * Ogrenilmis veri yapisi (connectRaw dumplarindan):
 * - customers:  _id, fullName, phone, countryCode, email, notes, createdAt(unix)
 * - services:   _id, title, time(str-dk), price(str, empty=0), order, createdAt
 * - appointments: _id, customer[id], service[id], employee[id], status[str],
 *                 appointmentDate(YYYY-MM-DD), appointmentTime(HH:MM), notes, createdAt
 * - employees:  _id, fullName, phone, email, color, workingHours, services
 *
 * ID'ler MongoDB ObjectId (24-char hex) -> planla*Map tablolarinda string olarak tutulur.
 */
class PlanlaImporter
{
    /** @var PlanlaClient */
    private $client;
    /** @var int */
    private $salonId;
    private $out;
    private $counts = ['personel' => 0, 'hizmet' => 0, 'musteri' => 0, 'randevu' => 0, 'skipped' => 0];
    /** @var array planla _id => local id */
    private $hizmetMap = [];
    private $musteriMap = [];
    private $personelMap = [];
    private $defaultKategoriId = null;

    public function __construct(PlanlaClient $client, $salonId, $out = null)
    {
        $this->client = $client;
        $this->salonId = (int) $salonId;
        $this->out = $out;
    }

    public function summary()
    {
        return $this->counts;
    }

    private function fetchCategory($category)
    {
        $resp = $this->client->connectApi($category, [], [
            'category' => $category,
            'event'    => 'read',
        ]);
        if (!is_array($resp)) {
            $this->log("  category={$category} -> JSON donmedi");
            return [];
        }
        if (isset($resp['data']) && is_array($resp['data']) && (isset($resp['data'][0]) || empty($resp['data']))) {
            $cnt = count($resp['data']);
            $this->log("  category={$category} -> {$cnt} kayit");
            return $resp['data'];
        }
        if (isset($resp['meta']['error']) || isset($resp['error'])) {
            $err = isset($resp['error']) ? $resp['error'] : $resp['meta']['error'];
            $this->log("  category={$category} -> hata: " . (is_array($err) ? json_encode($err) : $err));
        }
        return [];
    }

    // ---- Personel ------------------------------------------------------

    public function importPersoneller()
    {
        $this->log('Personel cekiliyor (category=employees)...');
        $items = $this->fetchCategory('employees');
        foreach ($items as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $ad = isset($row['fullName']) ? trim($row['fullName']) : '';
            if (!$ad) continue;

            $tel = $this->telefonNormalize(isset($row['phone']) ? $row['phone'] : null);
            $email = !empty($row['email']) ? trim($row['email']) : null;

            $p = Personeller::where('personel_adi', $ad)->where('salon_id', $this->salonId)->first();
            if (!$p) {
                // 1) IsletmeYetkilileri (personel login hesabi)
                $yetkili = new IsletmeYetkilileri();
                $yetkili->name = $ad;
                $yetkili->gsm1 = $tel;
                if ($email) $yetkili->email = $email;
                $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $yetkili->password = Hash::make(Str::random(10));
                $yetkili->aktif = 1;
                $yetkili->save();

                // 2) Takvim sirasi + renk hesapla
                $sonSira = Personeller::where('salon_id', $this->salonId)->max('takvim_sirasi');
                $sira = ($sonSira ? $sonSira : 0) + 1;
                $sonRenk = Personeller::where('salon_id', $this->salonId)->orderBy('id', 'desc')->value('renk');
                if (!$sonRenk || $sonRenk >= 10) $renk = 1; else $renk = $sonRenk + 1;

                // 3) Personeller
                $p = new Personeller();
                $p->personel_adi = $ad;
                $p->cep_telefon = $tel;
                $p->salon_id = $this->salonId;
                $p->yetkili_id = $yetkili->id;
                $p->role_id = 5;
                $p->aktif = 1;
                $p->takvimde_gorunsun = 1;
                $p->takvim_sirasi = $sira;
                $p->renk = $renk;
                $p->save();

                // 4) model_has_roles (Spatie permission)
                DB::insert(
                    'INSERT INTO model_has_roles (role_id, model_type, model_id, salon_id) VALUES (?, ?, ?, ?)',
                    [5, 'App\\IsletmeYetkilileri', $yetkili->id, $this->salonId]
                );

                // 5) PersonelCalismaSaatleri (7 gun)
                $this->personelCalismaSaatleriYaz($p->id, isset($row['workingHours']) ? $row['workingHours'] : []);
            }
            if ($planlaId) $this->personelMap[$planlaId] = $p->id;
            $this->counts['personel']++;
        }
        $this->log('Personel aktarim: ' . $this->counts['personel']);
    }

    private function personelCalismaSaatleriYaz($personelId, $workingHours)
    {
        $gunler = [
            1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday',
            5 => 'friday', 6 => 'saturday', 7 => 'sunday',
        ];
        PersonelCalismaSaatleri::where('personel_id', $personelId)->delete();
        foreach ($gunler as $num => $eng) {
            $g = isset($workingHours[$eng]) && is_array($workingHours[$eng]) ? $workingHours[$eng] : [];
            $calisiyor = (isset($g['status']) && $g['status'] === 'open') ? 1 : 0;
            $baslangic = !empty($g['opening']) ? $g['opening'] : '09:00';
            $bitis     = !empty($g['closing']) ? $g['closing'] : '21:00';
            $pcs = new PersonelCalismaSaatleri();
            $pcs->personel_id = $personelId;
            $pcs->haftanin_gunu = $num;
            $pcs->calisiyor = $calisiyor;
            $pcs->baslangic_saati = $baslangic;
            $pcs->bitis_saati = $bitis;
            $pcs->save();
        }
    }

    // ---- Hizmetler -----------------------------------------------------

    public function importHizmetler()
    {
        $this->log('Hizmetler cekiliyor (category=services)...');
        $items = $this->fetchCategory('services');
        $kategoriId = $this->defaultKategoriId();

        foreach ($items as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $title = isset($row['title']) ? trim($row['title']) : '';
            if (!$title) continue;
            $sure = (int) (isset($row['time']) ? $row['time'] : 30);
            if ($sure <= 0) $sure = 30;
            $fiyat = 0;
            if (isset($row['price']) && $row['price'] !== '') {
                $fiyat = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $row['price']));
            }

            $hizmet = Hizmetler::where('hizmet_adi', $title)->where('hizmet_kategori_id', $kategoriId)->first();
            if (!$hizmet) {
                $hizmet = new Hizmetler();
                $hizmet->hizmet_adi = $title;
                $hizmet->hizmet_kategori_id = $kategoriId;
                $hizmet->ozel_hizmet = true;
                if (Schema::hasColumn('hizmetler', 'salon_id')) {
                    $hizmet->salon_id = $this->salonId;
                }
                $hizmet->save();
            }

            $sh = SalonHizmetler::where('salon_id', $this->salonId)->where('hizmet_id', $hizmet->id)->first();
            if (!$sh) {
                $sh = new SalonHizmetler();
                $sh->salon_id = $this->salonId;
                $sh->hizmet_id = $hizmet->id;
                $sh->hizmet_kategori_id = $kategoriId;
                $sh->aktif = 1;
                $sh->bolum = 2;
            }
            $sh->sure_dk = $sure;
            $sh->baslangic_fiyat = $fiyat;
            $sh->son_fiyat = $fiyat;
            $sh->save();
            $this->ensureKategoriRenk($kategoriId);

            if ($planlaId) $this->hizmetMap[$planlaId] = $hizmet->id;
            $this->counts['hizmet']++;
        }
        $this->log('Hizmet aktarim: ' . $this->counts['hizmet']);
    }

    private function defaultKategoriId()
    {
        if ($this->defaultKategoriId) return $this->defaultKategoriId;
        $ad = 'Planla';
        $k = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $ad)->first();
        if (!$k) {
            $k = new Hizmet_Kategorisi();
            $k->hizmet_kategorisi_adi = $ad;
            $k->save();
        }
        $this->defaultKategoriId = $k->id;
        return $this->defaultKategoriId;
    }

    private function ensureKategoriRenk($kategoriId)
    {
        $var = SalonHizmetKategoriRenkleri::where('salon_id', $this->salonId)
            ->where('hizmet_kategori_id', $kategoriId)->first();
        if ($var) return;
        $last = SalonHizmetKategoriRenkleri::where('salon_id', $this->salonId)
            ->orderBy('renk_id', 'desc')->first();
        $renk = 1;
        if ($last) $renk = ($last->renk_id >= 10) ? 1 : $last->renk_id + 1;
        $n = new SalonHizmetKategoriRenkleri();
        $n->salon_id = $this->salonId;
        $n->hizmet_kategori_id = $kategoriId;
        $n->renk_id = $renk;
        $n->save();
    }

    // ---- Musteriler ----------------------------------------------------

    public function importMusteriler()
    {
        $this->log('Musteriler cekiliyor (category=customers)...');
        $items = $this->fetchCategory('customers');
        foreach ($items as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $ad = isset($row['fullName']) ? trim($row['fullName']) : '';
            if (!$ad) { $this->counts['skipped']++; continue; }
            $tel = $this->telefonNormalize(isset($row['phone']) ? $row['phone'] : null);
            $email = !empty($row['email']) ? trim($row['email']) : null;
            $notes = !empty($row['notes']) ? trim($row['notes']) : null;
            $created = !empty($row['createdAt']) ? date('Y-m-d H:i:s', (int) $row['createdAt']) : date('Y-m-d H:i:s');

            $user = null;
            if ($tel) {
                $user = User::where('cep_telefon', $tel)->first();
            }
            if (!$user) {
                // Telefon yoksa planla_id tabanli sentetik placeholder; cep_telefon NOT NULL olsa da kaydeder
                $effectiveTel = $tel ?: ('planla_' . substr($planlaId, -10));
                $user = new User();
                $user->name = $ad;
                $user->cep_telefon = $effectiveTel;
                if ($email) $user->email = $email;
                $user->ozel_notlar = $notes;
                $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $user->created_at = $created;
                $user->save();
            }

            $portfoy = MusteriPortfoy::where('user_id', $user->id)->where('salon_id', $this->salonId)->first();
            if (!$portfoy) {
                $portfoy = new MusteriPortfoy();
                $portfoy->user_id = $user->id;
                $portfoy->salon_id = $this->salonId;
                $portfoy->aktif = 1;
                $portfoy->ozel_notlar = $notes;
                $portfoy->created_at = $created;
                $portfoy->save();
            }

            if ($planlaId) $this->musteriMap[$planlaId] = $user->id;
            $this->counts['musteri']++;
        }
        $this->log('Musteri aktarim: ' . $this->counts['musteri']);
    }

    private function telefonNormalize($tel)
    {
        if (!$tel) return null;
        $tel = preg_replace('/[^0-9]/', '', (string) $tel);
        $tel = preg_replace('/^90/', '', $tel);
        $tel = preg_replace('/^0/', '', $tel);
        return $tel ?: null;
    }

    // ---- Randevular ----------------------------------------------------

    public function importRandevular()
    {
        $this->log('Randevular cekiliyor (category=appointments)...');

        // Randevu aktariminda customer/service/employee ID'leri Planla ObjectId.
        // Randevu kacmamasi icin map'te olmayan Planla musterilerini otomatik olustur
        // (telefonsuz olsalar da placeholder olarak).
        $this->ensureAllMusterilerMapped();
        if (empty($this->hizmetMap))  $this->buildHizmetMap();
        if (empty($this->personelMap)) $this->buildPersonelMap();

        $items = $this->fetchCategory('appointments');

        $i = 0;
        foreach ($items as $row) {
            $i++;
            $tarih = isset($row['appointmentDate']) ? $row['appointmentDate'] : null;
            $saat  = isset($row['appointmentTime']) ? $row['appointmentTime'] : null;
            if (!$tarih || !$saat) { $this->counts['skipped']++; continue; }
            if (strlen($saat) === 5) $saat .= ':00';

            $customerIds = isset($row['customer']) && is_array($row['customer']) ? $row['customer'] : [];
            $serviceIds  = isset($row['service'])  && is_array($row['service'])  ? $row['service']  : [];
            $employeeIds = isset($row['employee']) && is_array($row['employee']) ? $row['employee'] : [];
            $status      = isset($row['status'])   && is_array($row['status'])   ? $row['status']   : [];

            $planlaCustomerId = reset($customerIds) ?: null;
            $userId = $planlaCustomerId && isset($this->musteriMap[$planlaCustomerId])
                ? $this->musteriMap[$planlaCustomerId] : null;
            if (!$userId) { $this->counts['skipped']++; continue; }

            $personelId = null;
            $planlaEmpId = reset($employeeIds) ?: null;
            if ($planlaEmpId && isset($this->personelMap[$planlaEmpId])) {
                $personelId = $this->personelMap[$planlaEmpId];
            }

            $r = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$r) $r = new Randevular();
            $r->tarih = $tarih;
            $r->saat = $saat;
            $r->user_id = $userId;
            $r->salon_id = $this->salonId;
            $r->durum = 1;
            $r->salon = 1;
            if ($personelId) $r->olusturan_personel_id = $personelId;
            $statusStr = strtolower(implode(',', $status));
            if (strpos($statusStr, 'complet') !== false || strpos($statusStr, 'done') !== false) {
                $r->randevuya_geldi = 1;
            } elseif (strpos($statusStr, 'cancel') !== false || strpos($statusStr, 'no') !== false || strpos($statusStr, 'absent') !== false) {
                $r->randevuya_geldi = 0;
            }
            if (!empty($row['notes'])) $r->personel_notu = $row['notes'];
            if (!empty($row['createdAt'])) $r->created_at = date('Y-m-d H:i:s', (int) $row['createdAt']);
            $r->save();

            // Hizmetleri randevuya ekle
            $baslangic = $saat;
            foreach ($serviceIds as $sid) {
                if (!isset($this->hizmetMap[$sid])) continue;
                $localHizmetId = $this->hizmetMap[$sid];
                $sh = SalonHizmetler::where('salon_id', $this->salonId)->where('hizmet_id', $localHizmetId)->first();
                $sure = $sh ? (int) $sh->sure_dk : 30;
                if ($sure <= 0) $sure = 30;
                $bitis = date('H:i:s', strtotime('+' . $sure . ' minutes', strtotime($baslangic)));

                $rh = RandevuHizmetler::where('randevu_id', $r->id)->where('hizmet_id', $localHizmetId)->first();
                if (!$rh) $rh = new RandevuHizmetler();
                $rh->randevu_id = $r->id;
                $rh->hizmet_id = $localHizmetId;
                $rh->saat = $baslangic;
                $rh->saat_bitis = $bitis;
                $rh->sure_dk = $sure;
                if ($personelId) $rh->personel_id = $personelId;
                $rh->save();
                $baslangic = $bitis;
            }
            $this->counts['randevu']++;
            if ($i % 500 === 0) $this->log("  ..{$i} randevu islendi");
        }
        $this->log('Randevu aktarim: ' . $this->counts['randevu']);
    }

    // ---- Haritalari disardan yeniden kur (command ayri ayri tip secerse) ----

    private function buildMusteriMap()
    {
        // Planla _id -> local user_id: telefondan match (import sonrasi sync).
        foreach ($this->fetchCategory('customers') as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $tel = $this->telefonNormalize(isset($row['phone']) ? $row['phone'] : null);
            if (!$planlaId || !$tel) continue;
            $u = User::where('cep_telefon', $tel)->first();
            if ($u) $this->musteriMap[$planlaId] = $u->id;
        }
    }

    /**
     * Randevu aktarimi icin: Planla'daki tum musterilerin map'te karsiligi olmasini garanti eder.
     * Eksik olanlar (telefonsuz, dbde bulunamayan) icin placeholder User + portfoy yaratilir.
     * Bu sayede hicbir randevu 'customer bulunamadi' sebebiyle kaybedilmez.
     */
    private function ensureAllMusterilerMapped()
    {
        $this->log('Musteri map kuruluyor (eksik olanlar olusturuluyor)...');
        $yeni = 0;
        $bulundu = 0;
        $hata = 0;
        $hataOrnek = null;
        foreach ($this->fetchCategory('customers') as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            if (!$planlaId) continue;
            if (isset($this->musteriMap[$planlaId])) { $bulundu++; continue; }

            $ad = isset($row['fullName']) ? trim($row['fullName']) : '';
            if (!$ad) $ad = 'Planla ' . substr($planlaId, -6);
            $tel = $this->telefonNormalize(isset($row['phone']) ? $row['phone'] : null);
            $email = !empty($row['email']) ? trim($row['email']) : null;
            $notes = !empty($row['notes']) ? trim($row['notes']) : null;
            $created = !empty($row['createdAt']) ? date('Y-m-d H:i:s', (int) $row['createdAt']) : date('Y-m-d H:i:s');

            $user = null;
            if ($tel) {
                $user = User::where('cep_telefon', $tel)->first();
            }
            if (!$user) {
                // Telefon yoksa sentetik bir placeholder ver - boylece cep_telefon NOT NULL kisidi atlanir,
                // duplicate olmamasi icin planla_id tabanli unique string kullaniyoruz
                $effectiveTel = $tel ?: ('planla_' . substr($planlaId, -10));
                try {
                    $user = new User();
                    $user->name = $ad;
                    $user->cep_telefon = $effectiveTel;
                    if ($email) $user->email = $email;
                    $user->ozel_notlar = $notes;
                    $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                    $user->created_at = $created;
                    $user->save();
                    $yeni++;
                } catch (\Exception $e) {
                    $hata++;
                    if (!$hataOrnek) $hataOrnek = $e->getMessage();
                    Log::warning('[PlanlaImporter] user olusturma hatasi: ' . $e->getMessage() . ' | planla_id=' . $planlaId);
                    continue;
                }
            } else {
                $bulundu++;
            }

            try {
                $portfoy = MusteriPortfoy::where('user_id', $user->id)->where('salon_id', $this->salonId)->first();
                if (!$portfoy) {
                    $portfoy = new MusteriPortfoy();
                    $portfoy->user_id = $user->id;
                    $portfoy->salon_id = $this->salonId;
                    $portfoy->aktif = 1;
                    $portfoy->ozel_notlar = $notes;
                    $portfoy->created_at = $created;
                    $portfoy->save();
                }
            } catch (\Exception $e) {
                Log::warning('[PlanlaImporter] portfoy olusturma hatasi: ' . $e->getMessage() . ' | user_id=' . $user->id);
            }
            $this->musteriMap[$planlaId] = $user->id;
        }
        $this->log("Musteri map: bulundu={$bulundu}, yeni={$yeni}, hata={$hata}, map=" . count($this->musteriMap));
        if ($hataOrnek) $this->log("Ornek hata: " . $hataOrnek);
    }

    private function buildHizmetMap()
    {
        foreach ($this->fetchCategory('services') as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $title = isset($row['title']) ? trim($row['title']) : '';
            if (!$planlaId || !$title) continue;
            $h = Hizmetler::where('hizmet_adi', $title)->first();
            if ($h) $this->hizmetMap[$planlaId] = $h->id;
        }
    }

    private function buildPersonelMap()
    {
        foreach ($this->fetchCategory('employees') as $row) {
            $planlaId = isset($row['_id']) ? $row['_id'] : null;
            $ad = isset($row['fullName']) ? trim($row['fullName']) : '';
            if (!$planlaId || !$ad) continue;
            $p = Personeller::where('personel_adi', $ad)->where('salon_id', $this->salonId)->first();
            if ($p) $this->personelMap[$planlaId] = $p->id;
        }
    }

    private function log($msg)
    {
        if ($this->out) $this->out->writeln($msg);
        Log::info('[PlanlaImporter] ' . $msg);
    }
}
