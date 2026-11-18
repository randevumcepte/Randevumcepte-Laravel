<?php

namespace App\Console\Commands;

use App\Services\WaGeriKazanim;
use App\Services\WaGorselDanisman;
use Illuminate\Console\Command;

/**
 * WhatsApp asistani saatlik isleri:
 *   1) kacan musterilere geri kazanma mesaji (App\Services\WaGeriKazanim::tara)
 *   2) geri kazanma takibi: randevu alindi mi, geldi mi, tutar
 *   3) 30 gunden eski model fotograflarini sil (KVKK)
 * Elle deneme: /opt/php74/bin/php artisan wa:geri-kazanim --kuru   (mesaj gondermez, log'a yazar)
 */
class WaGeriKazanimCalistir extends Command
{
    protected $signature = 'wa:geri-kazanim {--kuru : Mesaj gonderme, yalniz kimlere gidecegini logla}';
    protected $description = 'WhatsApp asistani: kacan musteriyi geri kazanma + takip + eski fotograf temizligi';

    public function handle()
    {
        $r = WaGeriKazanim::tara((bool) $this->option('kuru'));
        $this->info("Geri kazanma: aday {$r['aday']}, gonderildi {$r['gonderildi']}" . ($r['atlandi'] ? ' | atlandi: ' . json_encode($r['atlandi']) : ''));
        $this->info('Takip guncellenen: ' . WaGeriKazanim::takip());
        if (!$this->option('kuru')) $this->info('Silinen eski fotograf: ' . WaGorselDanisman::eskiDosyalariSil());
        return 0;
    }
}
