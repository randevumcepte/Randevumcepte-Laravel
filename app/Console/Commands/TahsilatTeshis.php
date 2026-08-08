<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adisyon tahsilat/indirim teşhis komutu.
 *
 * Kullanım:
 *   /opt/php74/bin/php artisan tahsilat:teshis {adisyonId}
 */
class TahsilatTeshis extends Command
{
    protected $signature = 'tahsilat:teshis {adisyonId}';
    protected $description = 'Adisyon kalem/tahsilat/indirim/komisyon durumunu dokum eder.';

    public function handle()
    {
        $aid = (int) $this->argument('adisyonId');
        $a = DB::table('adisyonlar')->where('id', $aid)->first();
        if (!$a) { $this->error("Adisyon #$aid bulunamadı"); return 1; }

        $this->info("=== Adisyon #{$a->id} ===");
        $this->line("user_id: {$a->user_id}   salon_id: {$a->salon_id}   olusturma: {$a->created_at}");
        $this->line("adisyon indirim_tutari (adisyon-level): " . ($a->indirim_tutari ?? 'NULL'));

        $this->info("\n--- HİZMET KALEMLERİ ---");
        $hs = DB::table('adisyon_hizmetler')->where('adisyon_id', $aid)->get();
        foreach ($hs as $h) {
            $this->line("#{$h->id}  fiyat={$h->fiyat}  indirim_tutari={$h->indirim_tutari}  seans_sayisi={$h->seans_sayisi}  senet_id=".($h->senet_id ?? 'NULL')."  taksitli_id=".($h->taksitli_tahsilat_id ?? 'NULL'));
        }
        $this->info("\n--- ÜRÜN KALEMLERİ ---");
        $us = DB::table('adisyon_urunler')->where('adisyon_id', $aid)->get();
        foreach ($us as $u) {
            $this->line("#{$u->id}  fiyat={$u->fiyat}  indirim_tutari={$u->indirim_tutari}  adet={$u->adet}");
        }
        $this->info("\n--- PAKET KALEMLERİ ---");
        $ps = DB::table('adisyon_paketler')->where('adisyon_id', $aid)->get();
        foreach ($ps as $p) {
            $this->line("#{$p->id}  fiyat={$p->fiyat}  indirim_tutari={$p->indirim_tutari}  seans_sayisi={$p->seans_sayisi}");
        }

        $this->info("\n--- TAHSILATLAR (adisyon_id={$aid}) ---");
        $ts = DB::table('tahsilatlar')->where('adisyon_id', $aid)->orderBy('id')->get();
        $hasKom = Schema::hasColumn('tahsilatlar', 'komisyon_tutari');
        foreach ($ts as $t) {
            $kom = $hasKom ? $t->komisyon_tutari : '(kolon yok)';
            $this->line("#{$t->id}  tutar={$t->tutar}  yapilan_odeme={$t->yapilan_odeme}  komisyon_tutari={$kom}  odeme_tarihi={$t->odeme_tarihi}  odeme_yontemi_id={$t->odeme_yontemi_id}");
        }

        $this->info("\n--- TAHSILAT KALEM DAĞITIMI ---");
        $hIds = $hs->pluck('id')->all();
        $uIds = $us->pluck('id')->all();
        $pIds = $ps->pluck('id')->all();
        if ($hIds) {
            $this->line("TahsilatHizmetler:");
            $rows = DB::table('tahsilat_hizmetler')->whereIn('adisyon_hizmet_id', $hIds)->get();
            foreach ($rows as $r) $this->line("  tahsilat_id={$r->tahsilat_id}  adisyon_hizmet_id={$r->adisyon_hizmet_id}  tutar={$r->tutar}");
        }
        if ($uIds) {
            $this->line("TahsilatUrunler:");
            $rows = DB::table('tahsilat_urunler')->whereIn('adisyon_urun_id', $uIds)->get();
            foreach ($rows as $r) $this->line("  tahsilat_id={$r->tahsilat_id}  adisyon_urun_id={$r->adisyon_urun_id}  tutar={$r->tutar}");
        }
        if ($pIds) {
            $this->line("TahsilatPaketler:");
            $rows = DB::table('tahsilat_paketler')->whereIn('adisyon_paket_id', $pIds)->get();
            foreach ($rows as $r) $this->line("  tahsilat_id={$r->tahsilat_id}  adisyon_paket_id={$r->adisyon_paket_id}  tutar={$r->tutar}");
        }

        // Ozet
        $toplamKalem = $hs->sum('fiyat') + $us->sum('fiyat') + $ps->sum('fiyat');
        $toplamIndirim = $hs->sum('indirim_tutari') + $us->sum('indirim_tutari') + $ps->sum('indirim_tutari');
        $tahsilatSum = $ts->sum('tutar');
        $komSum = $hasKom ? $ts->sum('komisyon_tutari') : 0;
        $this->info("\n=== ÖZET ===");
        $this->line("Kalem toplam (fiyat):     " . number_format($toplamKalem, 2, ',', '.'));
        $this->line("Kalem indirim toplam:     " . number_format($toplamIndirim, 2, ',', '.'));
        $this->line("Net Alacak (kalem-indirim):" . number_format($toplamKalem - $toplamIndirim, 2, ',', '.'));
        $this->line("Tahsilat toplam:          " . number_format($tahsilatSum, 2, ',', '.'));
        $this->line("Komisyon toplam:          " . number_format($komSum, 2, ',', '.'));
        $this->line("Kalan:                    " . number_format(($toplamKalem - $toplamIndirim) - $tahsilatSum, 2, ',', '.'));
        return 0;
    }
}
