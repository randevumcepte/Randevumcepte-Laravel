<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\MusteriPortfoy;

/**
 * Bir salonun aktif musterilerini (musteri_portfoy) baska bir salona klonlar.
 * SADECE musteri klonlar; hizmet/paket vs. dokunmaz.
 * Hedefte zaten portfoyu olan musteri ATLANIR (users satiri paylasimli, tekrar acilmaz).
 *
 * Ornek:
 *   php artisan musteri:klonla                 # 246 -> 416 (varsayilan)
 *   php artisan musteri:klonla 246 416
 *   php artisan musteri:klonla 246 416 --dry   # yazmadan sadece rapor
 */
class MusteriKlonla extends Command
{
    protected $signature = 'musteri:klonla {kaynak=246 : Kaynak salon id} {hedef=416 : Hedef salon id} {--dry : Yazmadan sadece kac musteri eklenecegini goster}';

    protected $description = 'Kaynak salonun musterilerini (yoksa) hedef salona klonlar';

    public function handle()
    {
        $kaynak = (int) $this->argument('kaynak');
        $hedef  = (int) $this->argument('hedef');
        $dry    = (bool) $this->option('dry');

        if ($kaynak <= 0 || $hedef <= 0 || $kaynak === $hedef) {
            $this->error('Gecersiz salon id (kaynak/hedef).');
            return 1;
        }

        // Hedefte halihazirda portfoyu olan user_id'ler
        $hedefte_var = MusteriPortfoy::where('salon_id', $hedef)->pluck('user_id')->flip();

        $kaynak_portfoyler = MusteriPortfoy::where('salon_id', $kaynak)->where('aktif', 1)->get();

        $this->info("Kaynak {$kaynak}: {$kaynak_portfoyler->count()} aktif musteri  |  Hedef {$hedef}: {$hedefte_var->count()} mevcut portfoy");
        if ($dry) $this->warn('--dry: hicbir sey yazilmayacak.');

        $eklenen = 0;
        $atlandi = 0;

        if (!$dry) DB::beginTransaction();
        try {
            foreach ($kaynak_portfoyler as $p) {
                if ($hedefte_var->has($p->user_id)) {
                    $atlandi++;
                    continue;
                }
                if (!$dry) {
                    $yeni = new MusteriPortfoy();
                    $yeni->user_id               = $p->user_id;
                    $yeni->salon_id              = $hedef;
                    $yeni->musteri_tipi          = $p->musteri_tipi;
                    $yeni->ozel_notlar           = $p->ozel_notlar;
                    $yeni->aktif                 = true;
                    $yeni->kvkk_onay_alindi      = $p->kvkk_onay_alindi;
                    $yeni->onay_kodu             = $p->onay_kodu;
                    $yeni->olusturan_personel_id = $p->olusturan_personel_id;
                    $yeni->save();
                }
                // Ayni calistirmada ayni user'in tekrar eklenmesini engelle
                $hedefte_var->put($p->user_id, true);
                $eklenen++;
            }
            if (!$dry) DB::commit();
        } catch (\Exception $e) {
            if (!$dry) DB::rollBack();
            $this->error('Hata: ' . $e->getMessage());
            return 1;
        }

        $this->info(($dry ? '[DRY] ' : '') . "Eklenecek/eklenen: {$eklenen}  |  Zaten var (atlandi): {$atlandi}");
        return 0;
    }
}
