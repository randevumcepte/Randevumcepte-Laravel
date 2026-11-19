<?php

namespace App\Console\Commands;

use App\Services\WaAsistan;
use App\Services\WaTalep;
use Illuminate\Console\Command;

/**
 * WhatsApp asistaninin isletmeye sordugu konularin takibi (App\Services\WaTalep::takip), 5 dk'da bir:
 *   15 dk -> yoneticilere hatirlatma, 45 dk -> asistan musteriye nazikce doner, 24 saat -> cevapsiz.
 *   + havada kalan sohbetler (cevapsiz musteri mesaji / "bir bakayım" deyip birakilan) asistanca tamamlanir.
 */
class WaTalepTakip extends Command
{
    protected $signature = 'wa:talep-takip';
    protected $description = 'WhatsApp asistani: isletmeye sorulan konularda hatirlatma + musteriye donus + cevapsiz isaretleme';

    public function handle()
    {
        $r = WaTalep::takip();
        $h = WaAsistan::havadaKalanlariTamamla();
        $this->info("Hatirlatma: {$r['hatirlatma']}, asistan musteriye dondu: {$r['asistan']}, cevapsiz: {$r['cevapsiz']}, havada kalan tamamlandi: {$h}");
        return 0;
    }
}
