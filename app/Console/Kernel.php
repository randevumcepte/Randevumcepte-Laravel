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
    ];

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
