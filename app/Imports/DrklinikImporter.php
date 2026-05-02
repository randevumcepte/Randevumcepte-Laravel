<?php

namespace App\Imports;

use App\Services\DrklinikClient;
use App\Hizmetler;
use App\Hizmet_Kategorisi;
use App\SalonHizmetler;
use App\SalonHizmetKategoriRenkleri;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
    private $counts = ['hizmet' => 0, 'personel' => 0, 'musteri' => 0, 'randevu' => 0, 'tahsilat' => 0, 'skipped' => 0];
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
            $kategoriAd = mb_convert_case(mb_strtolower($kategoriAd, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
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
        $k = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', $ad)->first();
        if (!$k) {
            $k = new Hizmet_Kategorisi();
            $k->hizmet_kategorisi_adi = $ad;
            $k->save();
        }
        return $k->id;
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
