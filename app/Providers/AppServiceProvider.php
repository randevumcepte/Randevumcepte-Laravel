<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      ini_set('memory_limit', env('PHP_MEMORY_LIMIT', '1024M'));
      ini_set('max_execution_time', env('PHP_MAX_EXECUTION_TIME', 300)); // 60 saniye varsayılan
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
