<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SistemBildirim;

/**
 * Güvenlik Duvarı alarm bildirimi.
 *
 * Root watchdog (scripts/guvenlik-watchdog.sh) saldırgan IP'yi engelledikten
 * SONRA bu komutu çağırır. Mesajı sistem WhatsApp oturumundan (whatsmeow 3002)
 * gönderir; WA bağlı değilse SistemBildirim otomatik SMS'e düşer.
 *
 * Kullanım:
 *   /opt/php74/bin/php artisan guvenlik:bildir "🛡️ 1 IP engellendi ..."
 */
class GuvenlikBildir extends Command
{
    protected $signature = 'guvenlik:bildir {mesaj : Gönderilecek alarm metni}';

    protected $description = 'Güvenlik duvarı alarmını sistem WhatsApp/SMS ile gönderir';

    public function handle()
    {
        $mesaj = (string) $this->argument('mesaj');
        if (trim($mesaj) === '') {
            $this->error('Bos mesaj — atlandi.');
            return 1;
        }

        $r = SistemBildirim::gonder($mesaj);
        if (empty($r['ok'])) {
            $this->error('Gonderilemedi: ' . ($r['reason'] ?? 'bilinmiyor') . ' (Sistem WhatsApp bildirim numarasi/aktif ayarini kontrol edin)');
            return 1;
        }

        $this->info('Alarm gonderildi.');
        return 0;
    }
}
