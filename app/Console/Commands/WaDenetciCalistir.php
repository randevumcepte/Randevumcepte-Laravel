<?php

namespace App\Console\Commands;

use App\Services\WaDenetci;
use Illuminate\Console\Command;

/**
 * WhatsApp asistani Gece Denetcisi (App\Services\WaDenetci):
 *   wa:denetci gonder   -> bitmis sohbetleri Batch API'ye yollar (her gece 03:00)
 *   wa:denetci topla    -> biten batch sonuclarini isler, ders uretir + test eder (30 dk'da bir)
 *   wa:denetci ozet     -> haftalik ozet bildirimi (pazartesi 09:30)
 * Elle: /opt/php74/bin/php artisan wa:denetci gonder --isletme=236
 */
class WaDenetciCalistir extends Command
{
    protected $signature = 'wa:denetci {is : gonder | topla | ozet} {--isletme= : Yalniz bu isletme (gonder)}';
    protected $description = 'WhatsApp asistani Gece Denetcisi: sohbet inceleme, ders uretme/test, haftalik ozet';

    public function handle()
    {
        switch ($this->argument('is')) {
            case 'gonder':
                $r = WaDenetci::gonder($this->option('isletme') ? (int) $this->option('isletme') : null);
                $this->info('Denetime gonderilen sohbet: ' . ($r['adet'] ?? 0) . (empty($r['ok']) ? ' | HATA: ' . ($r['hata'] ?? '') : ''));
                break;
            case 'topla':
                $r = WaDenetci::topla();
                $this->info('Islenen denetim: ' . ($r['islenen'] ?? 0) . ', ders uretilen isletme: ' . ($r['isletme'] ?? 0));
                break;
            case 'ozet':
                $this->info('Haftalik ozet gonderilen isletme: ' . WaDenetci::haftalikOzet());
                break;
            default:
                $this->error('Gecersiz is: gonder | topla | ozet');
                return 1;
        }
        return 0;
    }
}
