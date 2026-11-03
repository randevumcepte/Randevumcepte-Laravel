<?php

namespace App\Console\Commands;

use App\SalonSadakatAyarlari;
use App\Services\SadakatPuanServisi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sadakat puani kazanimi: kazanimi acik salonlarda tahsilat (her X TL -> Y puan) ve
 * gelinen randevu (Z puan) kayitlarini defterle esitler; silinenlerin puanini geri alir.
 * Idempotent (SadakatPuanServisi). Schedule: everyFiveMinutes + withoutOverlapping.
 */
class SadakatPuanIsle extends Command
{
    protected $signature = 'sadakat:puan-isle
        {--salon= : Sadece bu salon ID icin calis (test)}
        {--dry : Yazmadan ne olacagini raporla (test)}';
    protected $description = 'Tahsilat ve gelinen randevulardan sadakat puani ver / geri al';

    public function handle()
    {
        $dry = (bool) $this->option('dry');
        $q = SalonSadakatAyarlari::where('aktif', 1)->whereNotNull('baslangic');
        if ($this->option('salon')) $q->where('salon_id', (int) $this->option('salon'));

        $ayarlar = $q->get();
        if ($ayarlar->isEmpty() && $this->option('salon')) {
            $this->line('Bu salonda puan kazanma kurali acik degil (Carkifelek > Puan Odulleri > Puan Kazanma Kurali).');
            return;
        }

        foreach ($ayarlar as $ayar) {
            try {
                $s = SadakatPuanServisi::mutabakat($ayar, $dry);
                if ($s['islem'] > 0 || $this->option('salon')) {
                    $this->line("salon {$ayar->salon_id}: {$s['islem']} islem, +{$s['verilen']} / -{$s['geri_alinan']} puan" . ($dry ? ' (dry)' : ''));
                }
            } catch (\Throwable $e) {
                Log::error('sadakat:puan-isle hata', ['salon_id' => $ayar->salon_id, 'err' => $e->getMessage()]);
            }
        }
    }
}
