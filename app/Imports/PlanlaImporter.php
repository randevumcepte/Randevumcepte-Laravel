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
use App\Randevular;
use App\RandevuHizmetler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Planla.co'dan cekilen JSON verileri randevumcepte modellerine map eder.
 *
 * NOT: Gercek planla endpoint yapisi probe asamasindan sonra netlestiginde
 * fetchMusteriler / fetchHizmetler / fetchRandevular metodlarindaki endpoint + key
 * eslesmeleri kesinlestirilecek. Suan en yaygin pattern'lari deneyen generic bir
 * yapi var; birden fazla key varyantini destekleyerek calisir.
 */
class PlanlaImporter
{
    /** @var PlanlaClient */
    private $client;
    /** @var int */
    private $salonId;
    /** @var OutputInterface */
    private $out;
    private $counts = ['musteri' => 0, 'hizmet' => 0, 'randevu' => 0];
    /** @var array planla_service_id => local hizmet_id */
    private $hizmetMap = [];
    /** @var array planla_customer_id => local user_id */
    private $musteriMap = [];
    /** @var array planla_staff_id => local personel_id */
    private $personelMap = [];

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

    // ---- Fetch katmani -------------------------------------------------

    /**
     * Sayfalanmis veri ceker. Donus: dizi (tum items). Birden fazla path ve param kombinasyonunu dener.
     */
    private function fetchPaginated(array $paths, $pageParam = 'page', $perPageParam = 'per_page', $perPage = 100)
    {
        foreach ($paths as $path) {
            $all = [];
            $page = 1;
            $tries = 0;
            while ($tries < 200) {
                $resp = $this->client->getJson($path, [$pageParam => $page, $perPageParam => $perPage]);
                if (!is_array($resp)) {
                    break;
                }
                $items = $this->extractItems($resp);
                if (empty($items)) {
                    break;
                }
                $all = array_merge($all, $items);
                // Pagination detection
                $hasMore = false;
                if (isset($resp['meta']['current_page'], $resp['meta']['last_page'])) {
                    $hasMore = $resp['meta']['current_page'] < $resp['meta']['last_page'];
                } elseif (isset($resp['current_page'], $resp['last_page'])) {
                    $hasMore = $resp['current_page'] < $resp['last_page'];
                } elseif (isset($resp['next_page_url'])) {
                    $hasMore = !empty($resp['next_page_url']);
                } else {
                    $hasMore = count($items) >= $perPage;
                }
                if (!$hasMore) break;
                $page++;
                $tries++;
            }
            if (!empty($all)) {
                $this->log("  path={$path} -> " . count($all) . " kayit");
                return $all;
            }
        }
        return [];
    }

    private function extractItems(array $resp)
    {
        foreach (['data', 'items', 'results', 'rows', 'records'] as $k) {
            if (isset($resp[$k]) && is_array($resp[$k])) {
                // Laravel paginator nested: data.data
                if (isset($resp[$k]['data']) && is_array($resp[$k]['data'])) {
                    return $resp[$k]['data'];
                }
                // index-0 varsa list'tir
                if (isset($resp[$k][0]) || empty($resp[$k])) {
                    return $resp[$k];
                }
            }
        }
        // top level list
        if (isset($resp[0])) return $resp;
        return [];
    }

    private function pick(array $row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                return $row[$k];
            }
            // nested dotted
            if (strpos($k, '.') !== false) {
                $parts = explode('.', $k);
                $v = $row;
                foreach ($parts as $p) {
                    if (is_array($v) && array_key_exists($p, $v)) { $v = $v[$p]; }
                    else { $v = null; break; }
                }
                if ($v !== null && $v !== '') return $v;
            }
        }
        return $default;
    }

    // ---- Hizmetler -----------------------------------------------------

    public function importHizmetler()
    {
        $this->log('Hizmetler cekiliyor...');
        $items = $this->fetchPaginated([
            '/services',
        ]);
        if (empty($items)) {
            $this->log('Hizmet bulunamadi (endpoint dogru degil olabilir).');
            return;
        }

        foreach ($items as $row) {
            $name = $this->pick($row, ['hizmet_adi', 'ad', 'name', 'title', 'service_name']);
            if (!$name) continue;
            $kategoriAdi = $this->pick($row, ['kategori.ad', 'kategori_adi', 'category.name', 'category_name', 'category'], 'Genel');
            $sure = (int) $this->pick($row, ['sure_dk', 'sure', 'duration', 'minutes', 'duration_minutes'], 30);
            $fiyat = (float) $this->pick($row, ['fiyat', 'price', 'amount'], 0);
            $planlaId = $this->pick($row, ['id', 'uuid']);

            $kategoriId = $this->kategoriEkleVeyaGetir($kategoriAdi);

            $hizmet = Hizmetler::where('hizmet_adi', $name)->where('hizmet_kategori_id', $kategoriId)->first();
            if (!$hizmet) {
                $hizmet = new Hizmetler();
                $hizmet->hizmet_adi = $name;
                $hizmet->hizmet_kategori_id = $kategoriId;
                $hizmet->ozel_hizmet = true;
                if (\Schema::hasColumn('hizmetler', 'salon_id')) {
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
            $sh->fiyat = $fiyat;
            $sh->save();
            $this->ensureKategoriRenk($kategoriId);

            if ($planlaId) $this->hizmetMap[$planlaId] = $hizmet->id;
            $this->counts['hizmet']++;
        }
        $this->log('Hizmet aktarim: ' . $this->counts['hizmet']);
    }

    private function kategoriEkleVeyaGetir($ad)
    {
        $k = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $ad)->first();
        if (!$k) {
            $k = new Hizmet_Kategorisi();
            $k->hizmet_kategorisi_adi = $ad;
            $k->save();
        }
        return $k->id;
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
        $this->log('Musteriler cekiliyor...');
        $items = $this->fetchPaginated([
            '/customers',
        ]);
        if (empty($items)) {
            $this->log('Musteri bulunamadi.');
            return;
        }

        foreach ($items as $row) {
            $ad   = $this->pick($row, ['ad_soyad', 'name', 'full_name', 'isim_soyisim']);
            if (!$ad) {
                $ad = trim($this->pick($row, ['ad', 'first_name'], '') . ' ' . $this->pick($row, ['soyad', 'last_name'], ''));
            }
            if (!$ad) continue;
            $tel = $this->pick($row, ['cep_telefon', 'phone', 'mobile', 'telefon', 'gsm']);
            $tel = $this->telefonNormalize($tel);
            $email = $this->pick($row, ['email', 'eposta', 'mail']);
            $cinsiyet = $this->cinsiyetMap($this->pick($row, ['cinsiyet', 'gender']));
            $dogum = $this->pick($row, ['dogum_tarihi', 'birthdate', 'dob', 'birth_date']);
            $tc = $this->pick($row, ['tc_kimlik_no', 'tc', 'tckn', 'identity']);
            $not = $this->pick($row, ['aciklama', 'not', 'notes', 'description']);
            $planlaId = $this->pick($row, ['id', 'uuid']);

            $user = null;
            if ($tel) {
                $user = User::where('cep_telefon', $tel)->first();
            }
            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }
            if (!$user) {
                $user = new User();
                $user->name = $ad;
                $user->cep_telefon = $tel;
                if ($email) $user->email = $email;
                if ($cinsiyet !== null) $user->cinsiyet = $cinsiyet;
                if ($tc) $user->tc_kimlik_no = $tc;
                if ($dogum) $user->dogum_tarihi = $this->tarihNormalize($dogum);
                $user->ozel_notlar = $not;
                $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $user->save();
            }

            $portfoy = MusteriPortfoy::where('user_id', $user->id)->where('salon_id', $this->salonId)->first();
            if (!$portfoy) {
                $portfoy = new MusteriPortfoy();
                $portfoy->user_id = $user->id;
                $portfoy->salon_id = $this->salonId;
                $portfoy->aktif = 1;
                $portfoy->ozel_notlar = $not;
                $portfoy->save();
            }

            if ($planlaId) $this->musteriMap[$planlaId] = $user->id;
            $this->counts['musteri']++;
        }
        $this->log('Musteri aktarim: ' . $this->counts['musteri']);
    }

    private function cinsiyetMap($v)
    {
        if ($v === null || $v === '') return null;
        $v = mb_strtolower((string) $v);
        if (in_array($v, ['kadin', 'kadın', 'k', 'female', 'f', '0'], true)) return 0;
        if (in_array($v, ['erkek', 'e', 'male', 'm', '1'], true)) return 1;
        return null;
    }

    private function telefonNormalize($tel)
    {
        if (!$tel) return null;
        $tel = preg_replace('/[^0-9]/', '', $tel);
        $tel = preg_replace('/^90/', '', $tel);
        $tel = preg_replace('/^0/', '', $tel);
        return $tel ?: null;
    }

    private function tarihNormalize($t)
    {
        if (!$t) return null;
        $ts = strtotime($t);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    // ---- Randevular ----------------------------------------------------

    public function importRandevular()
    {
        $this->log('Randevular cekiliyor...');
        // Appointments/bookings SPA route'u net degil; sign-in sonrasi frontend'de /statistics
        // veya /customers detayinda yukleniyor olabilir. Once common path'lari deneyelim.
        $items = $this->fetchPaginated([
            '/appointments', '/bookings', '/schedules', '/calendar',
        ]);
        if (empty($items)) {
            $this->log('Randevu bulunamadi.');
            return;
        }

        foreach ($items as $row) {
            $tarih = $this->tarihNormalize($this->pick($row, ['tarih', 'date', 'start_date', 'appointment_date']));
            $saat  = $this->pick($row, ['saat', 'time', 'start_time']);
            if (!$saat) {
                $start = $this->pick($row, ['start', 'start_at', 'start_datetime', 'begins_at']);
                if ($start) {
                    $ts = strtotime($start);
                    if ($ts) {
                        if (!$tarih) $tarih = date('Y-m-d', $ts);
                        $saat = date('H:i:s', $ts);
                    }
                }
            } else {
                $saat = date('H:i:s', strtotime($saat));
            }
            if (!$tarih || !$saat) continue;

            $planlaMusteriId = $this->pick($row, ['musteri_id', 'customer_id', 'client_id', 'musteri.id', 'customer.id']);
            $userId = isset($this->musteriMap[$planlaMusteriId]) ? $this->musteriMap[$planlaMusteriId] : null;
            if (!$userId) {
                $tel = $this->telefonNormalize($this->pick($row, ['musteri.cep_telefon', 'customer.phone', 'telefon', 'phone']));
                if ($tel) {
                    $u = User::where('cep_telefon', $tel)->first();
                    if ($u) $userId = $u->id;
                }
            }
            if (!$userId) continue;

            $planlaId = $this->pick($row, ['id', 'uuid']);
            $r = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$r) $r = new Randevular();
            $r->tarih = $tarih;
            $r->saat = $saat;
            $r->user_id = $userId;
            $r->salon_id = $this->salonId;
            $r->durum = 1;
            $r->salon = 1;
            $durumStr = (string) $this->pick($row, ['durum', 'status', 'state'], '');
            if (stripos($durumStr, 'geldi') !== false || in_array(mb_strtolower($durumStr), ['completed', 'attended', 'done'])) {
                $r->randevuya_geldi = 1;
            } elseif (stripos($durumStr, 'gelmedi') !== false || in_array(mb_strtolower($durumStr), ['no-show', 'no_show', 'missed'])) {
                $r->randevuya_geldi = 0;
            }
            $r->save();

            $hizmetlerArr = $this->pick($row, ['hizmetler', 'services', 'items'], []);
            if (!is_array($hizmetlerArr)) $hizmetlerArr = [];
            $baslangic = $saat;
            foreach ($hizmetlerArr as $h) {
                $hId = null;
                $pHId = $this->pick(is_array($h) ? $h : [], ['hizmet_id', 'service_id', 'hizmet.id', 'service.id', 'id']);
                if ($pHId && isset($this->hizmetMap[$pHId])) $hId = $this->hizmetMap[$pHId];
                if (!$hId) {
                    $hName = $this->pick(is_array($h) ? $h : [], ['hizmet_adi', 'name', 'hizmet.ad', 'service.name']);
                    if ($hName) {
                        $hz = Hizmetler::where('hizmet_adi', $hName)->first();
                        if ($hz) $hId = $hz->id;
                    }
                }
                if (!$hId) continue;
                $sure = (int) $this->pick(is_array($h) ? $h : [], ['sure_dk', 'duration', 'minutes'], 30);
                $bitis = date('H:i:s', strtotime('+' . $sure . ' minutes', strtotime($baslangic)));

                $rh = RandevuHizmetler::where('randevu_id', $r->id)->where('hizmet_id', $hId)->first();
                if (!$rh) $rh = new RandevuHizmetler();
                $rh->randevu_id = $r->id;
                $rh->hizmet_id = $hId;
                $rh->saat = $baslangic;
                $rh->saat_bitis = $bitis;
                $rh->sure_dk = $sure;
                $rh->save();
                $baslangic = $bitis;
            }

            if ($planlaId) { /* map tutmuyoruz randevu icin */ }
            $this->counts['randevu']++;
        }
        $this->log('Randevu aktarim: ' . $this->counts['randevu']);
    }

    private function log($msg)
    {
        if ($this->out) $this->out->writeln($msg);
        Log::info('[PlanlaImporter] ' . $msg);
    }
}
