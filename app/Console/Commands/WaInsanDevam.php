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
        // Yarim kalan / musterinin 5 dk'dir cevap vermedigi sohbetler de her dakika: hatirlatma 5-10 dk degil tam 5. dk'da gider
        // (wa:talep-takip 5 dk'da bir ayni isi yapar; ayni sohbet iki kez yazilmaz: Cache wa_havada_ kilidi)
        $this->info('Tamamlanan / hatirlatilan sohbet: ' . WaAsistan::havadaKalanlariTamamla());
        return 0;
    }
}
