<?php

namespace App\Console\Commands;

use App\Services\RandevuSemasi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * randevular ek alanlarini garanti eder (idempotent). Canli deploy migrate calistirmadigi icin
 * scheduler'dan calisir; alan varken ucuz no-op.
 */
class RandevuSemaEnsure extends Command
{
    protected $signature = 'randevu:sema-ensure';
    protected $description = 'randevular.randevuya_islemde alanini garanti eder.';

    public function handle()
    {
        if (RandevuSemasi::ensure()) {
            Log::info('randevu:sema-ensure — randevular.randevuya_islemde eklendi');
            $this->info('randevuya_islemde eklendi');
        }
        return 0;
    }
}
