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
       Commands\KampanyaAramaYap::class,
        Commands\KampanyaSMSGonder::class,
        Commands\RandevuHatirlatmaAramasiYap::class,
        Commands\AlacakHatirlatmaAramasiYap::class,
        Commands\TekrarAramaHatirlat::class,
       //Commands\NLPTokenGuncelle::class,
       //Commands\AvantajYayindanKaldir::class,
       //Commands\RandevuGuncelle::class,
       //Commands\SenetOdenmediBildirim::class,

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
      $schedule->command('sms:gonder')->withoutOverlapping()->everyMinute();
      $schedule->command('randevuarama:yap')->everyMinute();
      //$schedule->command('kampanyaarama:yap')->withoutOverlapping()->everyMinute();
      //$schedule->command('kampanyasms:gonder')->withoutOverlapping()->everyMinute();
      $schedule->command('alacakhatirlatma:aramayap')->withoutOverlapping()->everyMinute();
      $schedule->command('arama:hatirlat')->withoutOverlapping()->everyMinute();
      ///$schedule->command('dbyedek:al')->cron('*/15 * * * *')->withoutOverlapping();
       $schedule->command('dbyedek:al')->dailyAt('23:59')->withoutOverlapping();

      //$schedule->command('nlptoken:guncelle')->everyMinute();
      //$schedule->command('randevu:guncelle')->everyMinute();
      //$schedule->command('senetodenmedi:bildirim')->everyMinute();


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
