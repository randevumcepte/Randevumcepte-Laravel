<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\DBYedekAl::class,

        Commands\SMSGonder::class,
        Commands\RandevuSMSHatirlatma::class,
        Commands\DogumGunuSMSHatirlatma::class,
        Commands\AlacakSMSHatirlatma::class,

        Commands\KampanyaAramaYap::class,
        Commands\KampanyaSMSGonder::class,
        Commands\RandevuHatirlatmaAramasiYap::class,
        Commands\AlacakHatirlatmaAramasiYap::class,
        Commands\TekrarAramaHatirlat::class,
        Commands\PlanlaImport::class,
        Commands\DrklinikImport::class,
        Commands\SalonappyImport::class,
        Commands\SalonrandevuImport::class,
        Commands\CarkHatirlatmaGonder::class,
        Commands\AnketOtomatikGonder::class,
        Commands\WhatsappStuckKurtar::class,
        Commands\WhatsappTestGonder::class,
        Commands\IlacHatirlatmalari::class,
        Commands\OlcumHatirlatmalari::class,
        Commands\SeansHatirlatma::class,
    ];

    public function __construct(\Illuminate\Contracts\Foundation\Application $app, \Illuminate\Contracts\Events\Dispatcher $events)
    {
        // Defansif: bir komut dosyasi canli sunucuda eksikse (pull olmamissa)
        // tum Kernel cokuyordu ve cron komple duruyor (randevusms:hatirlat dahil).
        // Eksik class'lari filtrele, en azindan mevcut komutlar calismaya devam etsin.
        $this->commands = array_values(array_filter($this->commands, function ($cls) {
            return class_exists($cls);
        }));
        parent::__construct($app, $events);
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // SMS komutları
        $schedule->command('sms:gonder')->withoutOverlapping()->everyMinute();
        $schedule->command('randevusms:hatirlat')->withoutOverlapping()->everyMinute();
        $schedule->command('dogumgunusms:hatirlat')->withoutOverlapping()->everyMinute();
        $schedule->command('alacaksms:hatirlat')->withoutOverlapping()->everyMinute();

        // Arama hatırlatmaları
        $schedule->command('randevuarama:yap')->withoutOverlapping()->everyMinute();
        //$schedule->command('kampanyaarama:yap')->withoutOverlapping()->everyMinute();
        //$schedule->command('kampanyasms:gonder')->withoutOverlapping()->everyMinute();
        $schedule->command('alacakhatirlatma:aramayap')->withoutOverlapping()->everyMinute();
        $schedule->command('arama:hatirlat')->withoutOverlapping()->everyMinute();

        // Çarkıfelek hatırlatma — her 5 dakikada bir kontrol (saatlerin ±5dk penceresi)
        $schedule->command('cark:hatirlatma-gonder')->withoutOverlapping()->everyFiveMinutes();

        // İlaç ve ölçüm hatırlatmaları — kullanıcının belirlediği saatte (HH:i) tetiklenir
        $schedule->command('ilac:hatirlatma-calistir')->withoutOverlapping()->everyMinute();
        $schedule->command('olcum:hatirlatma-calistir')->withoutOverlapping()->everyMinute();

        // Memnuniyet anketi otomatik gönderim — randevu bitiş saatinde (MAX randevu_hizmetler.saat_bitis) tek sefer.
        $schedule->command('anket:otomatik-gonder')->withoutOverlapping()->everyMinute();

        // Seans hatırlatma — yarın yapılacak seansı 12:00'de tek seferlik push olarak müşteriye hatırlat.
        // Bildirim tıklanınca "Seanslarım" ekranı açılır (session_reminder -> sessions intent).
        $schedule->command('seans:hatirlat')->withoutOverlapping()->dailyAt('12:00');

        // WhatsApp kuyrukta takılı kalan mesajları SMS'e düşür — her 3 dakikada bir
        // Sebep: Node service RAM-only queue, restart/crash olunca mesajlar takılı kalıyordu
        $schedule->command('whatsapp:stuck-kurtar')->withoutOverlapping()->cron('*/3 * * * *');

        // Yedek
        $schedule->command('dbyedek:al')->dailyAt('23:59')->withoutOverlapping();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
