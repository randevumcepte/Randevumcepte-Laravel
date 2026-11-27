<?php

namespace App\Console\Commands;

use App\Services\AkilliBildirim;
use Illuminate\Console\Command;

/**
 * Dakikada bir: "once bildirim, acilmazsa SMS" ile bekletilen hatirlatma SMS'lerini isler (acilmadiysa
 * gonderir) ve 21:00-09:00 sessiz saatinde ertelenen kampanya bildirimlerini zamani gelince gonderir.
 */
class AkilliBildirimIsle extends Command
{
    protected $signature = 'bildirim:akilli';
    protected $description = 'Bekletilen hatirlatma SMS yedekleri + sessiz saatte ertelenen kampanya bildirimleri';

    public function handle()
    {
        [$sms, $iptal] = AkilliBildirim::yedekleriIsle();
        $ertelenen = AkilliBildirim::ertelenenleriGonder();
        if ($sms || $iptal || $ertelenen) {
            $this->line("yedek SMS gonderilen={$sms} gerek kalmayan={$iptal} ertelenen kampanya gonderilen={$ertelenen}");
        }
        return 0;
    }
}
