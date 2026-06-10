<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cagri Merkezi (Call Center) semasini GARANTILER (idempotent).
 *
 * NEDEN: Prod deploy'u sadece `git pull` yapiyor, `php artisan migrate` calistirmiyor.
 * Bu yuzden cagri merkezi migration'i (yeni kolon/tablolar) canliya yansimaz.
 * Bu komut scheduler ile saatlik calisir; semayi App\Services\CagriMerkeziSchema
 * uzerinden uygular (tek dogruluk kaynagi). Her sey mevcutken ucuz no-op'tur.
 *
 * Tek seferlik manuel: /opt/php74/bin/php artisan cagrimerkezi:schema-ensure
 */
class CagriMerkeziSchemaEnsure extends Command
{
    protected $signature = 'cagrimerkezi:schema-ensure {--quiet-noop : Degisiklik yoksa log yazma}';
    protected $description = 'Cagri merkezi semasini (arama_listesi, aranacak_musteriler, gorusme_notlari) garanti eder.';

    public function handle()
    {
        $uygulanan = \App\Services\CagriMerkeziSchema::ensure();

        if (empty($uygulanan)) {
            if (!$this->option('quiet-noop')) {
                $this->info('Cagri merkezi semasi zaten guncel. Yapilacak is yok.');
            }
            return 0;
        }

        $msg = 'cagrimerkezi:schema-ensure — UYGULANAN: ' . implode(', ', $uygulanan);
        $this->info($msg);
        Log::info($msg);
        return 0;
    }
}
