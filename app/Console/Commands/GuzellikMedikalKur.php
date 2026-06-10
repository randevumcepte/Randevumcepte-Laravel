<?php

namespace App\Console\Commands;

use App\Hizmet_Kategorisi;
use App\Hizmetler;
use App\SalonHizmetler;
use App\Salonlar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GuzellikMedikalKur extends Command
{
    protected $signature = 'hizmetler:medikal-kur
        {--salon= : Sablon salon ID (bos ise config varsayilanlar.guzellik_sablon_salon_id)}
        {--dry-run : Sadece raporla, kayit yapma}';

    protected $description = 'Medikal kategorisi + hizmetlerini olusturur ve sablon guzellik salonuna ekler. Idempotent (var olanlar atlanir).';

    /** Medikal hizmetleri (veride yok, yeni olusturulur). */
    protected $medikalHizmetler = [
        'Hydrafacial',
        'Mezoterapi',
        'PRP (Vampir Bakimi)',
        'Leke Tedavisi',
        'Cilt Genclestirme',
        'Kimyasal Peeling',
        'Bolgesel Yag / Lipoliz',
    ];

    public function handle()
    {
        $sablonId = (int) ($this->option('salon') ?: config('varsayilanlar.guzellik_sablon_salon_id', 0));
        $dryRun   = (bool) $this->option('dry-run');

        if (!$sablonId) {
            $this->error('Sablon salon ID yok (--salon verin veya config/varsayilanlar.php icindeki guzellik_sablon_salon_id ayarlayin).');
            return 1;
        }

        $sablon = Salonlar::find($sablonId);
        if (!$sablon) {
            $this->error("Sablon salon bulunamadi (id={$sablonId}).");
            return 1;
        }

        $this->info("Sablon salon: {$sablon->salon_adi} (id={$sablonId}, salon_turu_id={$sablon->salon_turu_id})");
        if ($dryRun) {
            $this->warn('DRY-RUN: kayit yapilmayacak, sadece raporlanacak.');
        }
        $this->line('');

        // 1) Medikal kategorisi (firstOrCreate by name)
        $kategori = Hizmet_Kategorisi::where('hizmet_kategorisi_adi', 'Medikal')->first();
        if (!$kategori) {
            if ($dryRun) {
                $this->line('- Kategori olusturulacak: Medikal');
            } else {
                $kategori = new Hizmet_Kategorisi();
                $kategori->hizmet_kategorisi_adi = 'Medikal';
                $kategori->save();
                $this->line("- Kategori olusturuldu: Medikal (id={$kategori->id})");
            }
        } else {
            $this->line("- Kategori mevcut: Medikal (id={$kategori->id})");
        }
        $kategoriId = $kategori ? $kategori->id : null;

        $hizmetTuruVar = Schema::hasColumn('hizmetler', 'salon_turu_id');

        // 2) Hizmetler + sablon salona ekleme
        $yeniHizmet = 0;
        $yeniSablon = 0;
        foreach ($this->medikalHizmetler as $ad) {
            // Global hizmet katalogunda var mi?
            $hizmet = Hizmetler::where('hizmet_adi', $ad)
                ->when($kategoriId, function ($q) use ($kategoriId) {
                    $q->where('hizmet_kategori_id', $kategoriId);
                })
                ->first();

            if (!$hizmet) {
                if ($dryRun) {
                    $this->line("  - Hizmet olusturulacak: {$ad}");
                    continue; // dry-run'da id olmadigi icin sablon adimi atlanir
                }
                $hizmet = new Hizmetler();
                $hizmet->hizmet_kategori_id = $kategoriId;
                $hizmet->hizmet_adi = $ad;
                $hizmet->fiyat = 0;
                if ($hizmetTuruVar) {
                    $hizmet->salon_turu_id = $sablon->salon_turu_id;
                }
                $hizmet->save();
                $yeniHizmet++;
                $this->line("  - Hizmet olusturuldu: {$ad} (id={$hizmet->id})");
            } else {
                $this->line("  - Hizmet mevcut: {$ad} (id={$hizmet->id})");
            }

            // Sablon salonun sunulan hizmetlerine ekle
            $zatenVar = SalonHizmetler::where('salon_id', $sablonId)
                ->where('hizmet_id', $hizmet->id)
                ->exists();
            if (!$zatenVar) {
                if ($dryRun) {
                    $this->line("    -> Sablona eklenecek: {$ad}");
                } else {
                    $sh = new SalonHizmetler();
                    $sh->salon_id           = $sablonId;
                    $sh->hizmet_id          = $hizmet->id;
                    $sh->hizmet_kategori_id = $kategoriId;
                    $sh->baslangic_fiyat    = 0;
                    $sh->son_fiyat          = 0;
                    $sh->save();
                    $yeniSablon++;
                    $this->line("    -> Sablona eklendi: {$ad}");
                }
            }
        }

        $this->line('');
        $this->info("Bitti. {$yeniHizmet} yeni hizmet, {$yeniSablon} sablon hizmet eklendi.");
        if (!$dryRun) {
            $this->line("Simdi mevcut guzellik salonlarina yaymak icin: php artisan hizmetler:varsayilan-yay");
        }
        return 0;
    }
}
