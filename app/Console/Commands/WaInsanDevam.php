<?php

namespace App\Console\Commands;

use App\Services\WaAsistan;
use Illuminate\Console\Command;

/**
 * Her dakika: personel WhatsApp'tan yazdi, musteri sonra yazdi ama personel insan_esik_dk (5) boyunca cevap vermedi
 * -> asistan musteriye doner (musteri beklemez). WaAsistan::havadaKalanlariTamamla(true).
 */
class WaInsanDevam extends Command
{
    protected $signature = 'wa:insan-devam';
    protected $description = 'WhatsApp asistani: personel sustuysa asistan musteriye geri doner';

    public function handle()
    {
        $this->info('Asistanin geri dondugu sohbet: ' . WaAsistan::havadaKalanlariTamamla(true));
        return 0;
    }
}
