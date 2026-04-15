<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Ilac;
use App\Jobs\IlacHatirlatmaJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IlacHatirlatmalari extends Command
{
    protected $signature = 'ilac:hatirlatma-calistir';
    protected $description = 'İlaç hatırlatmalarını kontrol eder';

    public function handle()
    {
        $now = Carbon::now()->format('H:i');

        Log::info("💊 İlaç kontrol zamanı: $now");

        Ilac::where('baslangic_tarihi', '<=', Carbon::now())
            ->where('kalan_adet', '>', 0)
            ->chunk(500, function ($ilaclar) use ($now) {
                foreach ($ilaclar as $ilac) {
                    
                    $saatler = $ilac->saatler ?? [];

                    if (in_array($now, $saatler)) {

                        Log::info("💊 Tetiklenen ilaç: {$ilac->adi}");

                        IlacHatirlatmaJob::dispatch($ilac)->onQueue('hatirlatmalar');
                    }
                }
            });

        $this->info('💊 İlaç kontrolü tamamlandı.');
    }
}