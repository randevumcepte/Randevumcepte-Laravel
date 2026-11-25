<?php

namespace App\Console\Commands;

use App\Services\DilSozluk;
use App\Services\MusteriDili;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pilot salonun kendi icerigi (hizmet, kategori, paket, urun, kampanya, odul, uzmanlik): sozlukte olmayanlar
 * yapay zeka ile bir kez cevrilip saklanir (dil_cevirileri). Yeni eklenen icerik gece kendiliginden cevrilir.
 */
class DilSalonCevir extends Command
{
    protected $signature = 'dil:salon-cevir {salon?} {--dry : Yalniz sayilari goster}';
    protected $description = 'Musteri dili: salon iceriginin eksik cevirilerini tamamlar';

    public function handle()
    {
        if (!MusteriDili::semaGaranti()) return $this->error('tablo yok');
        $salonlar = $this->argument('salon') ? [(int) $this->argument('salon')] : (array) config('diller.salonlar');
        foreach ($salonlar as $salonId) {
            if (!MusteriDili::acikMi($salonId)) { $this->line("salon {$salonId}: pilotta degil"); continue; }
            $metinler = $this->metinler((int) $salonId);
            foreach (MusteriDili::diller() as $d) {
                if ($d['kod'] === 'tr') continue;
                $s = DilSozluk::al($d['kod'], MusteriDili::salonSozluk($salonId, $d['kod']));
                $eksik = array_values(array_filter($metinler, function ($m) use ($s) { $s->cevir($m); return $s->eksikMi(); }));
                $this->line("salon {$salonId} {$d['kod']}: " . count($metinler) . ' metin, eksik ' . count($eksik));
                if ($this->option('dry')) continue;
                foreach (array_chunk($eksik, 25) as $grup) {
                    $c = MusteriDili::aiCevir($salonId, $grup, $d['kod']);
                    if (!$c) { $this->warn('  ceviri alinamadi'); continue; }
                    foreach ($grup as $i => $tr) {
                        if (MusteriDili::guvenli($tr, $c[$i])) MusteriDili::sakla($salonId, $d['kod'], $tr, $c[$i], 'icerik');
                    }
                }
            }
        }
    }

    private function metinler($salonId)
    {
        $m = [];
        $al = function ($tablo, $kolonlar, $kosul) use (&$m) {
            try {
                if (!Schema::hasTable($tablo)) return;
                $kolonlar = array_values(array_filter($kolonlar, function ($k) use ($tablo) { return Schema::hasColumn($tablo, $k); }));
                if (!$kolonlar) return;
                $q = DB::table($tablo);
                $kosul($q);
                foreach ($q->get($kolonlar) as $r) foreach ($kolonlar as $k) $m[] = (string) $r->$k;
            } catch (\Throwable $e) {
                $this->warn("  {$tablo}: " . mb_substr($e->getMessage(), 0, 90));
            }
        };
        $hizmet = DB::table('salon_sunulan_hizmetler')->where('salon_id', $salonId)->where('aktif', 1)->pluck('hizmet_id')->toArray();
        $al('hizmetler', ['hizmet_adi'], function ($q) use ($hizmet) { $q->whereIn('id', $hizmet); });
        $kat = DB::table('hizmetler')->whereIn('id', $hizmet)->pluck('hizmet_kategori_id')->unique()->toArray();
        $al('hizmet_kategorisi', ['hizmet_kategorisi_adi'], function ($q) use ($kat) { $q->whereIn('id', $kat); });
        $salonda = function ($q) use ($salonId) { $q->where('salon_id', $salonId); };
        $al('paketler', ['paket_adi'], $salonda);
        $al('urunler', ['urun_adi'], $salonda);
        $al('kampanya_yonetimi', ['paket_isim', 'mesaj'], $salonda);
        $al('bildirim_reklamlari', ['baslik', 'mesaj'], $salonda);
        $al('carkifelek_dilimleri', ['dilim_ismi'], function ($q) use ($salonId) {
            $q->whereIn(Schema::hasColumn('carkifelek_dilimleri', 'cark_id') ? 'cark_id' : 'carkifelek_id', DB::table('carkifelek_sistemi')->where('salon_id', $salonId)->pluck('id')->toArray());
        });
        $al('salon_puan_odulleri', ['baslik', 'aciklama'], $salonda);
        $al('damga_karti_kurallari', ['baslik', 'aciklama'], $salonda);
        $al('salon_personelleri', ['unvan', 'uzmanlik', 'aciklama'], $salonda);
        $al('salonlar', ['aciklama', 'meta_description'], function ($q) use ($salonId) { $q->where('id', $salonId); });
        $m = array_map(function ($x) { return trim(html_entity_decode(strip_tags($x), ENT_QUOTES, 'UTF-8')); }, $m);
        return array_values(array_unique(array_filter($m, function ($x) {
            return mb_strlen($x) > 1 && mb_strlen($x) < 900 && preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]{2}/u', $x);
        })));
    }
}
