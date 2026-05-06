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
                $ad = Adisyonlar::where('salon_id', $this->salonId)
                    ->where($notKolonu, 'LIKE', '%' . $idMarker . '%')->first();
            }
            // Fallback: salon+user+tarih ile dedup (tutar kolonu olmayabilir)
            if (!$ad) {
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
                $birimFiyat = $hv['tutar'] / max(1, $hv['seans']);
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
                $ah->geldi = 1;
                $ah->islem_tarihi = $tarih;
                $ah->islem_saati = '00:00:00';
                $ah->sure = $sh['sure_dk'] ?: 30;
                $ah->fiyat = $birimFiyat;
                $ah->save();
            }
            $eklendi++;
        }
        return ['eklendi' => $eklendi, 'atlandi' => $atlandi];
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
     * Tahsilatlar: kasa_islemleri.aspx + BTN_Ara, hafta-hafta tarama (server cap'i muhtemel 50).
     * Sutunlar (11 td): Tarih | Aciklama | Odeme Sekli | Tutar | Musteri | Banka | Taksit | Kasa | Dosya(buton) | Saat | GenelTip
     *
     * Drklinik'te tahsilat dogrudan musteriye bagli (randevu/adisyon iliskisi liste'de yok),
     * Tahsilatlar.adisyon_id NULL olarak yazilir.
     */
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
            $durum   = $cells[15] ?? '';

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

            // Hizmet (birim adiyla)
            $hizmetInfo = $hizmetMap[$this->trKey($birim)] ?? null;

            // Randevu idempotent: tarih+saat+user+salon
            $r = Randevular::where('tarih', $tarih)->where('saat', $saat)
                ->where('user_id', $userId)->where('salon_id', $this->salonId)->first();
            if (!$r) $r = new Randevular();
            $r->tarih = $tarih;
            $r->saat = $saat;
            $r->user_id = $userId;
            $r->salon_id = $this->salonId;
            $r->durum = 1;
            $r->salon = 0;
            $r->olusturan_personel_id = null;
            // Status mapping
            $du = mb_strtolower($durum, 'UTF-8');
            if (strpos($du, 'geldi') !== false && strpos($du, 'gelmedi') === false) $r->randevuya_geldi = 1;
            elseif (strpos($du, 'gelmedi') !== false || strpos($du, 'iptal') !== false) $r->randevuya_geldi = 0;
            $r->save();

            // RandevuHizmetler (birim varsa)
            if ($hizmetInfo) {
                $rh = RandevuHizmetler::where('randevu_id', $r->id)->where('hizmet_id', $hizmetInfo['hizmet_id'])->first();
                if (!$rh) $rh = new RandevuHizmetler();
                $rh->randevu_id = $r->id;
                $rh->hizmet_id = $hizmetInfo['hizmet_id'];
                $rh->saat = $saat;
                $rh->saat_bitis = $bitis ?: date('H:i:s', strtotime('+' . $hizmetInfo['sure_dk'] . ' minutes', strtotime($saat)));
                $rh->sure_dk = $hizmetInfo['sure_dk'];
                if ($personelId) $rh->personel_id = $personelId;
                if ($odaId) $rh->oda_id = $odaId;
                $rh->save();
            }

            // Adisyon + AdisyonHizmetler (tahsilat icin altyapi)
            $ad = Adisyonlar::where('user_id', $userId)
                ->where('salon_id', $this->salonId)
                ->where('tarih', $tarih)
                ->whereHas('hizmetler', function ($q) use ($r) { $q->where('randevu_id', $r->id); })
                ->first();
            if (!$ad) {
                $ad = new Adisyonlar();
                $ad->user_id = $userId;
                $ad->salon_id = $this->salonId;
                $ad->tarih = $tarih;
                $ad->olusturan_id = $personelId;
                $ad->save();
            }
            if ($hizmetInfo) {
                $ah = AdisyonHizmetler::where('adisyon_id', $ad->id)->where('randevu_id', $r->id)
                    ->where('hizmet_id', $hizmetInfo['hizmet_id'])->first();
                if (!$ah) $ah = new AdisyonHizmetler();
                $ah->adisyon_id = $ad->id;
                $ah->hizmet_id = $hizmetInfo['hizmet_id'];
                $ah->randevu_id = $r->id;
                $ah->personel_id = $personelId;
                $ah->geldi = isset($r->randevuya_geldi) ? $r->randevuya_geldi : 0;
                $ah->islem_tarihi = $tarih;
                $ah->islem_saati = $saat;
                $ah->sure = $hizmetInfo['sure_dk'];
                $ah->fiyat = $hizmetInfo['fiyat'];
                $ah->save();
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
            if (preg_match('/^(Düzenle|Duzenle|Sil|Ödemeler|Odemeler|Prim\s*Hesab[ıi]|Seç|Sec|Seçim|Secim|Detay|Göster|Goster|Güncelle|Guncelle|Kaydet|İptal|Iptal|Randevu\s*Kapatma|Onayla|Reddet)$/iu', $decoded)) return true;
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
