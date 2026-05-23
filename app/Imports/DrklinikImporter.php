<?php

namespace App\Imports;

use App\Services\DrklinikClient;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\SalonHizmetKategoriRenkleri;
use App\Personeller;
use App\PersonelCalismaSaatleri;
use App\IsletmeYetkilileri;
use App\Urunler;
use App\Odalar;
use App\OdaRenkleri;
use App\User;
use App\MusteriPortfoy;
use App\Randevular;
use App\RandevuHizmetler;
use App\Adisyonlar;
use App\AdisyonHizmetler;
use App\Tahsilatlar;
use App\TahsilatHizmetler;
use App\TahsilatUrunler;
use App\Masraflar;
use App\MasrafKategorisi;
use App\AdisyonPaketSeanslar;
use App\AdisyonUrunler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * uygulama.drklinik.net'ten cekilen verileri randevumcepte modellerine aktarir.
 * Site ASP.NET WebForms - veri HTML tablo olarak render ediliyor.
 */
class DrklinikImporter
{
    /** @var DrklinikClient */
    private $client;
    /** @var int */
    private $salonId;
    private $out;
    private $counts = ['hizmet' => 0, 'personel' => 0, 'urun' => 0, 'oda' => 0, 'musteri' => 0, 'randevu' => 0, 'satis' => 0, 'tahsilat' => 0, 'skipped' => 0];
    private $odaMap = []; // drklinik oda id => local oda id
    private $musteriMap = []; // drklinik musteri id => local user id
    private $drklinikUserCache = []; // drklinik musid => local user id (musid bazli)
    private $defaultKategoriId = null;

    public function __construct(DrklinikClient $client, $salonId, $out = null)
    {
        $this->client = $client;
        $this->salonId = (int) $salonId;
        $this->out = $out;
    }

    public function summary()
    {
        return $this->counts;
    }

    /**
     * hizmet_listesi.aspx tablosu: 2 sutun (Hizmet Adi, Fiyat).
     * Birimler DDL_Birim dropdown'unda; her birim icin postback ile filtreleyip
     * o birim hizmetlerini bizim Hizmet_Kategorisi olarak ekleriz.
     */
    public function importHizmetler()
    {
        $this->log('Hizmetler cekiliyor (hizmet_listesi.aspx)...');
        $initialHtml = $this->client->getHtml('/hizmet_listesi.aspx', 'hizmet_initial');
        if ($initialHtml === '') { $this->log('Sayfa cekilemedi.'); return; }

        // DDL_Birim option'larini cikar
        $birimler = $this->parseSelectOptions($initialHtml, 'DDL_Birim');
        if (empty($birimler)) {
            $this->log('Birim listesi bulunamadi - default kategori ile aktarilacak.');
            $this->importHizmetlerDefault($initialHtml);
            return;
        }

        $this->log('  ' . count($birimler) . ' birim bulundu.');
        $eklendi = 0;
        foreach ($birimler as $val => $ad) {
            if ($val === '0' || $val === '') continue; // "Birim Seciniz" placeholder
            $kategoriAd = trim(html_entity_decode($ad, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            // mb_convert_case Turkce I icin "i̇" (combining dot above) uretiyor;
            // kategori adini drklinik ham haliyle birakmak en saglam yol.
            $kategoriId = $this->kategoriEkleVeyaGetir($kategoriAd);

            // Birim secip postback yap
            $birimHtml = $this->client->postBack('/hizmet_listesi.aspx', 'DDL_Birim', '', [
                'DDL_Birim' => $val,
            ]);
            if ($birimHtml === null) { $this->log("  [{$kategoriAd}] postback basarisiz."); continue; }

            $rows = $this->parseTableRows($birimHtml);
            $birimEklendi = 0;
            foreach ($rows as $row) {
                if (count($row) < 2) continue;
                $hadi = trim($row[0]);
                if (!$hadi) continue;
                $fiyat = (float) preg_replace('/[^0-9.,]/', '', str_replace(',', '.', $row[1] ?? ''));
                if ($this->saveHizmet($hadi, $fiyat, $kategoriId)) {
                    $birimEklendi++;
                    $eklendi++;
                }
            }
            $this->log("  [{$kategoriAd}]: {$birimEklendi} hizmet");
            usleep(500000); // 0.5s rate limit
        }
        $this->counts['hizmet'] = $eklendi;
        $this->log("Hizmet aktarim toplam: {$eklendi}");
    }

    private function importHizmetlerDefault($html)
    {
        $kategoriId = $this->kategoriEkleVeyaGetir('Drklinik');
        $rows = $this->parseTableRows($html);
        foreach ($rows as $row) {
            if (count($row) < 2) continue;
            $ad = trim($row[0]);
            if (!$ad) continue;
            $fiyat = (float) preg_replace('/[^0-9.,]/', '', str_replace(',', '.', $row[1] ?? ''));
            if ($this->saveHizmet($ad, $fiyat, $kategoriId)) $this->counts['hizmet']++;
        }
        $this->log("Hizmet aktarim (default kategori): " . $this->counts['hizmet']);
    }

    private function saveHizmet($ad, $fiyat, $kategoriId)
    {
        // Ayni isimde urun varsa hizmet olarak EKLEME (drklinik bazi urunleri
        // hizmet listesinde "(H)" suffix ile gosterebiliyor).
        if ($this->isUrunName($ad)) {
            $this->log("  [skip-urun] '{$ad}' Urunler tablosunda kayitli, hizmet olarak eklenmeyecek.");
            return false;
        }
        $hizmet = Hizmetler::where('hizmet_adi', $ad)->where('hizmet_kategori_id', $kategoriId)->first();
        if (!$hizmet) {
            $hizmet = new Hizmetler();
            $hizmet->hizmet_adi = $ad;
            $hizmet->hizmet_kategori_id = $kategoriId;
            $hizmet->ozel_hizmet = true;
            if (Schema::hasColumn('hizmetler', 'salon_id')) $hizmet->salon_id = $this->salonId;
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
        $sh->sure_dk = 30;
        $sh->baslangic_fiyat = $fiyat;
        $sh->son_fiyat = $fiyat;
        $sh->save();
        $this->ensureKategoriRenk($kategoriId);
        return true;
    }

    private function kategoriEkleVeyaGetir($ad)
    {
        // Mevcut kategoriler arasinda trKey-bazli lookup (combining mark
        // sorunlu eski kayitlari da yakalar; varsa adini duzelterek dondurur)
        $needle = $this->trKey($ad);
        $existing = Hizmet_Kategorisi::all();
        foreach ($existing as $k) {
            if ($this->trKey($k->hizmet_kategorisi_adi) === $needle) {
                if ($k->hizmet_kategorisi_adi !== $ad) {
                    $k->hizmet_kategorisi_adi = $ad; // bozuk eski adi temiz hale guncelle
                    $k->save();
                }
                return $k->id;
            }
        }
        $k = new Hizmet_Kategorisi();
        $k->hizmet_kategorisi_adi = $ad;
        $k->save();
        return $k->id;
    }

    /**
     * calisanmodulu.aspx tablosu: Ad | Soyad | Telefon | Unvan | Duzenle | Sil
     * Tam Planla akisini uygular: IsletmeYetkilileri + Personeller + model_has_roles + calisma saatleri.
     */
    public function importPersoneller()
    {
        $this->log('Personel listesi cekiliyor (calisanmodulu.aspx)...');
        $listHtml = $this->client->getHtml('/calisanmodulu.aspx', 'personel_listesi');
        if ($listHtml === '') { $this->log('Liste sayfasi cekilemedi.'); return; }

        // Liste sayfasindan tum personel id'leri cikar (Duzenle linklerinden)
        preg_match_all('#calisan_ekle\.aspx\?id=(\d+)&t=d#', $listHtml, $idm);
        $ids = array_values(array_unique($idm[1]));
        $this->log('  ' . count($ids) . ' personel id bulundu.');

        $eklendi = 0;
        foreach ($ids as $idx => $id) {
            // Her personelin detay/duzenle formunu cek - ad, soyad, telefon, unvan dolu gelir
            $detail = $this->client->getHtml('/calisan_ekle.aspx?id=' . $id . '&t=d');
            if ($detail === '') continue;
            $ad    = $this->extractInputValue($detail, 'TB_Ad');
            $soyad = $this->extractInputValue($detail, 'TB_Soyad');
            $tel   = $this->telefonNormalize($this->extractInputValue($detail, 'TB_Telefon'));
            $unvan = $this->extractInputValue($detail, 'TB_Unvan');
            $tamAd = trim($ad . ' ' . $soyad);
            if ($tamAd === '') continue;

            if ($idx > 0 && $idx % 10 === 0) $this->log("  ..{$idx}/" . count($ids) . " personel okundu");
            usleep(200000);
            $tamAd  = trim($ad . ' ' . $soyad);
            if ($tamAd === '') continue;

            // Mevcut personel varsa SADECE eksik alanlari (telefon, unvan) guncelle, yetkili/role/calisma saatleri ekleme
            $p = Personeller::where('personel_adi', $tamAd)->where('salon_id', $this->salonId)->first();
            if ($p) {
                $upd = false;
                if (!$p->cep_telefon && $tel) { $p->cep_telefon = $tel; $upd = true; }
                if ($unvan && Schema::hasColumn('personeller', 'unvan') && !$p->unvan) { $p->unvan = $unvan; $upd = true; }
                if ($upd) $p->save();
                // Yetkili'ye de gsm1 ekle
                if ($p->yetkili_id && $tel) {
                    $y = IsletmeYetkilileri::find($p->yetkili_id);
                    if ($y && !$y->gsm1) { $y->gsm1 = $tel; $y->save(); }
                }
                continue;
            }

            // 1) IsletmeYetkilileri (login hesabi)
            $yetkili = new IsletmeYetkilileri();
            $yetkili->name = $tamAd;
            $yetkili->gsm1 = $tel;
            $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $yetkili->password = Hash::make(Str::random(10));
            $yetkili->aktif = 1;
            $yetkili->save();

            // 2) Renk + sira hesapla
            $sonSira = Personeller::where('salon_id', $this->salonId)->max('takvim_sirasi');
            $sira = ($sonSira ? $sonSira : 0) + 1;
            $sonRenk = Personeller::where('salon_id', $this->salonId)->orderBy('id', 'desc')->value('renk');
            $renk = (!$sonRenk || $sonRenk >= 10) ? 1 : $sonRenk + 1;

            // 3) Personeller
            $p = new Personeller();
            $p->personel_adi = $tamAd;
            $p->cep_telefon = $tel;
            $p->salon_id = $this->salonId;
            $p->yetkili_id = $yetkili->id;
            $p->role_id = 5;
            $p->aktif = 1;
            $p->takvimde_gorunsun = 1;
            $p->takvim_sirasi = $sira;
            $p->renk = $renk;
            if ($unvan && Schema::hasColumn('personeller', 'unvan')) $p->unvan = $unvan;
            $p->save();

            // 4) model_has_roles
            DB::insert(
                'INSERT INTO model_has_roles (role_id, model_type, model_id, salon_id) VALUES (?, ?, ?, ?)',
                [5, 'App\\IsletmeYetkilileri', $yetkili->id, $this->salonId]
            );

            // 5) PersonelCalismaSaatleri (7 gun, default 09:00-21:00 acik, pazar kapali)
            for ($g = 1; $g <= 7; $g++) {
                $pcs = new PersonelCalismaSaatleri();
                $pcs->personel_id = $p->id;
                $pcs->haftanin_gunu = $g;
                $pcs->calisiyor = ($g === 7) ? 0 : 1;
                $pcs->baslangic_saati = '09:00';
                $pcs->bitis_saati = '21:00';
                $pcs->save();
            }
            $eklendi++;
            $this->counts['personel']++;
        }
        $this->log("Personel aktarim: {$eklendi} yeni");
    }

    /**
     * Randevular: gunlukrandevulistesi.aspx + BTN_Ara, hafta-hafta tarama.
     * Server hard cap 50 satır/postback - 1 haftada genelde <50 randevu olur.
     *
     * Sutunlar (20 td): td[2]=Tarih td[3]=Bas td[4]=Bit td[5]=Ad Soyad
     * td[8]=Telefon td[10]=Birim td[11]=Calisan td[12]=Oda td[15]=Durum
     *
     * Her satirdan: User+Portfoy + Randevu + RandevuHizmetler + Adisyon + AdisyonHizmetler
     */
    public function importRandevular($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2018-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2026-12-31');
        $this->log('Randevular cekiliyor: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end) . ' (haftalik)...');

        // Randevular icin gerekli ad-bazli maplari kur
        $odaMapByName = $this->buildOdaMapByName();
        $personelMap  = $this->buildPersonelMapByName();
        $hizmetMap    = $this->buildHizmetMapByBirim();

        $weekStart = $start;
        $iter = 0;
        while ($weekStart <= $end) {
            $weekEnd = min($end, strtotime('+6 days', $weekStart));
            $eklenen = $this->scrapeRange($weekStart, $weekEnd, $personelMap, $hizmetMap, $odaMapByName, 0); // reference olarak gecirildi
            $iter++;
            if ($iter % 10 === 0) $this->log("  ..hafta {$iter} (" . date('Y-m-d', $weekStart) . "..) son_eklenen={$eklenen} toplam_randevu={$this->counts['randevu']}");
            $weekStart = strtotime('+7 days', $weekStart);
        }
        $this->log("Randevu aktarim toplam: {$this->counts['randevu']} (skipped: {$this->counts['skipped']})");
    }

    /**
     * Recursive: bir tarih araliginda data cek; eger satir sayisi cap'e (50)
     * dayanirsa aralik ikiye bolunur ve her yarisi icin yine cagrilir.
     * Boylece yogun haftalarda eksik veri kalmaz.
     */
    private function scrapeRange($startTs, $endTs, &$personelMap, $hizmetMap, &$odaMapByName, $depth)
    {
        $h = $this->client->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
            'TB_Tarih1' => date('d.m.Y', $startTs),
            'TB_Tarih2' => date('d.m.Y', $endTs),
        ]);
        usleep(300000);
        if ($h === null) return 0;

        // Cap kontrolu: en genis tablonun tr sayisi
        preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
        $maxTr = 0;
        foreach ($tm[1] as $t) {
            if (preg_match_all('~<tr[^>]*>~i', $t, $rm) && count($rm[0]) > $maxTr) $maxTr = count($rm[0]);
        }
        // Cap'e dayandi (>=50) VE aralik bolunebilir (>=2 gun) ise yariya bol
        if ($maxTr >= 50 && ($endTs - $startTs) >= 86400 && $depth < 6) {
            $mid = $startTs + intval(($endTs - $startTs) / 2);
            $a = $this->scrapeRange($startTs, $mid, $personelMap, $hizmetMap, $odaMapByName, $depth + 1);
            $b = $this->scrapeRange($mid + 86400, $endTs, $personelMap, $hizmetMap, $odaMapByName, $depth + 1);
            return $a + $b;
        }
        return $this->processRandevuPage($h, $personelMap, $hizmetMap, $odaMapByName);
    }

    /**
     * Tek seferlik: drklinik'ten haftalik tarama yapip mevcut randevularin
     * RandevuHizmetler.oda_id ve personel_id NULL alanlarini doldurur.
     */
    public function fixRandevuEksikler($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2018-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2026-12-31');
        $this->log('Randevu eksikleri (oda+personel) onariliyor: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end));
        $odaMapByName = $this->buildOdaMapByName();
        $personelMap  = $this->buildPersonelMapByName();

        $weekStart = $start; $iter = 0;
        $stats = ['updateOda' => 0, 'updatePers' => 0, 'unmatchedPers' => [], 'unmatchedOda' => []];
        while ($weekStart <= $end) {
            $weekEnd = min($end, strtotime('+6 days', $weekStart));
            $this->fixScrapeRange($weekStart, $weekEnd, $personelMap, $odaMapByName, $stats, 0);
            $iter++;
            if ($iter % 20 === 0) $this->log("  ..hafta {$iter} oda_upd={$stats['updateOda']} personel_upd={$stats['updatePers']}");
            $weekStart = strtotime('+7 days', $weekStart);
        }
        $this->log("Onarim toplam: oda_upd={$stats['updateOda']}, personel_upd={$stats['updatePers']}");
        $unmatchedPers = $stats['unmatchedPers']; $unmatchedOda = $stats['unmatchedOda'];
        if ($unmatchedPers) {
            $this->log("Eslesemeyen personel adlari (" . count($unmatchedPers) . "): " . implode(' | ', array_keys($unmatchedPers)));
        }
        if ($unmatchedOda) {
            $this->log("Eslesemeyen oda adlari: " . implode(' | ', array_keys($unmatchedOda)));
        }
    }

    /**
     * fixRandevuEksikler icin recursive scraper - cap'e dayanirsa yariya boler.
     */
    private function fixScrapeRange($startTs, $endTs, &$personelMap, &$odaMapByName, &$stats, $depth)
    {
        $h = $this->client->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
            'TB_Tarih1' => date('d.m.Y', $startTs),
            'TB_Tarih2' => date('d.m.Y', $endTs),
        ]);
        usleep(300000);
        if ($h === null) return;

        preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
        $maxTr = 0;
        foreach ($tm[1] as $t) {
            if (preg_match_all('~<tr[^>]*>~i', $t, $rm) && count($rm[0]) > $maxTr) $maxTr = count($rm[0]);
        }
        if ($maxTr >= 50 && ($endTs - $startTs) >= 86400 && $depth < 6) {
            $mid = $startTs + intval(($endTs - $startTs) / 2);
            $this->fixScrapeRange($startTs, $mid, $personelMap, $odaMapByName, $stats, $depth + 1);
            $this->fixScrapeRange($mid + 86400, $endTs, $personelMap, $odaMapByName, $stats, $depth + 1);
            return;
        }

        $rows = $this->parseTableRowsRaw($h);
        foreach ($rows as $rawRow) {
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 16) continue;
            $tarih = $this->tarihNormalize($cells[2] ?? '');
            $saat  = $cells[3] ?? '';
            if (!$tarih || !$saat) continue;
            if (strlen($saat) === 5) $saat .= ':00';

            $tel = $this->telefonNormalize($cells[8] ?? '');
            $calisan = $cells[11] ?? '';
            $oda = $cells[12] ?? '';
            $personelId = $personelMap[$this->trKey($calisan)] ?? null;
            if (!$personelId && $calisan) $personelId = $this->ensurePersonelId($calisan, $personelMap);
            $odaId = $odaMapByName[$this->trKey($oda)] ?? null;
            if (!$odaId && $oda) $odaId = $this->ensureOdaId($oda, $odaMapByName);

            if (!$personelId && !$odaId) continue;

            $userId = null;
            if ($tel) $userId = User::where('cep_telefon', $tel)->value('id');
            if (!$userId) continue;
            $randevu = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$randevu) continue;

            if ($personelId) {
                $u = RandevuHizmetler::where('randevu_id', $randevu->id)
                    ->whereNull('personel_id')->update(['personel_id' => $personelId]);
                $stats['updatePers'] += $u;
                AdisyonHizmetler::where('randevu_id', $randevu->id)
                    ->whereNull('personel_id')->update(['personel_id' => $personelId]);
            }
            if ($odaId) {
                $u = RandevuHizmetler::where('randevu_id', $randevu->id)
                    ->whereNull('oda_id')->update(['oda_id' => $odaId]);
                $stats['updateOda'] += $u;
            }
        }
    }

    /**
     * Lookup icin oda adina gore key'lenmis map. importOdalar'in $this->odaMap'i
     * drklinik_id => local_id seklindedir; bu fonksiyon ad-bazli ayri bir map doner.
     */
    private function buildOdaMapByName()
    {
        $map = [];
        foreach (Odalar::where('salon_id', $this->salonId)->get() as $o) {
            $map[$this->trKey($o->oda_adi)] = $o->id;
        }
        return $map;
    }

    private function buildPersonelMapByName()
    {
        $map = [];
        foreach (Personeller::where('salon_id', $this->salonId)->get() as $p) {
            $map[$this->trKey($p->personel_adi)] = $p->id;
        }
        return $map;
    }

    /**
     * Satislar (=Adisyonlar): genel_kasa_raporu_satis.aspx + BTN_Ara, aylik tarama.
     * Drklinik'te "Satis" bizim "Adisyon" karsiligi - musteriye yapilan toplam islem.
     * Sutunlar (14 td): Btn1 | Btn2 | SatisNo | Tarih | Musteri | Hizmetler(parse) |
     *                   Aciklama | Tutar | Odenen | Kalan | boslar
     * Hizmetler formati: "Ad (N Seans = X TRY)" - virgulle birden fazla olabilir.
     */
    public function importSatislar($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2024-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2030-12-31');
        $this->log('Satislar (=Adisyonlar) cekiliyor: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end) . ' (aylik)...');
        $defaultPers = $this->defaultPersonelId();

        $cur = $start; $iter = 0;
        $eklendi = 0; $atlandi = 0;
        while ($cur <= $end) {
            $monthEnd = min($end, strtotime(date('Y-m-t', $cur)));
            $iter++;
            $h = $this->client->postBack('/genel_kasa_raporu_satis.aspx', 'BTN_Ara', '', [
                'TB_TarihSec1' => date('d.m.Y', $cur),
                'TB_TarihSec2' => date('d.m.Y', $monthEnd),
            ]);
            usleep(500000);
            if ($h !== null) {
                $stats = $this->processSatisPage($h, $defaultPers);
                $eklendi += $stats['eklendi'];
                $atlandi += $stats['atlandi'];
            }
            $this->log("  ..ay {$iter} (" . date('Y-m', $cur) . ") eklendi={$eklendi} atlandi={$atlandi}");
            $cur = strtotime('+1 month', $cur);
        }
        $this->counts['satis'] = $eklendi;
        $this->log("Satis aktarim toplam: {$eklendi} (atlandi={$atlandi})");
    }

    private function processSatisPage($html, $defaultPers)
    {
        $eklendi = 0; $atlandi = 0;
        // Once raw HTML'den her satirin RAW tr ve musid linkini esle
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $html, $trMatches);
        foreach ($trMatches[1] as $tr) {
            // Bu satirin "Musteri Sayfasini Ac" linkindeki musid
            $musid = null;
            if (preg_match('~href="musteri\.aspx\?musid=(\d+)~', $tr, $m)) $musid = $m[1];
            // Td'leri parse
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 10) continue;

            $satisNo = $cells[2] ?? '';
            $tarih   = $this->tarihNormalize($cells[3] ?? '');
            $musteri = trim($cells[4] ?? '');
            $hizmetlerStr = $cells[5] ?? '';
            $aciklama = $cells[6] ?? '';
            $tutarStr = $cells[7] ?? '';
            if (!$satisNo || !$tarih) { $atlandi++; continue; }

            $tutar = $this->paraParse($tutarStr);
            // OnceLikLi: musid ile kesin eslesme
            $userId = $musid ? $this->ensureUserByMusid($musid) : null;
            // Fallback: ad ile (musid yoksa)
            if (!$userId && $musteri) $userId = $this->findUserByNameInSalon($musteri);
            if (!$userId) { $atlandi++; continue; }

            // Adisyon idempotent: drklinik satis_no'yu uygun bir text alanina yaz
            $idMarker = "drklinik:{$satisNo}";
            // Adisyonlar tablosunda hangi text kolonu var bul
            static $notKolonu = null;
            if ($notKolonu === null) {
                foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
                    if (\Schema::hasColumn('adisyonlar', $col)) { $notKolonu = $col; break; }
                }
                if (!$notKolonu) $notKolonu = false; // hicbiri yok
            }

            $ad = null;
            if ($notKolonu) {
                // SADECE marker ile dedup. Her drklinik satisi (SatisNo) kendi
                // adisyonudur. Tarih fallback'i FARKLI satislari birlestirip
                // seanslarin eksik kalmasina yol aciyordu - kaldirildi.
                $ad = Adisyonlar::where('salon_id', $this->salonId)
                    ->where($notKolonu, 'LIKE', '%' . $idMarker . '%')->first();
            } else {
                // Hic text kolonu yoksa son care: salon+user+tarih (eski davranis)
                $ad = Adisyonlar::where('salon_id', $this->salonId)
                    ->where('user_id', $userId)
                    ->where('tarih', $tarih)->first();
            }
            if (!$ad) {
                $ad = new Adisyonlar();
                $ad->user_id = $userId;
                $ad->salon_id = $this->salonId;
                $ad->tarih = $tarih;
                $ad->olusturan_id = $defaultPers;
                if ($notKolonu) {
                    $not = trim($aciklama . ' [' . $idMarker . ']');
                    $ad->{$notKolonu} = $not;
                }
                $ad->save();
            }

            // Hizmetler parse: "Ad (N Seans = X TRY)" virgulle ayri
            $hizmetler = $this->parseHizmetlerStr($hizmetlerStr);
            foreach ($hizmetler as $hv) {
                $seansSayisi = max(1, (int) $hv['seans']);
                $birimFiyat = $hv['tutar'] / $seansSayisi;
                $sh = $this->findSalonHizmetByName($hv['ad']);
                if (!$sh) {
                    $sh = $this->ensureSalonHizmet($hv['ad'], $birimFiyat);
                    if (!$sh) continue;
                }
                $existAh = AdisyonHizmetler::where('adisyon_id', $ad->id)
                    ->where('hizmet_id', $sh['hizmet_id'])->first();
                if ($existAh) continue;
                $ah = new AdisyonHizmetler();
                $ah->adisyon_id = $ad->id;
                $ah->hizmet_id = $sh['hizmet_id'];
                $ah->personel_id = $defaultPers;
                $ah->geldi = 0; // paketten henuz kullanilmamis (default)
                $ah->islem_tarihi = $tarih;
                $ah->islem_saati = '00:00:00';
                $ah->sure = $sh['sure_dk'] ?: 30;
                $ah->fiyat = $hv['tutar']; // TOPLAM tutar (paket yorumu)
                $ah->save();

                // Paket: N>1 ise N adet AdisyonPaketSeanslar (kullanilmamis)
                if ($seansSayisi > 1) {
                    for ($i = 1; $i <= $seansSayisi; $i++) {
                        $aps = new AdisyonPaketSeanslar();
                        $aps->adisyon_hizmet_id = $ah->id;
                        $aps->hizmet_id = $sh['hizmet_id'];
                        $aps->seans_no = $i;
                        $aps->geldi = 0;
                        $aps->save();
                    }
                }
            }
            $eklendi++;
        }
        return ['eklendi' => $eklendi, 'atlandi' => $atlandi];
    }

    /**
     * Bir randevuda paketten seans tuketildi mi? Bu kullanicinin o hizmet icin
     * kullanilmamis ilk AdisyonPaketSeanslar'ini bul, randevu_id ata, geldi=1 yap.
     */
    /**
     * Seans tuketildiginde yeni bir AdisyonPaketSeanslar kaydi olusturur.
     * Eslesen AdisyonHizmetler (kullanicinin bu hizmet icin acik paketi)
     * bulunursa adisyon_hizmet_id ile baglanir; yoksa bagimsiz.
     *
     * Idempotent: ayni randevu_id icin zaten kayit varsa eklenmez.
     */
    private function paketSeansiTuket($userId, $hizmetId, $randevuId, $tarih, $saat, $personelId, $odaId)
    {
        if (!$randevuId) return false;
        $already = AdisyonPaketSeanslar::where('randevu_id', $randevuId)->first();
        if ($already) return false;

        // Acik (henuz seans_sayisi'ndan az tuketim olan) AdisyonHizmetler bul
        $adisyonHizmetId = null;
        if ($hizmetId) {
            $rows = \DB::table('adisyon_hizmetler as ah')
                ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
                ->where('a.user_id', $userId)
                ->where('a.salon_id', $this->salonId)
                ->where('ah.hizmet_id', $hizmetId)
                ->whereNotNull('ah.seans_sayisi')
                ->select('ah.id', 'ah.seans_sayisi')
                ->orderBy('a.tarih')->get();
            foreach ($rows as $r) {
                $kullanilan = \DB::table('adisyon_paket_seanslar')
                    ->where('adisyon_hizmet_id', $r->id)->count();
                if ($kullanilan < (int) $r->seans_sayisi) {
                    $adisyonHizmetId = $r->id;
                    break;
                }
            }
        }
        // Eslesen acik paket yoksa hicbir sey yapma (cunku bu seans bizim
        // sistemde takip edilebilecek bir adisyon_hizmet'e bagli degil)
        if (!$adisyonHizmetId) return false;

        $sonNo = (int) (\DB::table('adisyon_paket_seanslar')
            ->where('adisyon_hizmet_id', $adisyonHizmetId)
            ->max('seans_no') ?? 0);

        $aps = new AdisyonPaketSeanslar();
        $aps->adisyon_hizmet_id = $adisyonHizmetId;
        $aps->hizmet_id = $hizmetId;
        $aps->seans_no = $sonNo + 1;
        $aps->randevu_id = $randevuId;
        $aps->seans_tarih = $tarih;
        $aps->seans_saat = $saat;
        if ($personelId) $aps->personel_id = $personelId;
        if ($odaId) $aps->oda_id = $odaId;
        $aps->geldi = 1;
        $aps->save();
        return true;
    }

    /**
     * Drklinik paket hint formati: "(N x Hizmet)" veya "(Hizmet x N)".
     * Musteri detay -> Randevular tablosu: "(100 dakika solaryum x 12)"
     * Randevu listesi td[13]: "(12x100 dakika solaryum)"
     * Donus: ['hizmet_adi' => '...', 'seans' => N] veya null
     */
    private function parsePaketSeansHint($s)
    {
        if (!$s) return null;
        $s = trim($s);
        // (Nxhizmet) - rakam basta
        if (preg_match('~\((\d+)\s*x\s*([^)]+)\)~iu', $s, $m)) {
            return ['seans' => (int) $m[1], 'hizmet_adi' => trim($m[2])];
        }
        // (hizmet x N) - rakam sonda
        if (preg_match('~\(([^)]+?)\s*x\s*(\d+)\)~iu', $s, $m)) {
            return ['seans' => (int) $m[2], 'hizmet_adi' => trim($m[1])];
        }
        return null;
    }

    /**
     * Drklinik urun hint formati: "(N x Urun)" veya "(Urun x N)".
     */
    private function parseUrunHint($s)
    {
        if (!$s) return null;
        $s = trim($s);
        if (preg_match('~\((\d+)\s*x\s*([^)]+)\)~iu', $s, $m)) {
            return ['adet' => (int) $m[1], 'urun_adi' => trim($m[2])];
        }
        if (preg_match('~\(([^)]+?)\s*x\s*(\d+)\)~iu', $s, $m)) {
            return ['adet' => (int) $m[2], 'urun_adi' => trim($m[1])];
        }
        return null;
    }

    private function parseHizmetlerStr($str)
    {
        $out = [];
        // "Ad (N Seans = X TRY)" virgul -> array. Ad icinde virgul olabilir, paranteze gore parcala
        if (!$str) return $out;
        // Regex: ad bos olmayan + "(N Seans = X TRY)"
        if (preg_match_all('~([^,(]+?)\s*\((\d+)\s*Seans\s*=\s*([\d.,]+)\s*(?:TRY|TL|₺)?\)~iu', $str, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $ad = trim($row[1]);
                if ($ad === '' || $ad === ',') continue;
                $out[] = [
                    'ad' => $ad,
                    'seans' => (int) $row[2],
                    'tutar' => $this->paraParse($row[3]),
                ];
            }
        }
        // Eger regex 0 hizmet bulduysa duz string'i tek hizmet olarak kabul et
        if (empty($out) && trim($str)) {
            $out[] = ['ad' => trim($str), 'seans' => 1, 'tutar' => 0];
        }
        return $out;
    }

    private function paraParse($s)
    {
        if (preg_match('~([\d.]+),(\d{1,2})~', $s, $m)) {
            return (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
        }
        return (float) preg_replace('~[^0-9.]~', '', $s);
    }

    private $hizmetMapCache = null;
    private $urunNameSet = null;
    private $urunIdMap = null;

    /**
     * Bir hizmet adi, bu salonda Urunler tablosunda kayitli mi?
     * trKey-bazli karsilastirma; "(H)", "(U)" gibi suffix'leri normalize eder.
     */
    private function isUrunName($ad)
    {
        $this->buildUrunMaps();
        $key = $this->trKey($this->stripDrSuffix((string) $ad));
        return $key !== '' && isset($this->urunNameSet[$key]);
    }

    private function findUrunIdByName($ad)
    {
        $this->buildUrunMaps();
        $key = $this->trKey($this->stripDrSuffix((string) $ad));
        return $this->urunIdMap[$key] ?? null;
    }

    private function buildUrunMaps()
    {
        if ($this->urunNameSet !== null) return;
        $this->urunNameSet = [];
        $this->urunIdMap = [];
        $rows = \DB::table((new Urunler)->getTable())
            ->where('salon_id', $this->salonId)
            ->select('id', 'urun_adi')->get();
        foreach ($rows as $r) {
            $k = $this->trKey($this->stripDrSuffix($r->urun_adi));
            if ($k === '' || isset($this->urunNameSet[$k])) continue;
            $this->urunNameSet[$k] = true;
            $this->urunIdMap[$k] = $r->id;
        }
    }

    private function stripDrSuffix($s)
    {
        // Drklinik bazi adlarin sonuna "(H)" / "(U)" / "(P)" tag ekliyor
        return trim(preg_replace('~\s*\((?:H|U|P)\)\s*$~iu', '', (string) $s));
    }

    private function findSalonHizmetByName($ad)
    {
        $key = $this->trKey($ad);
        if ($this->hizmetMapCache === null) {
            $this->hizmetMapCache = [];
            $hizmetlerTable = (new Hizmetler)->getTable();
            $shTable = (new SalonHizmetler)->getTable();
            $rows = \DB::table($hizmetlerTable . ' as h')
                ->leftJoin($shTable . ' as sh', function ($j) {
                    $j->on('sh.hizmet_id', '=', 'h.id')->where('sh.salon_id', $this->salonId);
                })
                ->where(function ($q) {
                    $q->where('h.salon_id', $this->salonId)->orWhere('h.ozel_hizmet', true);
                })
                ->select('h.id', 'h.hizmet_adi', 'sh.sure_dk', 'sh.baslangic_fiyat')
                ->get();
            foreach ($rows as $r) {
                $k = $this->trKey($r->hizmet_adi);
                if ($k === '' || isset($this->hizmetMapCache[$k])) continue;
                $this->hizmetMapCache[$k] = [
                    'hizmet_id' => $r->id,
                    'sure_dk' => $r->sure_dk ?: 30,
                    'baslangic_fiyat' => $r->baslangic_fiyat ?: 0,
                ];
            }
        }
        return $this->hizmetMapCache[$key] ?? null;
    }

    /**
     * Eslesmeyen hizmet adi icin Hizmetler + SalonHizmetler kaydi olusturur (aktif=0).
     * Drklinik aktariminda kayitli hizmet bulunamayan satirlarda 0 TL adisyon
     * olusmasini engellemek icin pasif olarak hizmeti otomatik ekler.
     */
    private function ensureSalonHizmet($ad, $fiyat)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        // Urun ismi ise hizmet uretme; cagiran taraf null'a gore islem yapacak
        if ($this->isUrunName($ad)) {
            return null;
        }

        static $kategoriId = null;
        if ($kategoriId === null) {
            $kategoriId = $this->kategoriEkleVeyaGetir('Drklinik');
            $this->ensureKategoriRenk($kategoriId);
        }

        try {
            $h = new Hizmetler();
            $h->hizmet_adi = $ad;
            $h->hizmet_kategori_id = $kategoriId;
            $h->ozel_hizmet = true;
            if (Schema::hasColumn('hizmetler', 'salon_id')) $h->salon_id = $this->salonId;
            if (Schema::hasColumn('hizmetler', 'aktif'))    $h->aktif = 0;
            $h->save();

            $sh = new SalonHizmetler();
            $sh->salon_id = $this->salonId;
            $sh->hizmet_id = $h->id;
            $sh->hizmet_kategori_id = $kategoriId;
            $sh->aktif = 0;
            $sh->bolum = 2;
            $sh->sure_dk = 30;
            $sh->baslangic_fiyat = $fiyat;
            $sh->son_fiyat = $fiyat;
            $sh->save();

            $this->counts['hizmet']++;
            $entry = ['hizmet_id' => $h->id, 'sure_dk' => 30, 'baslangic_fiyat' => $fiyat];
            if (is_array($this->hizmetMapCache)) {
                $this->hizmetMapCache[$this->trKey($ad)] = $entry;
            }
            return $entry;
        } catch (\Throwable $e) {
            \Log::warning('[Drklinik] hizmet otomatik olusturulamadi', [
                'ad' => $ad, 'err' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Müşteri-bazlı satış + tahsilat + seans düşümü aktarımı.
     * Drklinik'in musteri.aspx?musid=XXX detay sayfasından tek seferde:
     *  - Tablo[1] Satışlar  -> Adisyon + AdisyonHizmetler + (paket varsa) AdisyonPaketSeanslar
     *  - Tablo[5] Tahsilatlar -> Tahsilat (adisyon match: tarih+tutar)
     *  - Tablo[3] Randevular "Seans Düşümü" sütunu -> AdisyonPaketSeanslar.randevu_id
     *
     * Tarih aralığındaki randevu listesinden tüm unique musid'leri toplar,
     * her musid için detay sayfasını işler.
     */
    /**
     * Saglama raporu: her musteri icin drklinik "Kalan Seanslar" vs bizim DB.
     * Cikti: /tmp/drk_seans_fark_<salon>.csv
     * Yazma yapmaz, sadece karsilastirir.
     */
    public function raporSeansFark($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2018-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2030-12-31');
        $this->log('Seans saglama: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end));

        // Musid topla
        $musidSet = [];
        $weekStart = $start; $iter = 0;
        while ($weekStart <= $end) {
            $weekEnd = min($end, strtotime('+6 days', $weekStart));
            $this->collectMusidsRange($weekStart, $weekEnd, $musidSet, 0);
            $iter++;
            if ($iter % 20 === 0) $this->log("  ..hafta {$iter} unique musid: " . count($musidSet));
            $weekStart = strtotime('+7 days', $weekStart);
        }
        $this->log('Toplam unique musid: ' . count($musidSet));

        $csvPath = '/tmp/drk_seans_fark_' . $this->salonId . '.csv';
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, ['musid', 'musteri', 'hizmet', 'drk_alinan', 'biz_alinan',
                      'drk_harcanan', 'biz_harcanan', 'drk_kalan', 'biz_kalan', 'durum']);

        $apsTable = (new AdisyonPaketSeanslar)->getTable();
        $okMusteri = 0; $farkMusteri = 0; $farkSatir = 0; $i = 0;
        foreach ($musidSet as $musid => $_) {
            $i++;
            $userId = $this->ensureUserByMusid((string) $musid);
            if (!$userId) continue;
            $h = $this->client->getHtml('/musteri.aspx?musid=' . $musid);
            if (strlen($h) < 5000) continue;
            $musteriAd = trim(trim($this->extractInputValue($h, 'TB_Ad')) . ' ' . trim($this->extractInputValue($h, 'TB_Soyad')));

            // Kalan Seanslar tablosunu bul + parse et (hizmet bazinda topla)
            $drk = []; // hizmetAd => [alinan, harcanan, kalan]
            preg_match_all('~<table[^>]*class="[^"]*table[^"]*"[^>]*>(.*?)</table>~is', $h, $tm);
            foreach ($tm[1] as $body) {
                preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th);
                $headers = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
                if (!in_array('Harcanan', $headers, true) ||
                    !(in_array('Satın Alınan', $headers, true) || in_array('Kalan', $headers, true))) continue;
                $idx = [];
                foreach ($headers as $k => $hd) $idx[$this->trKey($hd)] = $k;
                $iH = $idx['hizmet'] ?? 0;
                $iA = $idx[$this->trKey('Satın Alınan')] ?? 1;
                $iHar = $idx['harcanan'] ?? 3;
                preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $body, $rr);
                foreach ($rr[1] as $tr) {
                    if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
                    preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
                    if (empty($tds[1])) continue;
                    $c = [];
                    foreach ($tds[1] as $tdRaw) {
                        $c[] = trim(html_entity_decode(trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw))), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    }
                    $hAd = trim($c[$iH] ?? '');
                    if ($hAd === '') continue;
                    $al = (int) preg_replace('~\D~', '', $c[$iA] ?? '0');
                    $har = (int) preg_replace('~[^\d-]~', '', $c[$iHar] ?? '0');
                    if ($al <= 0) continue;
                    if (!isset($drk[$hAd])) $drk[$hAd] = ['alinan' => 0, 'harcanan' => 0];
                    $drk[$hAd]['alinan'] += $al;
                    $drk[$hAd]['harcanan'] += max(0, $har);
                }
            }
            if (empty($drk)) continue;

            $musteriFarkli = false;
            foreach ($drk as $hAd => $v) {
                $sh = $this->findSalonHizmetByName($hAd);
                $bizAlinan = 0; $bizHarcanan = 0;
                if ($sh) {
                    $ahRows = \DB::table('adisyon_hizmetler as ah')
                        ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
                        ->where('a.user_id', $userId)->where('a.salon_id', $this->salonId)
                        ->where('ah.hizmet_id', $sh['hizmet_id'])
                        ->whereNotNull('ah.seans_sayisi')
                        ->select('ah.id', 'ah.seans_sayisi')->get();
                    $bizAlinan = (int) $ahRows->sum('seans_sayisi');
                    if ($ahRows->count()) {
                        $bizHarcanan = (int) \DB::table($apsTable)
                            ->whereIn('adisyon_hizmet_id', $ahRows->pluck('id')->all())->count();
                    }
                }
                $drkKalan = $v['alinan'] - $v['harcanan'];
                $bizKalan = $bizAlinan - $bizHarcanan;
                $durum = ($v['alinan'] === $bizAlinan && $v['harcanan'] === $bizHarcanan) ? 'OK' : 'FARK';
                if ($durum === 'FARK') { $musteriFarkli = true; $farkSatir++; }
                fputcsv($fp, [$musid, $musteriAd, $hAd, $v['alinan'], $bizAlinan,
                              $v['harcanan'], $bizHarcanan, $drkKalan, $bizKalan, $durum]);
            }
            if ($musteriFarkli) $farkMusteri++; else $okMusteri++;
            if ($i % 100 === 0) $this->log("  ..{$i}/" . count($musidSet) . " ok_musteri={$okMusteri} farkli_musteri={$farkMusteri}");
        }
        fclose($fp);
        $this->log("Saglama bitti. OK musteri={$okMusteri}, farkli musteri={$farkMusteri}, fark satir={$farkSatir}");
        $this->log("CSV: {$csvPath}");
    }

    public function importSatisVeTahsilat($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2024-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2026-12-31');
        $this->log('Satis+Tahsilat: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end));

        // 1) Tarih aralığındaki tüm musid'leri topla
        $this->log('Musid toplama (randevu listesi haftalik tarama)...');
        $musidSet = [];
        $weekStart = $start; $iter = 0;
        while ($weekStart <= $end) {
            $weekEnd = min($end, strtotime('+6 days', $weekStart));
            $this->collectMusidsRange($weekStart, $weekEnd, $musidSet, 0);
            $iter++;
            if ($iter % 20 === 0) $this->log("  ..hafta {$iter} unique musid: " . count($musidSet));
            $weekStart = strtotime('+7 days', $weekStart);
        }
        $this->log('Toplam unique musid: ' . count($musidSet));

        // 2) Her musid için detay sayfasını işle
        $i = 0;
        foreach ($musidSet as $musid => $_) {
            $userId = $this->ensureUserByMusid((string) $musid);
            if (!$userId) { continue; }
            $this->importMusteriDetay((string) $musid, $userId);
            $i++;
            if ($i % 50 === 0) {
                $sd = $this->counts['seans_dusumu'] ?? 0;
                $rl = $this->counts['tahsilat_relink'] ?? 0;
                $tp = $this->counts['tahsilat_propagate'] ?? 0;
                $this->log("  ..musteri {$i}/" . count($musidSet) . " satis={$this->counts['satis']} tahsilat={$this->counts['tahsilat']} relink={$rl} prop={$tp} seans={$sd}");
            }
            usleep(300000);
        }
        $sd = $this->counts['seans_dusumu'] ?? 0;
        $rl = $this->counts['tahsilat_relink'] ?? 0;
        $tp = $this->counts['tahsilat_propagate'] ?? 0;
        $this->log("Aktarim tamam: satis={$this->counts['satis']}, tahsilat={$this->counts['tahsilat']}, tahsilat_relink={$rl}, tahsilat_propagate={$tp}, seans_dusumu={$sd}");
    }

    private function collectMusidsRange($startTs, $endTs, &$musidSet, $depth)
    {
        $h = $this->client->postBack('/gunlukrandevulistesi.aspx', 'BTN_Ara', '', [
            'TB_Tarih1' => date('d.m.Y', $startTs),
            'TB_Tarih2' => date('d.m.Y', $endTs),
        ]);
        usleep(250000);
        if ($h === null) return;

        preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
        $maxTr = 0;
        foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>~i', $t, $rm) && count($rm[0]) > $maxTr) $maxTr = count($rm[0]);
        if ($maxTr >= 50 && ($endTs - $startTs) >= 86400 && $depth < 6) {
            $mid = $startTs + intval(($endTs - $startTs) / 2);
            $this->collectMusidsRange($startTs, $mid, $musidSet, $depth + 1);
            $this->collectMusidsRange($mid + 86400, $endTs, $musidSet, $depth + 1);
            return;
        }
        if (preg_match_all('~href="musteri\.aspx\?musid=(\d+)~', $h, $m)) {
            foreach ($m[1] as $id) $musidSet[$id] = true;
        }
    }

    /**
     * Bir müşterinin musteri.aspx?musid=X detay sayfasından
     * satış, tahsilat ve seans düşümü tablolarını işler.
     */
    public function importMusteriDetay($musid, $userId)
    {
        $h = $this->client->getHtml('/musteri.aspx?musid=' . $musid);
        if (strlen($h) < 5000) return;

        preg_match_all('~<table[^>]*class="[^"]*table[^"]*"[^>]*>(.*?)</table>~is', $h, $tm);
        $kalanSeansTablo = null; $kalanSeansHeaders = null;
        foreach ($tm[1] as $body) {
            preg_match_all('~<th[^>]*>(.*?)</th>~is', $body, $th);
            $headers = array_map(function ($t) { return trim(html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8')); }, $th[1]);
            if (in_array('Satış No', $headers, true) && in_array('Hizmetler', $headers, true)) {
                $this->processMusteriSatislar($body, $userId, $musid);
            } elseif (in_array('Ödeme Şekli', $headers, true) && in_array('Banka Hesabı', $headers, true)) {
                $this->processMusteriTahsilatlar($body, $userId);
            } elseif (in_array('Seans Düşümü', $headers, true)) {
                // Musteri.aspx Randevular tablosu: hem Randevular+RandevuHizmetler
                // olusturur hem Seans Dusumu sutunundan APS tuketimi yapar.
                $this->processMusteriRandevular($body, $userId, $headers);
            } elseif (in_array('Harcanan', $headers, true) &&
                      (in_array('Satın Alınan', $headers, true) || in_array('Kalan', $headers, true))) {
                // Drklinik "Kalan Seanslar" tablosu - OTORITER seans kaynagi.
                // Diger tablolar islendikten SONRA reconcile etsin diye sakla.
                $kalanSeansTablo = $body; $kalanSeansHeaders = $headers;
            }
        }
        // Kalan Seanslar en son: satis + seans dusumu islendikten sonra
        // tuketilen seans sayisini drklinik'in kesin degerine esitler.
        if ($kalanSeansTablo !== null) {
            $this->processKalanSeanslar($kalanSeansTablo, $userId, $kalanSeansHeaders);
        }
    }

    private function processMusteriSatislar($tbody, $userId, $musid)
    {
        $defaultPers = $this->defaultPersonelId();
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $tbody, $rows);
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            // Beklenen sira (button hucreler atlandi): SatisNo, Tarih, Paket, Hizmetler, Aciklama, Tutar, Odenen, Kalan, SatisiYapan
            $cells = array_values(array_filter($cells, function ($c, $k) {
                return true; // sirayi koruyalim - filter etmiyoruz, butonlar zaten '' yazildi
            }, ARRAY_FILTER_USE_BOTH));
            // Bos basta varsa atla
            $i = 0; while ($i < count($cells) && $cells[$i] === '') $i++;
            $data = array_values(array_slice($cells, $i));
            if (count($data) < 6) continue;

            $satisNo = $data[0];
            $tarih   = $this->tarihNormalize($data[1] ?? '');
            $paketAd = $data[2] ?? '';
            $hizmetlerStr = $data[3] ?? '';
            $aciklama = $data[4] ?? '';
            $tutar    = $this->paraParse($data[5] ?? '0');
            if (!$satisNo || !$tarih) continue;

            $idMarker = "drklinik:{$satisNo}";
            // Adisyon dedup
            static $notKolonu = null;
            if ($notKolonu === null) {
                foreach (['adisyon_notu','aciklama','genel_aciklama','notlar','not','dosya_no','referans'] as $col) {
                    if (\Schema::hasColumn('adisyonlar', $col)) { $notKolonu = $col; break; }
                }
                if (!$notKolonu) $notKolonu = false;
            }
            $ad = null;
            if ($notKolonu) {
                $ad = Adisyonlar::where('salon_id', $this->salonId)
                    ->where($notKolonu, 'LIKE', '%' . $idMarker . '%')->first();
            }
            if (!$ad) {
                $ad = new Adisyonlar();
                $ad->user_id = $userId;
                $ad->salon_id = $this->salonId;
                $ad->tarih = $tarih;
                $ad->olusturan_id = $defaultPers;
                if ($notKolonu) $ad->{$notKolonu} = trim($aciklama . ' [' . $idMarker . ']');
                if (\Schema::hasColumn((new Adisyonlar)->getTable(), 'toplam_tutar')) {
                    $ad->toplam_tutar = $tutar;
                }
                $ad->save();
            } elseif (\Schema::hasColumn((new Adisyonlar)->getTable(), 'toplam_tutar') && (float) ($ad->toplam_tutar ?? 0) <= 0) {
                // Onceki import'ta toplam_tutar set edilmemisse simdi yaz
                $ad->toplam_tutar = $tutar;
                $ad->save();
            }
            $this->counts['satis']++;

            // Hizmetler "Ad (N Seans = X TRY)" parse
            $hizmetler = $this->parseHizmetlerStr($hizmetlerStr);
            foreach ($hizmetler as $hv) {
                $seansSayisi = max(1, (int) $hv['seans']);
                $birimFiyat = $hv['tutar'] / $seansSayisi;
                // Once Urunler tablosunda eslesen var mi diye bak
                $urunId = $this->findUrunIdByName($hv['ad']);
                if ($urunId) {
                    $existAu = AdisyonUrunler::where('adisyon_id', $ad->id)
                        ->where('urun_id', $urunId)->first();
                    if ($existAu) continue;
                    $au = new AdisyonUrunler();
                    $au->adisyon_id = $ad->id;
                    $au->urun_id = $urunId;
                    $au->adet = $seansSayisi;
                    $au->fiyat = $hv['tutar'] ?: 0;
                    $au->save();
                    continue;
                }
                $sh = $this->findSalonHizmetByName($hv['ad']);
                if (!$sh) {
                    $sh = $this->ensureSalonHizmet($hv['ad'], $birimFiyat);
                    if (!$sh) continue;
                }
                $existAh = AdisyonHizmetler::where('adisyon_id', $ad->id)
                    ->where('hizmet_id', $sh['hizmet_id'])->first();
                if ($existAh) {
                    // Eski import'larda seans_sayisi bos olabilir; doldur
                    if (\Schema::hasColumn((new AdisyonHizmetler)->getTable(), 'seans_sayisi')
                        && empty($existAh->seans_sayisi)) {
                        $existAh->seans_sayisi = $seansSayisi;
                        $existAh->save();
                    }
                    continue;
                }
                $ah = new AdisyonHizmetler();
                $ah->adisyon_id = $ad->id;
                $ah->hizmet_id = $sh['hizmet_id'];
                $ah->personel_id = $defaultPers;
                $ah->geldi = 0;
                $ah->islem_tarihi = $tarih;
                $ah->islem_saati = '00:00:00';
                $ah->sure = $sh['sure_dk'] ?: 30;
                $ah->fiyat = $hv['tutar'];
                if (\Schema::hasColumn((new AdisyonHizmetler)->getTable(), 'seans_sayisi')) {
                    $ah->seans_sayisi = $seansSayisi;
                }
                $ah->save();
                // AdisyonPaketSeanslar pre-create EDILMEZ. Sadece seans tuketilince
                // (processMusteriRandevuSeansDusumu) tek tek olusturulur.
            }
        }
    }

    private function processMusteriTahsilatlar($tbody, $userId)
    {
        $defaultPers = $this->defaultPersonelId();
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $tbody, $rows);

        // Per-customer ordinal counter: ayni (tarih, tutar, yontem) icin drklinik
        // birden fazla tahsilat gosterirse, dedup ikinci/ucuncu kaydi atlamasin.
        // claimed[sig] = bu loop'ta su ana kadar eslestirdigimiz mevcut DB sayisi
        $claimed = [];

        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            $i = 0; while ($i < count($cells) && $cells[$i] === '') $i++;
            $data = array_values(array_slice($cells, $i));
            // Beklenen: Tarih, Aciklama, OdemeSekli, Kasa, Tutar, Banka, Taksit
            if (count($data) < 5) continue;
            $tarih = $this->tarihNormalize($data[0] ?? '');
            $odemeSekli = $data[2] ?? '';
            $tutar = $this->paraParse($data[4] ?? '0');
            if (!$tarih || $tutar <= 0) continue;
            $odemeYontemi = $this->odemeYontemiMap($odemeSekli);

            $adisyonId = $this->findMatchingAdisyonId($userId, $tarih, $tutar);

            // Sig + ordinal dedup: ayni signature'dan drklinik N tane gosteriyorsa
            // bizde de N tane olmali. claimed[sig] su anki match offset.
            $sig = $tarih . '|' . $tutar . '|' . $odemeYontemi;
            $offset = $claimed[$sig] ?? 0;

            $matches = Tahsilatlar::where('user_id', $userId)
                ->where('salon_id', $this->salonId)
                ->where('odeme_tarihi', $tarih)
                ->where('tutar', $tutar)
                ->where('odeme_yontemi_id', $odemeYontemi)
                ->orderBy('id')->get();

            if ($matches->count() > $offset) {
                // Bu drklinik satirina karsi gelen DB kaydi var
                $exists = $matches[$offset];
                $claimed[$sig] = $offset + 1;
                if (!$exists->adisyon_id && $adisyonId) {
                    $exists->adisyon_id = $adisyonId;
                    $exists->save();
                    $this->counts['tahsilat_relink'] = ($this->counts['tahsilat_relink'] ?? 0) + 1;
                }
                if ($exists->adisyon_id) {
                    $this->propagateAdisyonToTahsilat($exists);
                }
                continue;
            }
            // DB'de bu signature icin yeterli kayit yok -> yeni ekle (claimed sayar)
            $claimed[$sig] = $offset + 1;

            $t = new Tahsilatlar();
            $t->user_id = $userId;
            $t->salon_id = $this->salonId;
            $t->tutar = $tutar;
            $t->yapilan_odeme = $tutar;
            $t->odeme_yontemi_id = $odemeYontemi;
            $t->odeme_tarihi = $tarih;
            $t->olusturan_id = $defaultPers;
            if ($adisyonId) $t->adisyon_id = $adisyonId;
            $t->save();
            $this->counts['tahsilat']++;
            if ($t->adisyon_id) {
                $this->propagateAdisyonToTahsilat($t);
            }
        }
    }

    /**
     * Tahsilat tutarini, baglandigi adisyonun hizmet+urun kalemlerine
     * fiyat-orantili olarak dagitir; tahsilat_hizmetler ve tahsilat_urunler
     * kayitlarini uretir. Idempotent: zaten kayit varsa eklemez.
     *
     * Tam odeme   -> her kaleme kendi fiyati yazilir
     * Kismi odeme -> oransal pay (yuvarlama hatasi son kaleme yazilir)
     */
    private function propagateAdisyonToTahsilat($tahsilat)
    {
        if (!$tahsilat || !$tahsilat->adisyon_id) return;
        if (TahsilatHizmetler::where('tahsilat_id', $tahsilat->id)->exists()) return;
        if (TahsilatUrunler::where('tahsilat_id', $tahsilat->id)->exists()) return;

        $hizmetler = AdisyonHizmetler::where('adisyon_id', $tahsilat->adisyon_id)->get();
        $urunler   = AdisyonUrunler::where('adisyon_id', $tahsilat->adisyon_id)->get();
        if ($hizmetler->isEmpty() && $urunler->isEmpty()) return;

        if ($this->dagitVeYaz($tahsilat, $hizmetler, $urunler)) {
            $this->counts['tahsilat_propagate'] = ($this->counts['tahsilat_propagate'] ?? 0) + 1;
        }
    }

    /**
     * Oransal dagitim + son kaleme yuvarlama farki. Tahsilat tutari =
     * sum(tahsilat_hizmetler.tutar) + sum(tahsilat_urunler.tutar).
     */
    private function dagitVeYaz($tahsilat, $hizmetler, $urunler)
    {
        $items = []; // [type, model, base_fiyat]
        foreach ($hizmetler as $h) {
            $f = (float) ($h->fiyat ?? 0);
            if ($f > 0) $items[] = ['hizmet', $h, $f];
        }
        foreach ($urunler as $u) {
            $f = (float) ($u->fiyat ?? 0) * max(1, (int) ($u->adet ?? 1));
            if ($f > 0) $items[] = ['urun', $u, $f];
        }
        if (empty($items)) return false;

        $toplamFiyat = array_sum(array_column($items, 2));
        if ($toplamFiyat <= 0) return false;
        $tutar = (float) $tahsilat->tutar;
        if ($tutar <= 0) return false;

        $oran = $tutar / $toplamFiyat;
        $payToplam = 0.0;
        $paylar = [];
        foreach ($items as $i => $it) {
            $pay = round($it[2] * $oran, 2);
            $paylar[$i] = $pay;
            $payToplam += $pay;
        }
        // Yuvarlama farki son kaleme
        $fark = round($tutar - $payToplam, 2);
        if (abs($fark) > 0.001 && !empty($paylar)) {
            $sonIdx = array_key_last($paylar);
            $paylar[$sonIdx] = round($paylar[$sonIdx] + $fark, 2);
        }

        foreach ($items as $i => $it) {
            $pay = $paylar[$i];
            if ($pay <= 0) continue;
            if ($it[0] === 'hizmet') {
                $th = new TahsilatHizmetler();
                $th->tahsilat_id = $tahsilat->id;
                $th->adisyon_hizmet_id = $it[1]->id;
                $th->tutar = $pay;
                $th->save();
            } else {
                $tu = new TahsilatUrunler();
                $tu->tahsilat_id = $tahsilat->id;
                $tu->adisyon_urun_id = $it[1]->id;
                $tu->tutar = $pay;
                $tu->save();
            }
        }
        return true;
    }

    /**
     * Tahsilat icin en uygun adisyonu bul.
     * Oncelik: ayni tarih + ayni tutar > ayni tarih + ilk > yakin tarih (30 gun) + tutar.
     * Tutar adisyonun kalemleri (adisyon_hizmetler + adisyon_urunler) toplamiyla
     * karsilastirilir; Adisyonlar.toplam_tutar kolonu varsa o da kabul edilir.
     */
    private function findMatchingAdisyonId($userId, $tarih, $tutar)
    {
        // SIKI eslesme: ya kesin tutar match'i ya NULL. Alakasiz adisyona
        // baglamaktansa tahsilat adisyon_id=NULL kalsin (sonra repair edilebilir).
        $tol = 0.01;
        // 1) Ayni tarih + ayni tutar
        $sameDate = Adisyonlar::where('user_id', $userId)
            ->where('salon_id', $this->salonId)
            ->where('tarih', $tarih)->orderBy('id')->get();
        foreach ($sameDate as $ad) {
            if (abs($this->adisyonTutar($ad) - $tutar) < $tol) return $ad->id;
        }
        // 2) Tahsilat tarihi onceki 30 gun icindeki bir adisyona ait olabilir
        //    (taksit/sonradan odeme) -- SADECE tutar tam eslesirse
        $oncesi = Adisyonlar::where('user_id', $userId)
            ->where('salon_id', $this->salonId)
            ->whereDate('tarih', '<=', $tarih)
            ->whereDate('tarih', '>=', date('Y-m-d', strtotime($tarih . ' -30 days')))
            ->orderBy('tarih', 'desc')->get();
        foreach ($oncesi as $ad) {
            if (abs($this->adisyonTutar($ad) - $tutar) < $tol) return $ad->id;
        }
        // Hicbir kesin eslesme yok -> NULL. Tahsilat adisyona baglanmaz,
        // alakasiz bir adisyona yanlislikla yapismaz.
        return null;
    }

    /**
     * Adisyonun toplam tutari. Tablo'da toplam_tutar kolonu varsa onu,
     * yoksa adisyon_hizmetler+adisyon_urunler toplamini doner.
     */
    private function adisyonTutar($ad)
    {
        static $hasCol = null;
        if ($hasCol === null) $hasCol = \Schema::hasColumn((new Adisyonlar)->getTable(), 'toplam_tutar');
        if ($hasCol && (float) ($ad->toplam_tutar ?? 0) > 0) return (float) $ad->toplam_tutar;
        $sumH = (float) AdisyonHizmetler::where('adisyon_id', $ad->id)->sum('fiyat');
        $sumU = (float) AdisyonUrunler::where('adisyon_id', $ad->id)
            ->selectRaw('COALESCE(SUM(fiyat * GREATEST(adet,1)), 0) as t')->value('t');
        return $sumH + $sumU;
    }

    /**
     * Drklinik "Kalan Seanslar" tablosu — her hizmet icin Satin Alinan / Harcanan / Kalan.
     * Bu OTORITER kaynaktir. Bizdeki paket adisyon_hizmetler icin:
     *  - seans_sayisi toplami != Satin Alinan ise (tek paket varsa) duzeltilir
     *  - tuketilen AdisyonPaketSeanslar sayisi Harcanan'a esitlenir (eksikse eklenir,
     *    fazlaysa once randevusuz olanlardan silinir)
     */
    private function processKalanSeanslar($tbody, $userId, $headers)
    {
        $idx = [];
        foreach ($headers as $k => $hd) $idx[$this->trKey($hd)] = $k;
        $iHizmet   = $idx['hizmet'] ?? 0;
        $iAlinan   = $idx[$this->trKey('Satın Alınan')] ?? 1;
        $iHarcanan = $idx['harcanan'] ?? 3;

        $apsTable = (new AdisyonPaketSeanslar)->getTable();
        $hasRandevuId = \Schema::hasColumn($apsTable, 'randevu_id');

        // 1) Tum satirlari hizmet bazinda TOPLA (ayni hizmet birden fazla satirda olabilir)
        $agg = []; // hizmetId => ['alinan'=>, 'harcanan'=>]
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $tbody, $rows);
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            $hizmetAd = trim($cells[$iHizmet] ?? '');
            if ($hizmetAd === '') continue;
            $satinAlinan = (int) preg_replace('~\D~', '', $cells[$iAlinan] ?? '0');
            // Harcanan negatif olabilir (drklinik 'Suresi Biten' -2 gibi) - isaret korunsun
            $harcananRaw = trim($cells[$iHarcanan] ?? '0');
            $harcanan = (int) preg_replace('~[^\d-]~', '', $harcananRaw);
            if ($satinAlinan <= 0) continue;

            $sh = $this->findSalonHizmetByName($hizmetAd);
            if (!$sh) continue;
            $hid = $sh['hizmet_id'];
            if (!isset($agg[$hid])) $agg[$hid] = ['alinan' => 0, 'harcanan' => 0];
            $agg[$hid]['alinan']   += $satinAlinan;
            $agg[$hid]['harcanan'] += max(0, $harcanan);
        }

        // 2) Hizmet bazinda reconcile
        foreach ($agg as $hizmetId => $sum) {
            $harcanan = (int) $sum['harcanan'];

            $ahRows = \DB::table('adisyon_hizmetler as ah')
                ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
                ->where('a.user_id', $userId)->where('a.salon_id', $this->salonId)
                ->where('ah.hizmet_id', $hizmetId)
                ->whereNotNull('ah.seans_sayisi')
                ->select('ah.id', 'ah.seans_sayisi')->orderBy('a.tarih')->get();
            if ($ahRows->isEmpty()) continue;
            $ahIds = $ahRows->pluck('id')->all();

            // tek paket ise seans_sayisi'ni Satin Alinan'a esitle
            if ($ahRows->count() === 1 && (int) $ahRows[0]->seans_sayisi !== (int) $sum['alinan'] && $sum['alinan'] > 0) {
                \DB::table('adisyon_hizmetler')->where('id', $ahRows[0]->id)
                    ->update(['seans_sayisi' => (int) $sum['alinan']]);
            }

            // tuketilen APS sayisini Harcanan'a esitle
            $mevcut = (int) \DB::table($apsTable)->whereIn('adisyon_hizmet_id', $ahIds)->count();
            if ($mevcut < $harcanan) {
                $eklenecek = $harcanan - $mevcut;
                $hedefAh = $ahRows[0]->id;
                $sonNo = (int) (\DB::table($apsTable)->where('adisyon_hizmet_id', $hedefAh)->max('seans_no') ?? 0);
                for ($k = 0; $k < $eklenecek; $k++) {
                    $sonNo++;
                    \DB::table($apsTable)->insert([
                        'adisyon_hizmet_id' => $hedefAh,
                        'hizmet_id' => $hizmetId,
                        'seans_no'  => $sonNo,
                        'geldi'     => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->counts['seans_dusumu'] = ($this->counts['seans_dusumu'] ?? 0) + $eklenecek;
            } elseif ($mevcut > $harcanan) {
                $fazla = $mevcut - $harcanan;
                $q = \DB::table($apsTable)->whereIn('adisyon_hizmet_id', $ahIds);
                if ($hasRandevuId) $q->orderByRaw('randevu_id IS NULL DESC'); // once randevusuzlar
                $silId = $q->orderByDesc('id')->limit($fazla)->pluck('id')->all();
                if ($silId) {
                    \DB::table($apsTable)->whereIn('id', $silId)->delete();
                    $this->counts['seans_silinen'] = ($this->counts['seans_silinen'] ?? 0) + count($silId);
                }
            }
        }
    }

    /**
     * Musteri.aspx "Randevular" tablosu (Seans Dusumu sutunu iceren).
     * Header-indexed parsing: kolon yerleri degisirse calismaya devam eder.
     * Yaptiklari:
     *   1) Randevular (tarih+saat+user+salon ile dedup, update)
     *   2) RandevuHizmetler (randevu_id+hizmet_id ile dedup, update)
     *   3) Personel ve oda lookup/create
     *   4) durum: iptal->2, diger->1
     *   5) randevuya_geldi: geldi/gelmedi/iptal'den
     *   6) Seans Dusumu varsa AdisyonPaketSeanslar tuketim
     */
    private function processMusteriRandevular($tbody, $userId, $headers)
    {
        // Header'lardan kolon indeksi cikar (sira degisikligine dayanikli)
        $idx = [];
        foreach ($headers as $k => $hd) $idx[$this->trKey($hd)] = $k;
        $iTarih      = $idx['tarih'] ?? 0;
        $iSaat       = $idx['saat'] ?? 1;
        $iBitis      = $idx['bitis'] ?? $idx[$this->trKey('Bitiş')] ?? null;
        $iHizmet     = $idx['hizmet'] ?? $idx['hizmetler'] ?? null;
        $iPersonel   = $idx['personel'] ?? $idx['calisan'] ?? $idx[$this->trKey('Çalışan')] ?? null;
        $iOda        = $idx['oda'] ?? null;
        $iDurum      = $idx['durum'] ?? null;
        $iAciklama   = $idx['aciklama'] ?? $idx[$this->trKey('Açıklama')] ?? null;
        $iSeansDus   = $idx[$this->trKey('Seans Düşümü')] ?? $idx[$this->trKey('Seans Dusumu')] ?? null;

        // Lazy maps (her musteri icin tekrar kurmuyoruz — sadece bir kere)
        static $persMap = null; static $odaMap = null;
        if ($persMap === null) $persMap = $this->buildPersonelMapByName();
        if ($odaMap === null)  $odaMap  = $this->buildOdaMapByName();

        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $tbody, $rows);
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (empty($cells)) continue;

            $tarihStr = preg_replace('~\s*\([^)]+\)~u', '', $cells[$iTarih] ?? '');
            $tarih = $this->tarihNormalize($tarihStr);
            $saat  = $cells[$iSaat] ?? '';
            if (!$tarih || !$saat) continue;
            if (strlen($saat) === 5) $saat .= ':00';

            $bitis = '';
            if ($iBitis !== null && !empty($cells[$iBitis]) && preg_match('~^\d{1,2}:\d{2}~', $cells[$iBitis])) {
                $bitis = $cells[$iBitis];
                if (strlen($bitis) === 5) $bitis .= ':00';
            }

            $hizmetStr = $iHizmet !== null ? trim($cells[$iHizmet] ?? '') : '';
            $personel  = $iPersonel !== null ? trim($cells[$iPersonel] ?? '') : '';
            $oda       = $iOda !== null ? trim($cells[$iOda] ?? '') : '';
            $durum     = $iDurum !== null ? trim($cells[$iDurum] ?? '') : '';
            $aciklama  = $iAciklama !== null ? trim($cells[$iAciklama] ?? '') : '';
            $seansDus  = $iSeansDus !== null ? trim($cells[$iSeansDus] ?? '') : '';

            // 1) Randevular - dedup + update
            $r = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$r) $r = new Randevular();
            $r->tarih = $tarih;
            $r->saat = $saat;
            $r->user_id = $userId;
            $r->salon_id = $this->salonId;
            $r->salon = 0;
            $r->olusturan_personel_id = null;
            $du = mb_strtolower($durum, 'UTF-8');
            if (strpos($du, 'iptal') !== false) $r->durum = 2;
            else $r->durum = 1;
            if (strpos($du, 'geldi') !== false && strpos($du, 'gelmedi') === false) $r->randevuya_geldi = 1;
            elseif (strpos($du, 'gelmedi') !== false || strpos($du, 'iptal') !== false) $r->randevuya_geldi = 0;
            if ($aciklama) $r->personel_notu = $aciklama;
            $r->save();
            $this->counts['randevu'] = ($this->counts['randevu'] ?? 0) + 1;

            // 2) Personel + oda lookup
            $personelId = null;
            if ($personel) {
                $personelId = $persMap[$this->trKey($personel)] ?? null;
                if (!$personelId) { $personelId = $this->ensurePersonelId($personel, $persMap); }
            }
            $odaId = null;
            if ($oda) {
                $odaId = $odaMap[$this->trKey($oda)] ?? null;
                if (!$odaId) { $odaId = $this->ensureOdaId($oda, $odaMap); }
            }

            // 3) Hizmet (paket "(NxAd)" formati da olabilir)
            $hizmetId = null; $sureDk = 30;
            $hizmetAdiHint = $hizmetStr;
            if ($hizmetAdiHint && preg_match('~\((\d+)x([^)]+)\)~iu', $hizmetAdiHint, $m)) {
                $hizmetAdiHint = trim($m[2]);
            }
            if ($hizmetAdiHint !== '') {
                $sh = $this->findSalonHizmetByName($hizmetAdiHint);
                if (!$sh) $sh = $this->ensureSalonHizmet($hizmetAdiHint, 0);
                if ($sh) { $hizmetId = $sh['hizmet_id']; $sureDk = $sh['sure_dk'] ?: 30; }
            }
            if ($bitis) {
                $diff = (int) round((strtotime($bitis) - strtotime($saat)) / 60);
                if ($diff > 0) $sureDk = $diff;
            }

            // 4) RandevuHizmetler - dedup + update
            $rhQuery = RandevuHizmetler::where('randevu_id', $r->id);
            if ($hizmetId) $rhQuery->where('hizmet_id', $hizmetId);
            else $rhQuery->whereNull('hizmet_id');
            $rh = $rhQuery->first();
            if (!$rh) $rh = new RandevuHizmetler();
            $rh->randevu_id = $r->id;
            $rh->hizmet_id = $hizmetId;
            $rh->saat = $saat;
            $rh->saat_bitis = $bitis ?: date('H:i:s', strtotime('+' . $sureDk . ' minutes', strtotime($saat)));
            $rh->sure_dk = $sureDk;
            if ($personelId) $rh->personel_id = $personelId;
            if ($odaId) $rh->oda_id = $odaId;
            try { $rh->save(); } catch (\Throwable $e) { Log::warning('drk musteri rh: ' . $e->getMessage()); }

            // 5) Seans Dusumu (varsa) -> AdisyonPaketSeanslar tuketim
            if ($seansDus !== '' && mb_stripos($seansDus, 'Düş', 0, 'UTF-8') !== false) {
                $seansSayisi = 1;
                $paketHint = $this->parsePaketSeansHint($hizmetStr);
                if ($paketHint) $seansSayisi = max(1, (int) $paketHint['seans']);
                $yazilan = $this->seanslariTuket($userId, $hizmetId, $tarih, $saat, $seansSayisi);
                if ($yazilan > 0) {
                    $this->counts['seans_dusumu'] = ($this->counts['seans_dusumu'] ?? 0) + $yazilan;
                }
            }
        }
    }

    private function processMusteriRandevuSeansDusumu($tbody, $userId)
    {
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $tbody, $rows);
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            $i = 0; while ($i < count($cells) && $cells[$i] === '') $i++;
            $data = array_values(array_slice($cells, $i));
            if (count($data) < 9) continue;
            $tarihStr = preg_replace('~\s*\([^)]+\)~u', '', $data[0]);
            $tarih = $this->tarihNormalize($tarihStr);
            $saat = $data[1] ?? '';
            if (strlen($saat) === 5) $saat .= ':00';
            $hizmetlerStr = $data[3] ?? '';
            $seansDusumu = $data[8] ?? '';
            if (!$tarih) continue;
            if (mb_stripos($seansDusumu, 'Düş', 0, 'UTF-8') === false) continue;

            // Hizmet adi + seans sayisi cikar
            $hizmetId = null;
            $seansSayisi = 1;
            $paketHint = $this->parsePaketSeansHint($hizmetlerStr);
            if ($paketHint) {
                $seansSayisi = max(1, (int) $paketHint['seans']);
                $sh = $this->findSalonHizmetByName($paketHint['hizmet_adi']);
                if ($sh) $hizmetId = $sh['hizmet_id'];
            }
            if (!$hizmetId && $hizmetlerStr) {
                $clean = $paketHint['hizmet_adi'] ?? '';
                if ($clean === '') {
                    $clean = trim(preg_replace('~\s*\([^)]+\)\s*$~u', '', $hizmetlerStr));
                }
                if ($clean !== '') {
                    $sh = $this->findSalonHizmetByName($clean);
                    if ($sh) $hizmetId = $sh['hizmet_id'];
                }
            }

            // Bir randevuda N seans dusulebiliyor (drklinik formati: "(hizmet x N)")
            $yazilan = $this->seanslariTuket($userId, $hizmetId, $tarih, $saat, $seansSayisi);
            if ($yazilan > 0) {
                $this->counts['seans_dusumu'] = ($this->counts['seans_dusumu'] ?? 0) + $yazilan;
            }
        }
    }

    /**
     * Bir randevuda N seans tuket. Drklinik formati "(hizmet x N)" -> N seans.
     * Acik paket(ler)e dagitilir, gerektiginde birden fazla pakete tasinir.
     *
     * Idempotent: (ah_id, tarih, saat) icin mevcut kayit sayisi >= talep edilen
     * ise hicbir sey eklemez. Az ise farki tamamlar.
     *
     * Donus: yazilan APS sayisi.
     */
    private function seanslariTuket($userId, $hizmetId, $tarih, $saat, $kac)
    {
        $kac = max(1, (int) $kac);
        $saat = $saat ?: '00:00:00';

        // Acik (kullanilan < seans_sayisi) AdisyonHizmetler'i sirayla bul
        $rows = \DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
            ->where('a.user_id', $userId)
            ->where('a.salon_id', $this->salonId)
            ->whereNotNull('ah.seans_sayisi')
            ->select('ah.id', 'ah.hizmet_id', 'ah.seans_sayisi')
            ->orderBy('a.tarih')->get();

        // Once hizmet_id eslesenleri sirala
        $sira = [];
        if ($hizmetId) {
            foreach ($rows as $r) if ((int) $r->hizmet_id === (int) $hizmetId) $sira[] = $r;
            foreach ($rows as $r) if ((int) $r->hizmet_id !== (int) $hizmetId) $sira[] = $r;
        } else {
            $sira = $rows->all();
        }

        $yazilan = 0;
        foreach ($sira as $r) {
            if ($kac <= 0) break;
            $toplam = (int) $r->seans_sayisi;
            $kullanilan = (int) \DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->count();
            $bosKalan = $toplam - $kullanilan;
            if ($bosKalan <= 0) continue;

            // Idempotent: (ah_id, tarih, saat) uclusu icin mevcut sayi
            $mevcutBuSatir = (int) \DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)
                ->where('seans_tarih', $tarih)
                ->where('seans_saat', $saat)
                ->count();
            $hedef = min($kac, $bosKalan);
            $eksik = max(0, $hedef - $mevcutBuSatir);
            if ($eksik === 0) {
                $kac -= $hedef; // bu satir icin gereken zaten yazilmis say
                continue;
            }

            $sonNo = (int) (\DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->max('seans_no') ?? 0);

            for ($i = 0; $i < $eksik; $i++) {
                $sonNo++;
                $aps = new AdisyonPaketSeanslar();
                $aps->adisyon_hizmet_id = $r->id;
                $aps->hizmet_id = $r->hizmet_id;
                $aps->seans_no = $sonNo;
                $aps->seans_tarih = $tarih;
                $aps->seans_saat = $saat;
                $aps->geldi = 1;
                $aps->save();
                $yazilan++;
            }
            $kac -= $eksik;
        }
        return $yazilan;
    }

    /**
     * Hizmet ismi eslesmediginde fallback: kullanicinin herhangi bir
     * acik (seans_sayisi > kullanilan) AdisyonHizmetler kaydina yeni
     * AdisyonPaketSeanslar (geldi=1) ekle.
     */
    private function paketSeansiTuketHerhangiHizmet($userId, $randevuId, $tarih, $saat)
    {
        if (!$randevuId) return false;
        $already = AdisyonPaketSeanslar::where('randevu_id', $randevuId)->first();
        if ($already) return false;

        $rows = \DB::table('adisyon_hizmetler as ah')
            ->join('adisyonlar as a', 'ah.adisyon_id', '=', 'a.id')
            ->where('a.user_id', $userId)
            ->where('a.salon_id', $this->salonId)
            ->whereNotNull('ah.seans_sayisi')
            ->select('ah.id', 'ah.hizmet_id', 'ah.seans_sayisi')
            ->orderBy('a.tarih')->get();
        foreach ($rows as $r) {
            $kullanilan = \DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->count();
            if ($kullanilan >= (int) $r->seans_sayisi) continue;

            $sonNo = (int) (\DB::table('adisyon_paket_seanslar')
                ->where('adisyon_hizmet_id', $r->id)->max('seans_no') ?? 0);
            $aps = new AdisyonPaketSeanslar();
            $aps->adisyon_hizmet_id = $r->id;
            $aps->hizmet_id = $r->hizmet_id;
            $aps->seans_no = $sonNo + 1;
            $aps->randevu_id = $randevuId;
            $aps->seans_tarih = $tarih;
            $aps->seans_saat = $saat;
            $aps->geldi = 1;
            $aps->save();
            return true;
        }
        return false;
    }

    /**
     * Tahsilatlar: kasa_islemleri.aspx + BTN_Ara, hafta-hafta tarama (server cap'i muhtemel 50).
     * Sutunlar (11 td): Tarih | Aciklama | Odeme Sekli | Tutar | Musteri | Banka | Taksit | Kasa | Dosya(buton) | Saat | GenelTip
     *
     * Drklinik'te tahsilat dogrudan musteriye bagli (randevu/adisyon iliskisi liste'de yok),
     * Tahsilatlar.adisyon_id NULL olarak yazilir.
     */
    /**
     * Giderler: kasa_islemleri.aspx + BTN_GiderHepsi (TB_GiderTarihBas/Bit).
     * Sutunlar (10 td): Tarih | Aciklama | Odeme Sekli | Tutar | Genel Tip |
     *                   Gider Tipi | Kasa | Odenen | Saat | (button)
     * Masraflar tablosuna yazilir. Idempotent: salon_id+tarih+tutar+saat+aciklama
     * hash'i ile dedup.
     */
    public function importGiderler($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? $baslangic : '2018-01-01';
        $end   = $bitis ? $bitis : date('Y-m-d');
        $this->log("Giderler cekiliyor: {$start} - {$end} ...");

        $h = $this->client->postBack('/kasa_islemleri.aspx', 'BTN_GiderHepsi', '', [
            'TB_GiderTarihBas' => date('d.m.Y', strtotime($start)),
            'TB_GiderTarihBit' => date('d.m.Y', strtotime($end)),
            'DDL_GiderTipi'    => '0',
            'DDL_KasaGider'    => '0',
            'DDL_Giderler'     => 'Ödeme Şekli',
            'DDL_GenelTip'     => '',
        ]);
        if (!$h) { $this->log('Sayfa cekilemedi.'); return; }
        usleep(300000);

        $tableBodies = $this->extractAllTables($h);
        if (empty($tableBodies)) { $this->log('Tablo bulunamadi.'); return; }

        // En cok satira sahip tabloyu sec (gider tablosu)
        $best = ''; $bestTrs = 0;
        foreach ($tableBodies as $body) {
            $trc = preg_match_all('~<tr[^>]*>~i', $body, $r) ? count($r[0]) : 0;
            if ($trc > $bestTrs) { $bestTrs = $trc; $best = $body; }
        }

        if (!isset($this->counts['gider'])) $this->counts['gider'] = 0;
        if (!isset($this->counts['gider_skip'])) $this->counts['gider_skip'] = 0;

        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $best, $rows);
        $defaultPers = $this->defaultPersonelId();
        $seenHashes = []; // ayni hash 2.kez gelirse marker'a suffix ekle
        foreach ($rows[1] as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; }
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 9) { $this->log("[skip-cells<9] " . implode(' | ', $cells)); continue; }

            $tarih      = $this->tarihNormalize($cells[0] ?? '');
            $aciklama   = $cells[1] ?? '';
            $odemeSekli = $cells[2] ?? '';
            $tutarStr   = $cells[3] ?? '';
            $genelTip   = $cells[4] ?? '';
            $giderTipi  = $cells[5] ?? '';
            $odenen     = $cells[7] ?? '';
            $saat       = $cells[8] ?? '';

            if (!$tarih) { $this->log("[skip-tarih] '{$cells[0]}' | tutar='{$tutarStr}' aciklama='{$aciklama}'"); $this->counts['gider_skip']++; continue; }
            // "32.000,00 TRY" parse
            $tutar = 0.0;
            if (preg_match('~([\d.]+),(\d{1,2})~', $tutarStr, $m)) {
                $tutar = (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
            } else {
                $tutar = (float) preg_replace('~[^0-9.]~', '', $tutarStr);
            }
            if ($tutar <= 0) { $this->log("[skip-tutar=0] tarih='{$tarih}' tutarStr='{$tutarStr}' aciklama='{$aciklama}'"); $this->counts['gider_skip']++; continue; }
            if (strlen($saat) === 5) $saat .= ':00';

            $kategoriAdi = trim($giderTipi) ?: (trim($genelTip) ?: 'Diğer');
            $kategoriId  = $this->ensureMasrafKategori($kategoriAdi);
            $odemeYontemi = $this->odemeYontemiMap($odemeSekli);

            // Harcayan: Maaş satirinda "Odenen" personel adi olabilir, ya da aciklama
            $harcayanAd = '';
            if (mb_stripos($kategoriAdi, 'Maaş', 0, 'UTF-8') !== false) {
                $harcayanAd = trim($odenen) ?: trim($aciklama);
            }
            $harcayanId = null;
            if ($harcayanAd) {
                $harcayanId = Personeller::where('salon_id', $this->salonId)
                    ->where('personel_adi', 'LIKE', '%' . $harcayanAd . '%')
                    ->value('id');
            }
            if (!$harcayanId) $harcayanId = $defaultPers;

            // Idempotent dedup - notlar kolonuna drklinik hash'i yazip ona gore eslestir
            $masTable = (new Masraflar)->getTable();
            $hasNotlar = \Schema::hasColumn($masTable, 'notlar');
            $baseHash = md5($tarih . '|' . $tutar . '|' . $saat . '|' . $aciklama . '|' . $kategoriAdi . '|' . $odemeSekli);
            $occ = ($seenHashes[$baseHash] ?? 0) + 1;
            $seenHashes[$baseHash] = $occ;
            $hashKey = $occ > 1 ? $baseHash . ':' . $occ : $baseHash;
            $marker = 'drk:' . $hashKey;

            if ($hasNotlar) {
                $exists = Masraflar::where('salon_id', $this->salonId)
                    ->where('notlar', 'LIKE', '%' . $marker . '%')->exists();
            } else {
                // notlar yoksa eski (zayif) dedup
                $existsQ = Masraflar::where('salon_id', $this->salonId)
                    ->where('tarih', $tarih)
                    ->where('tutar', $tutar);
                if (\Schema::hasColumn($masTable, 'aciklama')) $existsQ->where('aciklama', $aciklama);
                $exists = $existsQ->exists();
            }
            if ($exists) { $this->counts['gider_skip']++; continue; }

            $m = new Masraflar();
            $m->salon_id = $this->salonId;
            $m->tarih = $tarih;
            $m->tutar = $tutar;
            if (\Schema::hasColumn($masTable, 'saat')) $m->saat = $saat;
            if (\Schema::hasColumn($masTable, 'aciklama')) $m->aciklama = $aciklama;
            if (\Schema::hasColumn($masTable, 'masraf_kategori_id')) $m->masraf_kategori_id = $kategoriId;
            if (\Schema::hasColumn($masTable, 'odeme_yontemi_id')) $m->odeme_yontemi_id = $odemeYontemi;
            if (\Schema::hasColumn($masTable, 'harcayan_id')) $m->harcayan_id = $harcayanId;
            if ($hasNotlar) $m->notlar = $marker;
            $m->save();
            $this->counts['gider']++;
        }
        $this->log("Gider import bitti: eklenen={$this->counts['gider']}, atlanan={$this->counts['gider_skip']}");
    }

    /**
     * Masraf kategorisi getir/olustur. trKey bazli match.
     */
    private function ensureMasrafKategori($ad)
    {
        $ad = trim((string) $ad);
        if ($ad === '') $ad = 'Diğer';
        $table = (new MasrafKategorisi)->getTable();
        // Olası kolon adlari (kullanici DB'sinde 'kategoriler' kullanılıyor)
        $nameCol = null;
        foreach (['kategori', 'kategoriler', 'kategori_adi', 'kategori_ad', 'ad', 'name', 'adi'] as $c) {
            if (\Schema::hasColumn($table, $c)) { $nameCol = $c; break; }
        }

        static $cache = null;
        if ($cache === null) {
            $cache = [];
            if ($nameCol) {
                foreach (\DB::table($table)->select('id', $nameCol)->get() as $k) {
                    $key = $this->trKey($k->{$nameCol} ?? '');
                    if ($key !== '') $cache[$key] = $k->id;
                }
            }
        }
        $needle = $this->trKey($ad);
        if (isset($cache[$needle])) return $cache[$needle];
        try {
            $k = new MasrafKategorisi();
            if ($nameCol) $k->{$nameCol} = $ad;
            if (\Schema::hasColumn($table, 'salon_id')) $k->salon_id = $this->salonId;
            $k->save();
            $cache[$needle] = $k->id;
            return $k->id;
        } catch (\Throwable $e) {
            \Log::warning('[Drklinik] masraf_kategorisi olusturulamadi', ['ad' => $ad, 'err' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Nested table'lar icin saglam parser.
     */
    private function extractAllTables($html)
    {
        $bodies = [];
        $offset = 0;
        $len = strlen($html);
        while ($offset < $len) {
            $start = stripos($html, '<table', $offset);
            if ($start === false) break;
            $tagEnd = strpos($html, '>', $start);
            if ($tagEnd === false) break;
            $bodyStart = $tagEnd + 1;
            $depth = 1;
            $pos = $bodyStart;
            while ($depth > 0 && $pos < $len) {
                $nextOpen = stripos($html, '<table', $pos);
                $nextClose = stripos($html, '</table>', $pos);
                if ($nextClose === false) break;
                if ($nextOpen !== false && $nextOpen < $nextClose) {
                    $depth++;
                    $pos = $nextOpen + 6;
                } else {
                    $depth--;
                    if ($depth === 0) {
                        $bodies[] = substr($html, $bodyStart, $nextClose - $bodyStart);
                        $offset = $nextClose + 8;
                        break;
                    }
                    $pos = $nextClose + 8;
                }
            }
            if ($depth > 0) break;
        }
        return $bodies;
    }

    public function importTahsilatlar($baslangic = null, $bitis = null)
    {
        $start = $baslangic ? strtotime($baslangic) : strtotime('2018-01-01');
        $end   = $bitis ? strtotime($bitis) : strtotime('2030-12-31');
        $this->log('Tahsilatlar cekiliyor: ' . date('Y-m-d', $start) . ' - ' . date('Y-m-d', $end) . ' (haftalik)...');
        $defaultPers = $this->defaultPersonelId();

        $weekStart = $start; $iter = 0;
        while ($weekStart <= $end) {
            $weekEnd = min($end, strtotime('+6 days', $weekStart));
            $this->scrapeTahsilatRange($weekStart, $weekEnd, $defaultPers, 0);
            $iter++;
            if ($iter % 10 === 0) $this->log("  ..hafta {$iter} (" . date('Y-m-d', $weekStart) . "..) toplam_tahsilat={$this->counts['tahsilat']}");
            $weekStart = strtotime('+7 days', $weekStart);
        }
        $this->log("Tahsilat aktarim toplam: {$this->counts['tahsilat']} (skipped: {$this->counts['skipped']})");
    }

    private function defaultPersonelId()
    {
        $id = Personeller::where('salon_id', $this->salonId)->where('aktif', 1)->orderBy('id')->value('id');
        if (!$id) $id = Personeller::where('salon_id', $this->salonId)->orderBy('id')->value('id');
        return $id;
    }

    private function scrapeTahsilatRange($startTs, $endTs, $defaultPers, $depth)
    {
        $h = $this->client->postBack('/kasa_islemleri.aspx', 'BTN_Ara', '', [
            'TB_TarihSec1' => date('d.m.Y', $startTs),
            'TB_TarihSec2' => date('d.m.Y', $endTs),
        ]);
        usleep(300000);
        if ($h === null) return;

        preg_match_all('~<table[^>]*>(.*?)</table>~is', $h, $tm);
        $maxTr = 0;
        foreach ($tm[1] as $t) if (preg_match_all('~<tr[^>]*>~i', $t, $rm) && count($rm[0]) > $maxTr) $maxTr = count($rm[0]);
        if ($maxTr >= 50 && ($endTs - $startTs) >= 86400 && $depth < 6) {
            $mid = $startTs + intval(($endTs - $startTs) / 2);
            $this->scrapeTahsilatRange($startTs, $mid, $defaultPers, $depth + 1);
            $this->scrapeTahsilatRange($mid + 86400, $endTs, $defaultPers, $depth + 1);
            return;
        }

        $rows = $this->parseTableRowsRaw($h);
        foreach ($rows as $rawRow) {
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) { $cells[] = ''; continue; } // sira korumak icin bos ekle
                $clean = trim(preg_replace('~\s+~', ' ', strip_tags($tdRaw)));
                $cells[] = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
            if (count($cells) < 10) continue;

            $tarih = $this->tarihNormalize($cells[0] ?? '');
            $aciklama = $cells[1] ?? '';
            $odemeSekli = $cells[2] ?? '';
            $tutarStr = $cells[3] ?? '';
            $musteri = $cells[4] ?? '';
            $saat = $cells[9] ?? '';

            if (!$tarih) { $this->counts['skipped']++; continue; }
            $tutar = (float) preg_replace('~[^0-9,\.]~', '', str_replace(',', '.', $tutarStr));
            // Tek ondalık nokta - virgül ondalık olabilir
            $tutar = (float) preg_replace('~,~', '', $tutarStr);
            // En sondaki virgüllü ondalık formatı: 199,00
            if (preg_match('~([\d.]+),(\d{1,2})\s*(?:TRY|TL)?~', $tutarStr, $m)) {
                $tutar = (float) (str_replace('.', '', $m[1]) . '.' . $m[2]);
            }
            if ($tutar <= 0) { $this->counts['skipped']++; continue; }

            // Musteri adi ile salonun User'larini ara
            $musteri = trim($musteri);
            if (!$musteri) { $this->counts['skipped']++; continue; }
            $userId = $this->findUserByNameInSalon($musteri);
            if (!$userId) { $this->counts['skipped']++; continue; }

            $odemeYontemi = $this->odemeYontemiMap($odemeSekli);

            // Idempotent: ayni tarih+saat+user+tutar+yontem varsa atla
            $exists = Tahsilatlar::where('user_id', $userId)
                ->where('salon_id', $this->salonId)
                ->where('odeme_tarihi', $tarih)
                ->where('tutar', $tutar)
                ->where('odeme_yontemi_id', $odemeYontemi)
                ->first();
            if ($exists) continue;

            $t = new Tahsilatlar();
            $t->user_id = $userId;
            $t->salon_id = $this->salonId;
            $t->tutar = $tutar;
            $t->yapilan_odeme = $tutar;
            $t->odeme_yontemi_id = $odemeYontemi;
            $t->odeme_tarihi = $tarih;
            $t->olusturan_id = $defaultPers;
            if ($aciklama) $t->notlar = $aciklama;
            $t->save();
            $this->counts['tahsilat']++;
        }
    }

    private function odemeYontemiMap($metin)
    {
        $m = mb_strtolower(trim((string) $metin), 'UTF-8');
        if (strpos($m, 'nakit') !== false) return 1;
        if (strpos($m, 'kart') !== false || strpos($m, 'pos') !== false) return 2;
        if (strpos($m, 'havale') !== false || strpos($m, 'eft') !== false || strpos($m, 'transfer') !== false) return 3;
        return 4;
    }

    /**
     * Drklinik musteri ID (musid) -> bizim user_id eslemesi.
     * Detay sayfasi GET (musteri.aspx?musid=XXX), TB_CepTel + TB_Ad + TB_Soyad parse,
     * telefon ile DB'de User bul/yarat. Cache'lenir, ayni musid icin tek GET.
     */
    private function ensureUserByMusid($musid)
    {
        $musid = trim((string) $musid);
        if (!$musid || $musid === '0') return null;
        if (isset($this->drklinikUserCache[$musid])) return $this->drklinikUserCache[$musid];

        $h = $this->client->getHtml('/musteri.aspx?musid=' . $musid);
        if (strlen($h) < 5000) {
            $this->drklinikUserCache[$musid] = null;
            return null;
        }

        $ad    = $this->extractInputValue($h, 'TB_Ad');
        $soyad = $this->extractInputValue($h, 'TB_Soyad');
        $tel   = $this->telefonNormalize($this->extractInputValue($h, 'TB_CepTel'));
        $tamAd = trim(trim($ad) . ' ' . trim($soyad));
        if (!$tamAd) $tamAd = 'Drklinik ' . $musid;

        $userId = null;
        if ($tel) {
            // Telefon ile lookup (sistem genelinde, cross-salon paylasimi kabul)
            $u = User::where('cep_telefon', $tel)->first();
            if ($u) $userId = $u->id;
        }
        if (!$userId) {
            // Telefon yoksa bu salon'da ayni isim arama
            $u = User::where('name', $tamAd)
                ->whereHas('salonlar', function ($q) { $q->where('salon_id', $this->salonId); })
                ->first();
            if ($u) $userId = $u->id;
        }
        if (!$userId) {
            // Yeni user
            try {
                $u = new User();
                $u->name = $tamAd;
                $u->cep_telefon = $tel ?: null;
                $u->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $u->save();
                $userId = $u->id;
            } catch (\Exception $e) {
                $u = new User();
                $u->name = $tamAd;
                $u->cep_telefon = 'drklinik_' . $musid;
                $u->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $u->save();
                $userId = $u->id;
            }
            $this->counts['musteri']++;
        }

        // Bu salonun portfoyune ekle (yoksa)
        $portfoy = MusteriPortfoy::where('user_id', $userId)
            ->where('salon_id', $this->salonId)->first();
        if (!$portfoy) {
            $p = new MusteriPortfoy();
            $p->user_id = $userId;
            $p->salon_id = $this->salonId;
            $p->aktif = 1;
            $p->save();
        }

        $this->drklinikUserCache[$musid] = $userId;
        return $userId;
    }

    /**
     * Drklinik liste'den gelen musteri adina gore User bul; bulunamazsa
     * sisteme telefonsuz yeni User + portfoy ekleyip donulur.
     */
    private function findUserByNameInSalon($ad)
    {
        $ad = trim($ad);
        if (!$ad) return null;

        // 1) Bu salonun portfoyunde ayni isim
        $u = User::where('name', $ad)
            ->whereHas('salonlar', function ($q) { $q->where('salon_id', $this->salonId); })
            ->orderBy('id')->first();
        if ($u) return $u->id;

        // 2) Case-insensitive deneme
        $u = User::whereRaw('LOWER(name) = ?', [mb_strtolower($ad, 'UTF-8')])
            ->whereHas('salonlar', function ($q) { $q->where('salon_id', $this->salonId); })
            ->orderBy('id')->first();
        if ($u) return $u->id;

        // 3) trKey ile (Turkce normalize)
        $needle = $this->trKey($ad);
        $candidates = User::whereHas('salonlar', function ($q) {
            $q->where('salon_id', $this->salonId);
        })->where(function ($q) use ($ad) {
            // Onceki SQL filtre: ad'in ilk kelimesini whereLike ile dar tut
            $first = explode(' ', $ad)[0];
            if ($first) $q->where('name', 'LIKE', $first . '%');
        })->limit(50)->get();
        foreach ($candidates as $cu) {
            if ($this->trKey($cu->name) === $needle) return $cu->id;
        }

        // 4) Bulunamadi - telefonsuz yeni user olustur
        try {
            $new = new User();
            $new->name = $ad;
            $new->cep_telefon = null;
            $new->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $new->save();
        } catch (\Exception $e) {
            // NOT NULL ise placeholder
            $new = new User();
            $new->name = $ad;
            $new->cep_telefon = 'drklinik_' . substr(md5($ad), 0, 10);
            $new->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
            $new->save();
        }
        $portfoy = new MusteriPortfoy();
        $portfoy->user_id = $new->id;
        $portfoy->salon_id = $this->salonId;
        $portfoy->aktif = 1;
        $portfoy->save();
        $this->counts['musteri']++;
        return $new->id;
    }

    /**
     * Eslesmeyen personel adi icin pasif personel kaydi olusturur, map'e ekler.
     */
    private function ensurePersonelId($ad, &$map)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        $key = $this->trKey($ad);
        if (isset($map[$key])) return $map[$key];

        $yetkili = new IsletmeYetkilileri();
        $yetkili->name = $ad;
        $yetkili->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
        $yetkili->password = Hash::make(Str::random(10));
        $yetkili->aktif = 0;
        $yetkili->save();

        $sonSira = Personeller::where('salon_id', $this->salonId)->max('takvim_sirasi');
        $sira = ($sonSira ? $sonSira : 0) + 1;
        $sonRenk = Personeller::where('salon_id', $this->salonId)->orderBy('id', 'desc')->value('renk');
        $renk = (!$sonRenk || $sonRenk >= 10) ? 1 : $sonRenk + 1;

        $p = new Personeller();
        $p->personel_adi = $ad;
        $p->salon_id = $this->salonId;
        $p->yetkili_id = $yetkili->id;
        $p->role_id = 5;
        $p->aktif = 0;
        $p->takvimde_gorunsun = 0;
        $p->takvim_sirasi = $sira;
        $p->renk = $renk;
        $p->save();

        DB::insert(
            'INSERT INTO model_has_roles (role_id, model_type, model_id, salon_id) VALUES (?, ?, ?, ?)',
            [5, 'App\\IsletmeYetkilileri', $yetkili->id, $this->salonId]
        );

        $map[$key] = $p->id;
        return $p->id;
    }

    /**
     * Eslesmeyen oda adi icin pasif oda kaydi olusturur, map'e ekler.
     */
    private function ensureOdaId($ad, &$map)
    {
        $ad = trim((string) $ad);
        if ($ad === '') return null;
        $key = $this->trKey($ad);
        if (isset($map[$key])) return $map[$key];

        $oda = new Odalar();
        $oda->oda_adi = $ad;
        $oda->salon_id = $this->salonId;
        $oda->durum = 0;
        if (Schema::hasColumn('odalar', 'aktifmi')) $oda->aktifmi = 0;
        $oda->save();

        $this->ensureOdaRenk($oda->id);
        $map[$key] = $oda->id;
        return $oda->id;
    }

    /**
     * Lookup key: Turkce karakterleri ASCII'e cevir + lowercase + bosluk sadelestir.
     * Hem map hem aranacak isim ayni transformdan gecince eslesme dogru calisir.
     */
    private function trKey($s)
    {
        $s = trim((string) $s);
        $tr = ['İ'=>'i','I'=>'i','ı'=>'i','Ç'=>'c','ç'=>'c','Ğ'=>'g','ğ'=>'g','Ö'=>'o','ö'=>'o','Ş'=>'s','ş'=>'s','Ü'=>'u','ü'=>'u'];
        $s = strtr($s, $tr);
        $s = mb_strtolower($s, 'UTF-8');
        // Unicode combining marks (combining dot above U+0307 vb.) kaldir
        $s = preg_replace('/\p{M}+/u', '', $s);
        return preg_replace('/\s+/', ' ', $s);
    }

    /**
     * Birim adiyla salon hizmeti eslemesi - drklinik birim'i bizdeki Hizmet_Kategorisi adi.
     * O kategoride bir SalonHizmetler dondurur (default ilki).
     */
    private function buildHizmetMapByBirim()
    {
        $map = [];
        $kategoriler = Hizmet_Kategorisi::all();
        foreach ($kategoriler as $k) {
            $sh = SalonHizmetler::where('salon_id', $this->salonId)
                ->where('hizmet_kategori_id', $k->id)
                ->where('aktif', 1)
                ->orderBy('id')->first();
            if ($sh) {
                $map[$this->trKey($k->hizmet_kategorisi_adi)] = [
                    'hizmet_id'    => $sh->hizmet_id,
                    'sure_dk'      => (int) $sh->sure_dk ?: 30,
                    'fiyat'        => (float) $sh->baslangic_fiyat,
                    'kategori_id'  => $k->id,
                ];
            }
        }
        return $map;
    }

    private function processRandevuPage($html, &$personelMap, $hizmetMap, &$odaMapByName)
    {
        $eklenen = 0;
        // Raw tr ile dolas - musid linkini de yakala
        preg_match_all('~<tr[^>]*>(.*?)</tr>~is', $html, $trMatches);
        foreach ($trMatches[1] as $tr) {
            // Musid linki bu satirda
            $musid = null;
            if (preg_match('~href="musteri\.aspx\?musid=(\d+)~', $tr, $m)) $musid = $m[1];

            preg_match_all('~<td[^>]*>(.*?)</td>~is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cells = [];
            foreach ($tds[1] as $tdRaw) {
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cells[] = $clean;
            }
            if (count($cells) < 16) continue;
            $tarih = $this->tarihNormalize($cells[2] ?? '');
            $saat  = $cells[3] ?? '';
            $bitis = $cells[4] ?? '';
            if (!$tarih || !$saat) { $this->counts['skipped']++; continue; }

            $adSoyad = $cells[5] ?? '';
            $tel     = $this->telefonNormalize($cells[8] ?? '');
            $birim   = $cells[10] ?? '';
            $calisan = $cells[11] ?? '';
            $oda     = $cells[12] ?? '';
            $hizmetlerStr = $cells[13] ?? ''; // "(NxHizmet)" paket seansi
            $urunlerStr   = $cells[14] ?? ''; // "(NxUrun)" urun satisi
            $durum   = $cells[15] ?? '';
            $aciklama = $cells[16] ?? ''; // personel_notu

            if (strlen($saat) === 5) $saat .= ':00';
            if (strlen($bitis) === 5) $bitis .= ':00';

            // Musteri eslesme: oncelikli musid (kesin), yedek telefon, sonra ad
            $userId = null;
            if ($musid) $userId = $this->ensureUserByMusid($musid);
            if (!$userId) $userId = $this->upsertMusteri($adSoyad, $tel);
            if (!$userId) { $this->counts['skipped']++; continue; }

            // Personel + oda lookup (Turkce-normalize key ile, eslesmiyorsa pasif olusur)
            $personelId = $personelMap[$this->trKey($calisan)] ?? null;
            if (!$personelId && $calisan) $personelId = $this->ensurePersonelId($calisan, $personelMap);
            $odaId = $odaMapByName[$this->trKey($oda)] ?? null;
            if (!$odaId && $oda) $odaId = $this->ensureOdaId($oda, $odaMapByName);

            // Sure_dk = bitis - baslangic
            if (strlen($bitis) === 5) $bitis .= ':00';
            $sureDk = ($saat && $bitis) ? (int) round((strtotime($bitis) - strtotime($saat)) / 60) : 30;
            if ($sureDk <= 0) $sureDk = 30;

            // SADECE RANDEVU - hicbir adisyon/paket/urun mantigi yok.
            // Randevular kaydi: saat, bitis, musteri, personel, oda, durum, not.
            $r = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$r) $r = new Randevular();
            $r->tarih = $tarih;
            $r->saat = $saat;
            $r->user_id = $userId;
            $r->salon_id = $this->salonId;
            $r->salon = 0;
            $r->olusturan_personel_id = null;
            $du = mb_strtolower($durum, 'UTF-8');
            // durum: iptal -> 2, diger -> 1. Re-import'ta iptal olan randevu guncellensin.
            if (strpos($du, 'iptal') !== false) $r->durum = 2;
            else $r->durum = 1;
            if (strpos($du, 'geldi') !== false && strpos($du, 'gelmedi') === false) $r->randevuya_geldi = 1;
            elseif (strpos($du, 'gelmedi') !== false || strpos($du, 'iptal') !== false) $r->randevuya_geldi = 0;
            if ($aciklama) $r->personel_notu = $aciklama;
            $r->save();

            // RandevuHizmetler her zaman eklenir (saat, bitis, personel, oda bilgisi icin).
            // hizmet_id sadece td[13] 'Hizmetler' sutunu doluysa atanir.
            // td[13] iki format:
            //   "(NxHizmet adi)" -> paket randevusu
            //   "Hizmet adi"     -> tek seans (parantezsiz)
            // td[13] BOS ise hizmet_id NULL kalir (drklinik'te de hizmet yok, sadece slot).
            $hizmetIdAtanacak = null;
            $hizmetAdiHint = null;
            $hizmetlerStrTrim = trim($hizmetlerStr);
            if ($hizmetlerStrTrim !== '') {
                if (preg_match('~\((\d+)x([^)]+)\)~iu', $hizmetlerStrTrim, $m)) {
                    $hizmetAdiHint = trim($m[2]);
                } else {
                    $hizmetAdiHint = $hizmetlerStrTrim;
                }
            }
            if ($hizmetAdiHint) {
                $sh2 = $this->findSalonHizmetByName($hizmetAdiHint);
                if (!$sh2) $sh2 = $this->ensureSalonHizmet($hizmetAdiHint, 0);
                if ($sh2) $hizmetIdAtanacak = $sh2['hizmet_id'];
            }

            // Idempotent: ayni randevuda ayni hizmet_id (NULL dahil) varsa update
            $rhQuery = RandevuHizmetler::where('randevu_id', $r->id);
            if ($hizmetIdAtanacak) $rhQuery->where('hizmet_id', $hizmetIdAtanacak);
            else $rhQuery->whereNull('hizmet_id');
            $rh = $rhQuery->first();
            if (!$rh) $rh = new RandevuHizmetler();
            $rh->randevu_id = $r->id;
            $rh->hizmet_id = $hizmetIdAtanacak;
            $rh->saat = $saat;
            $rh->saat_bitis = $bitis ?: date('H:i:s', strtotime('+' . $sureDk . ' minutes', strtotime($saat)));
            $rh->sure_dk = $sureDk;
            if ($personelId) $rh->personel_id = $personelId;
            if ($odaId) $rh->oda_id = $odaId;
            try {
                $rh->save();
            } catch (\Exception $e) {
                Log::warning('Drklinik randevu_hizmetler save fail: ' . $e->getMessage());
            }

            $this->counts['randevu']++;
            $eklenen++;
        }
        return $eklenen;
    }

    private function upsertMusteri($adSoyad, $tel)
    {
        $adSoyad = trim($adSoyad);
        if (!$adSoyad && !$tel) return null;
        if (!$adSoyad) $adSoyad = $tel;

        if ($tel) {
            $u = User::where('cep_telefon', $tel)->first();
            if (!$u) {
                $u = new User();
                $u->name = $adSoyad;
                $u->cep_telefon = $tel;
                $u->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                $u->save();
            }
        } else {
            // Telefonsuz: ad+salon kombinasyonu ile ara
            $u = User::where('name', $adSoyad)
                ->whereNull('cep_telefon')
                ->whereHas('salonlar', function ($q) {
                    $q->where('salon_id', $this->salonId);
                })->first();
            if (!$u) {
                try {
                    $u = new User();
                    $u->name = $adSoyad;
                    $u->cep_telefon = null;
                    $u->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                    $u->save();
                } catch (\Exception $e) {
                    // NULL kabul edilmedi -> placeholder
                    $u = new User();
                    $u->name = $adSoyad;
                    $u->cep_telefon = 'drklinik_' . md5($adSoyad);
                    $u->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                    $u->save();
                }
            }
        }

        // Portfoy ekle
        $portfoy = MusteriPortfoy::where('user_id', $u->id)->where('salon_id', $this->salonId)->first();
        if (!$portfoy) {
            $portfoy = new MusteriPortfoy();
            $portfoy->user_id = $u->id;
            $portfoy->salon_id = $this->salonId;
            $portfoy->aktif = 1;
            $portfoy->save();
            $this->counts['musteri']++;
        }
        return $u->id;
    }

    /**
     * Musteriler: musterilistesi.aspx (DataGridView, sayfa basina 6 kayit, postback pagination).
     * Sutunlar: Sec(buton) | ID | Ad | Soyad | Cep | D.Tarihi
     * Pagination __doPostBack('DGRV_MusteriListesi', 'Page$N')
     */
    public function importMusteriler()
    {
        $this->log('Musteriler cekiliyor (musterilistesi.aspx, pagination)...');
        $page = 1;
        $totalRows = 0;
        $sayfaUyari = 0;

        $h = $this->client->getHtml('/musterilistesi.aspx');
        if ($h === '') { $this->log('Sayfa cekilemedi.'); return; }

        while (true) {
            $rowsAdded = $this->importMusteriPage($h);
            $totalRows += $rowsAdded;
            if ($page % 10 === 0) $this->log("  ..sayfa {$page}, toplam islenen: {$totalRows}");

            // Bir sonraki sayfa Page${page+1} var mi?
            $next = $page + 1;
            $hasNext = preg_match('~__doPostBack\([\'"&#39;]+DGRV_MusteriListesi[\'"&#39;]+,\s*[\'"&#39;]+Page\$' . $next . '[\'"&#39;]+~i', $h);
            if (!$hasNext) {
                // Numeric pager goremiyor olabiliriz, "..." ile devam ediyor olabilir
                // Page$X (X > current) genel pattern'i ara
                if (preg_match_all('~Page\$(\d+)~', $h, $m2)) {
                    $maxSeen = max(array_map('intval', $m2[1]));
                    if ($maxSeen >= $next) $hasNext = true;
                }
            }
            if (!$hasNext) {
                $sayfaUyari++;
                if ($sayfaUyari > 1) break; // Iki kez ust uste sayfa yoksa dur
            } else { $sayfaUyari = 0; }

            // Sonraki sayfa postback
            $h = $this->client->postBack('/musterilistesi.aspx', 'DGRV_MusteriListesi', 'Page$' . $next);
            if ($h === null) { $this->log('Postback null, durduruldu.'); break; }

            // Donen HTML hala 1. sayfa gibi ise (page change calismadi) dur
            if (!preg_match('~Page\$\d+~', $h)) break;
            $page = $next;
            usleep(200000); // 0.2sn
        }
        $this->log("Musteri aktarim toplam: {$this->counts['musteri']} (skipped: {$this->counts['skipped']})");
    }

    private function importMusteriPage($html)
    {
        $rows = $this->parseTableRowsRaw($html);
        $count = 0;
        foreach ($rows as $rawRow) {
            // Sec butonunu atla, kalanlar: ID, Ad, Soyad, Cep, D.Tarihi
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) continue;
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cells[] = $clean;
            }
            if (count($cells) < 4) { $this->counts['skipped']++; continue; }
            $drklinikId = $cells[0];
            $ad         = $cells[1] ?: '';
            $soyad      = $cells[2] ?: '';
            $tel        = $this->telefonNormalize($cells[3]);
            $dogum      = isset($cells[4]) ? $this->tarihNormalize($cells[4]) : null;

            $tamAd = trim($ad . ' ' . $soyad);
            if (!$tamAd) $tamAd = 'Drklinik ' . $drklinikId;

            $user = null;
            if ($tel) {
                // Gercek telefon varsa onunla lookup/create
                $user = User::where('cep_telefon', $tel)->first();
                if (!$user) {
                    $user = new User();
                    $user->name = $tamAd;
                    $user->cep_telefon = $tel;
                    $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                    if ($dogum) $user->dogum_tarihi = $dogum;
                    $user->save();
                }
            } else {
                // Telefonsuz: bu salonun portfoyunde ayni isimde telefonsuz user var mi?
                $existing = User::where('name', $tamAd)
                    ->whereNull('cep_telefon')
                    ->whereHas('salonlar', function ($q) {
                        $q->where('salon_id', $this->salonId);
                    })->first();
                if (!$existing) {
                    // Yeni user, cep_telefon=NULL dene
                    try {
                        $user = new User();
                        $user->name = $tamAd;
                        $user->cep_telefon = null;
                        $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                        if ($dogum) $user->dogum_tarihi = $dogum;
                        $user->save();
                    } catch (\Exception $e) {
                        // NOT NULL ise placeholder fallback
                        $user = new User();
                        $user->name = $tamAd;
                        $user->cep_telefon = 'drklinik_' . $drklinikId;
                        $user->profil_resim = '/public/isletmeyonetim_assets/img/avatar.png';
                        if ($dogum) $user->dogum_tarihi = $dogum;
                        $user->save();
                    }
                } else {
                    $user = $existing;
                }
            }

            $portfoy = MusteriPortfoy::where('user_id', $user->id)->where('salon_id', $this->salonId)->first();
            if (!$portfoy) {
                $portfoy = new MusteriPortfoy();
                $portfoy->user_id = $user->id;
                $portfoy->salon_id = $this->salonId;
                $portfoy->aktif = 1;
                $portfoy->save();
            }

            if ($drklinikId) $this->musteriMap[$drklinikId] = $user->id;
            $this->counts['musteri']++;
            $count++;
        }
        return $count;
    }

    private function tarihNormalize($t)
    {
        if (!$t || $t === ' ' || trim($t) === '') return null;
        // dd.mm.yyyy veya dd/mm/yyyy
        if (preg_match('~^(\d{1,2})[./](\d{1,2})[./](\d{4})$~', trim($t), $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        $ts = strtotime($t);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /**
     * Odalar: calisan_ekle.aspx detay formundaki DDL_Oda dropdown'undan cekilir.
     * Ayrica oda.aspx sayfasi mevcut (alternatif kaynak).
     */
    public function importOdalar()
    {
        $this->log('Odalar cekiliyor (DDL_Oda dropdown)...');

        // Bir personelin id'sini al, detay formundan DDL_Oda options
        $list = $this->client->getHtml('/calisanmodulu.aspx');
        if (!preg_match('~calisan_ekle\.aspx\?id=(\d+)&t=d~', $list, $m)) {
            $this->log('Personel id bulunamadi, DDL_Oda alinamiyor.');
            return;
        }
        $personelId = $m[1];
        $detail = $this->client->getHtml('/calisan_ekle.aspx?id=' . $personelId . '&t=d');
        $odalar = $this->parseSelectOptions($detail, 'DDL_Oda');
        if (empty($odalar)) { $this->log('DDL_Oda secenekleri bos.'); return; }

        $eklendi = 0;
        foreach ($odalar as $val => $ad) {
            if ($val === '0' || $val === '') continue;
            $odaAd = trim(html_entity_decode($ad, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($odaAd === '') continue;

            $oda = Odalar::where('oda_adi', $odaAd)->where('salon_id', $this->salonId)->first();
            if (!$oda) {
                $oda = new Odalar();
                $oda->oda_adi = $odaAd;
                $oda->salon_id = $this->salonId;
                $oda->durum = 1;
                if (Schema::hasColumn('odalar', 'aktifmi')) $oda->aktifmi = 1;
                $oda->save();
                $eklendi++;
            }
            $this->odaMap[$val] = $oda->id;
            $this->ensureOdaRenk($oda->id);
            $this->counts['oda']++;
        }
        $this->log("Oda aktarim: yeni={$eklendi}, toplam_map=" . count($this->odaMap));
    }

    private function ensureOdaRenk($odaId)
    {
        $var = OdaRenkleri::where('salon_id', $this->salonId)->where('oda_id', $odaId)->first();
        if ($var) return;
        $last = OdaRenkleri::where('salon_id', $this->salonId)->orderBy('id', 'desc')->first();
        $renk = 1;
        if ($last && isset($last->renk_id)) $renk = ($last->renk_id >= 10) ? 1 : $last->renk_id + 1;
        $n = new OdaRenkleri();
        $n->salon_id = $this->salonId;
        $n->oda_id = $odaId;
        $n->renk_id = $renk;
        $n->save();
    }

    /**
     * uruntanimlamalari.aspx tablosu: Urun Adi | Urun Kodu | Stok | ... | Satis Fiyati | Alis Fiyati | Marka
     */
    public function importUrunler()
    {
        $this->log('Urunler cekiliyor (uruntanimlamalari.aspx)...');
        $html = $this->client->getHtml('/uruntanimlamalari.aspx', 'urun_listesi');
        if ($html === '') { $this->log('Sayfa cekilemedi.'); return; }

        $rows = $this->parseTableRowsRaw($html);
        $this->log('  HTML tablodan ' . count($rows) . ' satir cikartildi.');

        $eklendi = 0;
        foreach ($rows as $rawRow) {
            $cells = [];
            foreach ($rawRow as $tdRaw) {
                if ($this->isButtonCell($tdRaw)) continue;
                $clean = trim(preg_replace('/\s+/', ' ', strip_tags($tdRaw)));
                $clean = trim(html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $cells[] = $clean;
            }
            $ad = isset($cells[0]) ? $cells[0] : '';
            if (!$ad) continue;
            $kod  = isset($cells[1]) ? $cells[1] : '';
            $stok = isset($cells[2]) ? (int) preg_replace('/[^0-9-]/', '', $cells[2]) : 0;
            // Ilk fiyat-benzeri (3. cell ve sonrasi) -> satis fiyati
            $fiyat = 0;
            foreach (array_slice($cells, 3) as $cell) {
                if (preg_match('/(\d+[\.,]\d{2})/', $cell, $m)) {
                    $fiyat = (float) str_replace(',', '.', $m[1]);
                    break;
                }
            }

            $u = Urunler::where('urun_adi', $ad)->where('salon_id', $this->salonId)->first();
            if (!$u) {
                $u = new Urunler();
                $u->urun_adi = $ad;
                $u->salon_id = $this->salonId;
            }
            if ($kod) $u->barkod = $kod;
            $u->stok_adedi = $stok;
            $u->fiyat = $fiyat;
            $u->aktif = 1;
            $u->save();
            $eklendi++;
            $this->counts['urun']++;
        }
        $this->log("Urun aktarim: {$eklendi}");
    }

    /**
     * En genis tabloyu bul, her satirin RAW td HTML'lerini doner (icerisinde input/a tag'leri korunur).
     */
    private function parseTableRowsRaw($html)
    {
        if (!preg_match_all('#<table[^>]*>(.*?)</table>#is', $html, $tables)) return [];
        $bestRows = [];
        foreach ($tables[1] as $t) {
            if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $t, $r)) {
                if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
            }
        }
        if (empty($bestRows)) return [];
        $out = [];
        foreach ($bestRows as $tr) {
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('#<td[^>]*>(.*?)</td>#is', $tr, $tds);
            if (empty($tds[1])) continue;
            $out[] = $tds[1];
        }
        return $out;
    }

    /**
     * td icerigi sadece buton/link/script ise true. Gercek text yoksa atilmali.
     */
    private function isButtonCell($tdRaw)
    {
        $hasButton = preg_match('#<(?:input[^>]+type="(?:button|submit)"|a\s|button\s)#i', $tdRaw);
        $textOnly = trim(strip_tags($tdRaw));
        // strip_tags HTML entity'leri korur, decode et ki "Se&#231;" -> "Seç"
        $decoded = trim(html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($hasButton) {
            // Aksiyon kelimeleri (TR karakterler dahil)
            if (preg_match('/^(Düzenle|Duzenle|Sil|Ödemeler|Odemeler|Ödeme\s*Al|Odeme\s*Al|Prim\s*Hesab[ıi]|Seç|Sec|Seçim|Secim|Detay|Göster|Goster|Güncelle|Guncelle|Kaydet|İptal|Iptal|Randevu\s*Kapatma|Onayla|Reddet|Müşteri\s*Sayfas[ıi]n[ıi]?\s*A[çc]|Musteri\s*Sayfas[ıi]n[ıi]?\s*A[çc]|Dosyay[ıi]\s*[İI]ncele)$/iu', $decoded)) return true;
            if ($decoded === '' || strlen($decoded) < 2) return true;
        }
        return false;
    }

    private function telefonNormalize($tel)
    {
        if (!$tel) return null;
        $tel = preg_replace('/[^0-9]/', '', (string) $tel);
        $tel = preg_replace('/^90/', '', $tel);
        $tel = preg_replace('/^0/', '', $tel);
        return $tel ?: null;
    }

    /**
     * ASP.NET WebForms TextBox <input name="X" value="..."> degerini cikar.
     */
    private function extractInputValue($html, $name)
    {
        // value once gelirse veya sonra gelirse iki paterni dene
        $pat = '#<input[^>]+name="' . preg_quote($name, '#') . '"[^>]*\bvalue="([^"]*)"#i';
        if (preg_match($pat, $html, $m)) return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $pat2 = '#<input[^>]+\bvalue="([^"]*)"[^>]+name="' . preg_quote($name, '#') . '"#i';
        if (preg_match($pat2, $html, $m)) return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return '';
    }

    private function parseSelectOptions($html, $selectId)
    {
        $pat = '#<select[^>]+(?:id|name)="' . preg_quote($selectId, '#') . '"[^>]*>(.*?)</select>#is';
        if (!preg_match($pat, $html, $m)) return [];
        $opts = [];
        if (preg_match_all('#<option[^>]*value="([^"]*)"[^>]*>(.*?)</option>#is', $m[1], $om, PREG_SET_ORDER)) {
            foreach ($om as $o) $opts[$o[1]] = trim(strip_tags($o[2]));
        }
        return $opts;
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

    /**
     * Generic HTML tablo parser: <tr> icindeki <td>'leri (header hari�) toplar.
     * Header'i (th) atlar, sadece veri satirlarini doner. Her satir td icerigi text array'i.
     */
    private function parseTableRows($html)
    {
        // En genis tabloyu bul (rapor gridviewleri ana tablodur)
        if (!preg_match_all('#<table[^>]*>(.*?)</table>#is', $html, $tables)) return [];
        // En cok satir iceren tabloyu sec
        $bestRows = [];
        foreach ($tables[1] as $t) {
            if (preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $t, $r)) {
                if (count($r[1]) > count($bestRows)) $bestRows = $r[1];
            }
        }
        if (empty($bestRows)) return [];

        $out = [];
        foreach ($bestRows as $tr) {
            // header satirini atla
            if (stripos($tr, '<th') !== false && stripos($tr, '<td') === false) continue;
            preg_match_all('#<td[^>]*>(.*?)</td>#is', $tr, $tds);
            if (empty($tds[1])) continue;
            $cols = array_map(function ($td) {
                $t = preg_replace('/\s+/', ' ', strip_tags($td));
                return trim(html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }, $tds[1]);
            $out[] = $cols;
        }
        return $out;
    }

    private function log($msg)
    {
        if ($this->out) $this->out->writeln($msg);
        Log::info('[DrklinikImporter] ' . $msg);
    }
}
